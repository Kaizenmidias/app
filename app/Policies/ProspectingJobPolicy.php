<?php
namespace App\Policies;
use App\Models\{User,ProspectingJob};
class ProspectingJobPolicy { public function view(User $user, ProspectingJob $job): bool { return $user->id===$job->user_id; } public function delete(User $user, ProspectingJob $job): bool { return $this->view($user,$job); } }
