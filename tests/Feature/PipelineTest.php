<?php

namespace Tests\Feature;

use App\Models\{Lead, PipelineOpportunity, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineTest extends TestCase
{
    use RefreshDatabase;

    private function user() { return User::factory()->create(['email_verified_at' => now(), 'status' => 'active']); }

    public function test_stage_lifecycle_and_isolation(): void
    {
        $u = $this->user(); $v = $this->user();
        $this->actingAs($u)->post('/pipeline/stages', ['name' => 'Novo'])->assertRedirect();
        $s = $u->pipelineStages()->first();
        $this->actingAs($u)->patch('/pipeline/stages/'.$s->id, ['name' => 'Contato'])->assertRedirect();
        $this->actingAs($v)->patch('/pipeline/stages/'.$s->id, ['name' => 'Hack'])->assertForbidden();
        $this->actingAs($u)->delete('/pipeline/stages/'.$s->id)->assertRedirect();
        $this->assertDatabaseMissing('pipeline_stages', ['id' => $s->id]);
    }

    public function test_duplicate_send_and_safe_transfer(): void
    {
        $u = $this->user();
        $this->actingAs($u)->post('/pipeline/stages', ['name' => 'Novo']);
        $this->actingAs($u)->post('/pipeline/stages', ['name' => 'Contato']);
        $a = $u->pipelineStages()->orderBy('position')->first(); $b = $u->pipelineStages()->orderBy('position', 'desc')->first();
        $lead = Lead::create(['user_id' => $u->id, 'name' => 'Empresa']);
        $this->actingAs($u)->post('/pipeline/leads/'.$lead->id); $this->actingAs($u)->post('/pipeline/leads/'.$lead->id);
        $this->assertSame(1, PipelineOpportunity::count());
        $this->actingAs($u)->delete('/pipeline/stages/'.$a->id, ['destination_stage_id' => $b->id])->assertRedirect();
        $this->assertDatabaseHas('pipeline_opportunities', ['pipeline_stage_id' => $b->id]);
    }

    public function test_cannot_delete_occupied_stage_without_destination(): void
    {
        $u = $this->user(); $this->actingAs($u)->post('/pipeline/stages', ['name' => 'Novo']); $s = $u->pipelineStages()->first();
        $lead = Lead::create(['user_id' => $u->id, 'name' => 'Empresa']); PipelineOpportunity::create(['user_id' => $u->id, 'lead_id' => $lead->id, 'pipeline_stage_id' => $s->id]);
        $this->actingAs($u)->delete('/pipeline/stages/'.$s->id)->assertSessionHasErrors('pipeline');
        $this->assertDatabaseHas('pipeline_stages', ['id' => $s->id]);
    }

    public function test_occupied_stage_transfer_rejects_itself_without_mutation(): void
    {
        $u = $this->user(); $this->actingAs($u)->post('/pipeline/stages', ['name' => 'Origem']);
        $stage = $u->pipelineStages()->first(); $lead = Lead::create(['user_id' => $u->id, 'name' => 'Preservado']);
        $opportunity = PipelineOpportunity::create(['user_id' => $u->id, 'lead_id' => $lead->id, 'pipeline_stage_id' => $stage->id]);
        $this->actingAs($u)->delete('/pipeline/stages/'.$stage->id, ['destination_stage_id' => $stage->id])->assertStatus(422);
        $this->assertDatabaseHas('pipeline_stages', ['id' => $stage->id]);
        $this->assertDatabaseHas('pipeline_opportunities', ['id' => $opportunity->id, 'pipeline_stage_id' => $stage->id]);
    }

    public function test_other_user_stage_is_rejected_on_move(): void
    {
        $u = $this->user(); $v = $this->user(); $this->actingAs($u)->post('/pipeline/stages', ['name' => 'A']); $this->actingAs($v)->post('/pipeline/stages', ['name' => 'B']);
        $a = $u->pipelineStages()->first(); $b = $v->pipelineStages()->first(); $lead = Lead::create(['user_id' => $u->id, 'name' => 'Empresa']);
        $op = PipelineOpportunity::create(['user_id' => $u->id, 'lead_id' => $lead->id, 'pipeline_stage_id' => $a->id]);
        $this->actingAs($u)->patch('/pipeline/opportunities/'.$op->id, ['pipeline_stage_id' => $b->id])->assertNotFound();
    }

    public function test_reorder_requires_the_complete_owned_stage_set(): void
    {
        $u = $this->user(); $v = $this->user(); $this->actingAs($u)->post('/pipeline/stages', ['name' => 'A']); $this->actingAs($u)->post('/pipeline/stages', ['name' => 'B']); $this->actingAs($v)->post('/pipeline/stages', ['name' => 'Other']);
        $ids = $u->pipelineStages()->orderByDesc('position')->pluck('id')->all(); $this->actingAs($u)->post('/pipeline/stages/reorder', ['stage_ids' => $ids])->assertRedirect();
        $this->assertSame($ids, $u->pipelineStages()->orderBy('position')->pluck('id')->all()); $foreign = $v->pipelineStages()->first()->id;
        $this->actingAs($u)->post('/pipeline/stages/reorder', ['stage_ids' => [$ids[0], $foreign]])->assertStatus(422);
    }

    public function test_blank_stage_name_is_rejected(): void { $u = $this->user(); $this->actingAs($u)->post('/pipeline/stages', ['name' => '   '])->assertSessionHasErrors('name'); $this->assertDatabaseCount('pipeline_stages', 0); }
    public function test_pipeline_page_uses_shared_shell_and_stage_actions(): void { $u = $this->user(); $this->actingAs($u)->post('/pipeline/stages', ['name' => 'Novo']); $response = $this->actingAs($u)->get('/pipeline'); $response->assertOk()->assertSee('scraper-shell-sidebar')->assertSee('stage-menu')->assertSee('Nova etapa')->assertSee('pipeline.css'); }
}
