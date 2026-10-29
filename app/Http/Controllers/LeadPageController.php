<?php

namespace App\Http\Controllers;

use App\Models\{Lead, LeadActivity, LeadList, LeadLabel, ProspectingJob};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadPageController extends Controller
{
    public function __invoke(Request $request) { return $this->render($request); }

    public function results(Request $request, ProspectingJob $job)
    {
        abort_unless($job->user_id === $request->user()->id, 404);
        return $this->render($request, $job);
    }

    private function render(Request $request, ?ProspectingJob $job = null)
    {
        $user = $request->user();
        $term = trim((string) $request->query('q'));
        $contact = (string) $request->query('contact', 'all');
        $allowedPerPage = [20, 30, 50, 100, 150];
        $perPage = in_array($request->integer('per_page'), $allowedPerPage, true) ? $request->integer('per_page') : 20;
        $listId = $request->integer('list') ?: null;
        if ($listId && !$user->leadLists()->whereKey($listId)->exists()) abort(404);
        $query = $user->leads()->with(['job', 'list', 'responsible', 'notes', 'activities', 'opportunity.stage'])->latest();
        if ($job) $query->where('prospecting_job_id', $job->id);
        if ($listId) $query->where('lead_list_id', $listId);
        if ($contact === 'email') $query->whereNotNull('email')->where('email', '<>', '');
        if ($contact === 'phone') $query->whereNotNull('phone')->where('phone', '<>', '');
        if ($contact === 'site') $query->whereNotNull('website')->where('website', '<>', '');
        if ($contact === 'without_site') $query->where(fn ($builder) => $builder->whereNull('website')->orWhere('website', ''));
        if ($term) $query->where(fn ($builder) => $builder->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")->orWhere('address', 'like', "%{$term}%"));
        return view('leads.index', [
            'leads' => $query->paginate($perPage)->withQueryString(),
            'term' => $term,
            'lists' => $user->leadLists()->withCount('leads')->orderBy('name')->get(),
            'activeListId' => $listId,
            'resultJob' => $job,
            'contactFilter' => $contact,
            'perPage' => $perPage,
            'resultSummary' => $job ? [
                'collected' => (int) data_get($job->search_parameters, 'import_report.total', $job->leads()->count()),
                'duplicates' => (int) data_get($job->search_parameters, 'import_report.duplicates', 0),
                'without_site' => $job->leads()->where(fn ($builder) => $builder->whereNull('website')->orWhere('website', ''))->count(),
                'whatsapp_validated' => $job->leads()->get(['raw_data'])->filter(fn ($lead) => (bool) data_get($lead->raw_data, 'whatsapp_validated', false))->count(),
            ] : null,
            'assignees' => collect([$user]),
            'labels' => $user->leadLabels()->orderBy('name')->get(),
        ]);
    }

    public function storeList(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $request->user()->leadLists()->firstOrCreate(['name' => trim($data['name'])]);
        return back()->with('status', 'Lista criada.');
    }

    public function storeLabel(Request $request)
    {
        $data=$request->validate(['name'=>['required','string','max:80'],'background_color'=>['required','regex:/^#[0-9a-fA-F]{6}$/'],'text_color'=>['required','regex:/^#[0-9a-fA-F]{6}$/']]);
        $request->user()->leadLabels()->updateOrCreate(['name'=>trim($data['name'])],['background_color'=>strtolower($data['background_color']),'text_color'=>strtolower($data['text_color'])]);
        return back()->with('status','Etiqueta criada.');
    }

    public function applyLabel(Request $request)
    {
        $data=$request->validate(['lead_id'=>'required|integer','lead_label_id'=>'required|integer']);
        $lead=$request->user()->leads()->findOrFail($data['lead_id']); $label=$request->user()->leadLabels()->findOrFail($data['lead_label_id']);
        $tags=array_values(array_unique(array_filter(array_merge($lead->tags??[],[$label->name])))); $lead->update(['tags'=>$tags]); LeadActivity::create(['user_id'=>$request->user()->id,'lead_id'=>$lead->id,'type'=>'tag','description'=>'Etiqueta aplicada: '.$label->name]); return back()->with('status','Etiqueta aplicada.');
    }

    public function store(Request $request)
    {
        $data = $this->validateLead($request);
        $userId = $request->user()->id;
        $duplicate = Lead::where('user_id', $userId)->where('source', 'manual')->where('name', $data['name'])->when($data['email'] ?? null, fn ($query, $email) => $query->where('email', $email), fn ($query) => $query->whereNull('email'))->exists();
        if ($duplicate) return back()->withInput()->withErrors(['name' => 'Este lead manual já existe na sua conta.']);
        DB::transaction(fn () => Lead::create($this->leadAttributes($data, $userId, 'manual')));
        return back()->with('status', 'Lead cadastrado.');
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorize('view', $lead);
        $data = $this->validateLead($request);
        $tracked = ['lead_list_id','responsible_user_id','tags','name','phone','email','website','instagram','linkedin','sector','estimated_revenue','employee_count','budget_value','address'];
        $before = $lead->only($tracked);
        $raw=$lead->raw_data ?? []; $raw['funnel_stage']=$data['funnel_stage'] ?? ($raw['funnel_stage'] ?? null); $lead->update($this->leadAttributes($data, $request->user()->id, $lead->source ?: 'manual', $raw));
        $after = $lead->fresh()->only($tracked);
        $changes = array_keys(array_filter($after, fn ($value, $field) => $value != $before[$field], ARRAY_FILTER_USE_BOTH));
        if (in_array('tags', $changes, true)) $this->recordActivity($request, $lead, 'tag', 'Etiquetas atualizadas.');
        if (in_array('responsible_user_id', $changes, true)) $this->recordActivity($request, $lead, 'responsible', filled($after['responsible_user_id']) ? 'Responsável definido.' : 'Responsável removido.');
        if (in_array('lead_list_id', $changes, true)) $this->recordActivity($request, $lead, 'list', filled($after['lead_list_id']) ? 'Lista do lead alterada.' : 'Lead removido da lista.');
        if (array_diff($changes, ['tags','responsible_user_id','lead_list_id'])) $this->recordActivity($request, $lead, 'updated', 'Dados do lead atualizados.');
        if (filled($data['note'] ?? null)) {
            $lead->notes()->create(['user_id'=>$request->user()->id,'content'=>$data['note']]);
            $this->recordActivity($request, $lead, 'note', 'Anotação adicionada.');
        }
        return back()->with('status', 'Lead atualizado.');
    }

    public function assignList(Request $request)
    {
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1'], 'lead_ids.*' => ['integer'],
            'lead_list_id' => ['nullable', 'integer', Rule::exists('lead_lists', 'id')->where('user_id', $request->user()->id)],
        ]);
        $leads = Lead::where('user_id', $request->user()->id)->whereIn('id', $data['lead_ids'])->get();
        abort_unless($leads->count() === count(array_unique($data['lead_ids'])), 403);
        $targetListId = $data['lead_list_id'] ?? null;
        DB::transaction(function () use ($leads, $targetListId, $request) {
            foreach ($leads as $lead) {
                if ((int) $lead->lead_list_id === (int) $targetListId) continue;
                $lead->update(['lead_list_id' => $targetListId]);
                $this->recordActivity($request, $lead, 'list', $targetListId ? 'Lista do lead alterada.' : 'Lead removido da lista.');
            }
        });
        return back()->with('status', $leads->count().' leads atualizados.');
    }

    private function validateLead(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'], 'company' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'], 'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:2048'], 'observations' => ['nullable', 'string', 'max:10000'],
            'instagram' => ['nullable', 'url', 'max:2048'], 'linkedin' => ['nullable', 'url', 'max:2048'],
            'sector' => ['nullable', 'string', 'max:255'], 'estimated_revenue' => ['nullable', 'numeric', 'min:0'],
            'employee_count' => ['nullable', 'integer', 'min:0'], 'budget_value' => ['nullable', 'numeric', 'min:0'],
            'tags_text' => ['nullable', 'string', 'max:1000'], 'responsible_user_id' => ['nullable', 'integer', Rule::in([$request->user()->id])], 'funnel_stage'=>['nullable',Rule::in(['contacted','meeting','closed'])],
            'note' => ['nullable', 'string', 'max:10000'],
            'lead_list_id' => ['nullable', 'integer', Rule::exists('lead_lists', 'id')->where('user_id', $request->user()->id)],
        ]);
    }

    private function leadAttributes(array $data, int $userId, string $source, array $raw = []): array
    {
        $raw['manual_company'] = $data['company'] ?? null;
        $raw['manual_observations'] = $data['observations'] ?? null;
        $tags = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)($data['tags_text'] ?? ''))))));
        return ['user_id' => $userId, 'responsible_user_id'=>$data['responsible_user_id']??null, 'lead_list_id' => $data['lead_list_id'] ?? null, 'name' => $data['name'], 'category' => $data['category'] ?? null, 'sector'=>$data['sector']??null, 'estimated_revenue'=>$data['estimated_revenue']??null, 'employee_count'=>$data['employee_count']??null, 'budget_value'=>$data['budget_value']??null, 'tags'=>$tags ?: null, 'address' => $data['address'] ?? null, 'phone' => $data['phone'] ?? null, 'email' => $data['email'] ?? null, 'website' => $data['website'] ?? null, 'instagram'=>$data['instagram']??null, 'linkedin'=>$data['linkedin']??null, 'source' => $source, 'raw_data' => $raw];
    }

    private function recordActivity(Request $request, Lead $lead, string $type, string $description): void
    {
        LeadActivity::create(['user_id' => $request->user()->id, 'lead_id' => $lead->id, 'type' => $type, 'description' => $description]);
    }
}
