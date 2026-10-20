<?php

namespace App\Policies;

use App\Models\{PipelineStage, User};

class PipelineStagePolicy
{
    public function view(User $user, PipelineStage $stage): bool { return $user->id === $stage->user_id; }
    public function update(User $user, PipelineStage $stage): bool { return $this->view($user, $stage); }
    public function delete(User $user, PipelineStage $stage): bool { return $this->view($user, $stage); }
}
