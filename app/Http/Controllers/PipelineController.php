<?php

namespace App\Http\Controllers;

use App\Models\{Lead, LeadActivity, PipelineOpportunity, PipelineStage};
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        $stages = $request->user()->pipelineStages()
            ->with('opportunities.lead')
            ->orderBy('position')
            ->get();

        return view('pipeline.index', compact('stages'));
    }

    public function storeStage(Request $request)
    {
        $name = $this->stageName($request);
        $user = $request->user();
        DB::transaction(function () use ($user, $name) {
            $lockedUser = $this->lockUser($user->id);
            $this->ensureUniqueStageName($lockedUser->id, $name);
            $stages = $lockedUser->pipelineStages()->lockForUpdate()->orderBy('id')->get();
            $position = (($stages->max('position')) ?? -1) + 1;
            $lockedUser->pipelineStages()->create(['name' => $name, 'position' => $position]);
        }, 3);

        return back()->with('status', 'Etapa criada.');
    }

    public function updateStage(Request $request, PipelineStage $stage)
    {
        $this->authorize('update', $stage);
        $name = $this->stageName($request);
        DB::transaction(function () use ($stage, $name) {
            $this->lockUser($stage->user_id);
            $lockedStage = PipelineStage::query()->whereKey($stage->id)->where('user_id', $stage->user_id)->lockForUpdate()->firstOrFail();
            $this->ensureUniqueStageName($lockedStage->user_id, $name, $lockedStage->id);
            $lockedStage->update(['name' => $name]);
        }, 3);

        return back()->with('status', 'Nome da etapa atualizado.');
    }

    public function reorder(Request $request)
    {
        $ids = $request->validate([
            'stage_ids' => ['required', 'array', 'min:1'],
            'stage_ids.*' => ['integer', 'distinct'],
        ])['stage_ids'];
        $user = $request->user();
        DB::transaction(function () use ($ids, $user) {
            $this->lockUser($user->id);
            // Lock every owned stage in a stable order so concurrent reorders
            // serialize instead of deadlocking on opposite client orders.
            $userStageIds = $user->pipelineStages()->lockForUpdate()->orderBy('id')->pluck('id');
            abort_unless(count($ids) === $userStageIds->count() && $userStageIds->diff($ids)->isEmpty(), 422);

            foreach ($ids as $position => $id) {
                $user->pipelineStages()->whereKey($id)->update(['position' => $position]);
            }
        }, 3);

        return back()->with('status', 'Ordem das etapas atualizada.');
    }

    public function destroyStage(Request $request, PipelineStage $stage)
    {
        $this->authorize('delete', $stage);
        $destinationId = $request->validate(['destination_stage_id' => ['nullable', 'integer']])['destination_stage_id'] ?? null;
        $result = DB::transaction(function () use ($request, $stage, $destinationId) {
            $this->lockUser($stage->user_id);
            $lockedStage = PipelineStage::query()->whereKey($stage->id)->where('user_id', $stage->user_id)->lockForUpdate()->firstOrFail();
            $opportunities = PipelineOpportunity::query()->where('pipeline_stage_id', $lockedStage->id)->where('user_id', $stage->user_id)->lockForUpdate()->orderBy('id')->get();
            if ($opportunities->isNotEmpty() && !$destinationId) return false;
            $destination = null;
            if ($destinationId) {
                $destination = PipelineStage::query()->whereKey($destinationId)->where('user_id', $stage->user_id)->lockForUpdate()->firstOrFail();
                abort_if($destination->id === $lockedStage->id, 422);
            }
            if ($opportunities->isNotEmpty()) PipelineOpportunity::query()->whereIn('id', $opportunities->pluck('id'))->update(['pipeline_stage_id' => $destination->id]);
            $lockedStage->delete();
            return true;
        }, 3);
        if ($result === false) return back()->withErrors(['pipeline' => 'Selecione uma etapa de destino antes de excluir.']);

        return back()->with('status', 'Etapa excluída.');
    }

    public function send(Request $request, Lead $lead)
    {
        $this->authorize('view', $lead);
        DB::transaction(function () use ($request, $lead) {
            $user = $this->lockUser($request->user()->id);
            $stage = $user->pipelineStages()->lockForUpdate()->orderBy('position')->orderBy('id')->first();
            if (!$stage) throw ValidationException::withMessages(['pipeline' => 'Crie uma etapa antes de enviar leads.']);
            $created = false;
            try { PipelineOpportunity::create(['user_id' => $user->id, 'lead_id' => $lead->id, 'pipeline_stage_id' => $stage->id]); $created = true; }
            catch (QueryException $exception) {
                $alreadyExists = PipelineOpportunity::query()->where('user_id', $user->id)->where('lead_id', $lead->id)->exists();
                if (!$alreadyExists) throw $exception;
            }
            if ($created) LeadActivity::create(['user_id'=>$user->id,'lead_id'=>$lead->id,'type'=>'pipeline','description'=>'Lead enviado para a etapa '.$stage->name.'.']);
        }, 3);

        return back()->with('status', 'Lead enviado para a pipeline.');
    }

    public function sendMany(Request $request)
    {
        $ids = $request->validate(['lead_ids' => ['required', 'array', 'min:1'], 'lead_ids.*' => ['integer']])['lead_ids'];
        $leads = Lead::query()->where('user_id', $request->user()->id)->whereIn('id', $ids)->get();
        abort_unless($leads->count() === count(array_unique($ids)), 403);
        DB::transaction(function () use ($request, $leads) {
            $user = $this->lockUser($request->user()->id);
            $stage = $user->pipelineStages()->lockForUpdate()->orderBy('position')->orderBy('id')->first();
            if (!$stage) throw ValidationException::withMessages(['pipeline' => 'Crie uma etapa antes de enviar leads.']);
            foreach ($leads as $lead) { $opportunity=PipelineOpportunity::firstOrCreate(['user_id' => $user->id, 'lead_id' => $lead->id], ['pipeline_stage_id' => $stage->id]); if($opportunity->wasRecentlyCreated) LeadActivity::create(['user_id'=>$user->id,'lead_id'=>$lead->id,'type'=>'pipeline','description'=>'Lead enviado para a etapa '.$stage->name.'.']); }
        }, 3);
        return back()->with('status', $leads->count().' leads enviados para a pipeline.');
    }

    public function move(Request $request, PipelineOpportunity $opportunity)
    {
        $this->authorize('update', $opportunity);
        $destinationId = $request->validate(['pipeline_stage_id' => ['required', 'integer']])['pipeline_stage_id'];
        $userId = $request->user()->id;

        DB::transaction(function () use ($opportunity, $destinationId, $userId) {
            $this->lockUser($userId);
            $destination = PipelineStage::query()
                ->whereKey($destinationId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();
            abort_unless($destination, 404);

            $lockedOpportunity = PipelineOpportunity::query()
                ->whereKey($opportunity->id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();
            $lockedOpportunity->update(['pipeline_stage_id' => $destination->id]);
            LeadActivity::create(['user_id'=>$userId,'lead_id'=>$lockedOpportunity->lead_id,'type'=>'pipeline','description'=>'Lead movido para a etapa '.$destination->name.'.']);
        });

        return $request->expectsJson() ? response()->json(['ok' => true]) : back();
    }

    private function stageName(Request $request): string
    {
        $name = trim((string) $request->input('name'));
        if ($name === '' || mb_strlen($name) > 80) {
            throw ValidationException::withMessages(['name' => 'Informe um nome de etapa com até 80 caracteres.']);
        }

        return $name;
    }

    private function ensureUniqueStageName(int $userId, string $name, ?int $ignoreId = null): void
    {
        $query = PipelineStage::query()->where('user_id', $userId)->where('name', $name);
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages(['name' => 'Você já possui uma etapa com esse nome.']);
        }
    }

    private function lockUser(int $userId)
    {
        return \App\Models\User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
    }
}
