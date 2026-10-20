<?php
namespace App\Policies;
use App\Models\{User,Lead};
class LeadPolicy { public function view(User $user, Lead $lead): bool { return $user->id===$lead->user_id; } public function update(User $user, Lead $lead): bool { return $this->view($user,$lead); } public function delete(User $user, Lead $lead): bool { return $this->view($user,$lead); } }
