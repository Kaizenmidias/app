<?php

namespace App\Policies;

use App\Models\{PipelineOpportunity, User};

class PipelineOpportunityPolicy
{
    public function view(User $user, PipelineOpportunity $opportunity): bool { return $user->id === $opportunity->user_id; }
    public function update(User $user, PipelineOpportunity $opportunity): bool { return $this->view($user, $opportunity); }
}
