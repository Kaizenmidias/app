<?php

namespace App\Policies;

use App\Models\{LeadNote, User};

class LeadNotePolicy
{
    public function view(User $user, LeadNote $note): bool { return $user->id === $note->user_id; }
    public function update(User $user, LeadNote $note): bool { return $this->view($user, $note); }
    public function delete(User $user, LeadNote $note): bool { return $this->view($user, $note); }
}
