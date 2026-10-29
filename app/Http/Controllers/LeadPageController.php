<?php

namespace App\Http\Controllers;

use App\Models\{Lead, LeadList, ProspectingJob};
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
        $listId = $request->integer('list') ?: null;
        if ($listId && !$user->leadLists()->whereKey($listId)->exists()) abort(404);
        $query = $user->leads()->with(['job', 'list'])->latest();
        if ($job) $query->where('prospecting_job_id', $job->id);
        if ($listId) $query->where('lead_list_id', $listId);
        if ($term) $query->where(fn ($builder) => $builder->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")->orWhere('address', 'like', "%{$term}%"));
        return view('leads.index', [
            'leads' => $query->paginate(25)->withQueryString(),
            'term' => $term,
            'lists' => $user->leadLists()->withCount('leads')->orderBy('name')->get(),
            'activeListId' => $listId,
            'resultJob' => $job,
        ]);
    }

    public function storeList(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $request->user()->leadLists()->firstOrCreate(['name' => trim($data['name'])]);
        return back()->with('status', 'Lista criada.');
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
        $lead->update($this->leadAttributes($data, $request->user()->id, $lead->source ?: 'manual', $lead->raw_data ?? []));
        return back()->with('status', 'Lead atualizado.');
    }

    private function validateLead(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'], 'company' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'], 'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:2048'], 'observations' => ['nullable', 'string', 'max:10000'],
            'lead_list_id' => ['nullable', 'integer', Rule::exists('lead_lists', 'id')->where('user_id', $request->user()->id)],
        ]);
    }

    private function leadAttributes(array $data, int $userId, string $source, array $raw = []): array
    {
        $raw['manual_company'] = $data['company'] ?? null;
        $raw['manual_observations'] = $data['observations'] ?? null;
        return ['user_id' => $userId, 'lead_list_id' => $data['lead_list_id'] ?? null, 'name' => $data['name'], 'category' => $data['category'] ?? null, 'address' => $data['address'] ?? null, 'phone' => $data['phone'] ?? null, 'email' => $data['email'] ?? null, 'website' => $data['website'] ?? null, 'source' => $source, 'raw_data' => $raw];
    }
}
