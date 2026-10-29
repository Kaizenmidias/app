<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'profile_photo_path',
        'apify_api_token',
        'password',
        'role',
        'status',
        'module_access',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'apify_api_token' => 'encrypted',
            'module_access' => 'array',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isActive(): bool { return $this->status === 'active'; }
    public function canAccessModule(string $module): bool
    {
        if ($this->isAdmin()) return true;

        $access = $this->module_access;

        return $access === null || in_array($module, $access, true);
    }
    public function prospectingJobs() { return $this->hasMany(ProspectingJob::class); }
    public function leads() { return $this->hasMany(Lead::class); }
    public function leadLists() { return $this->hasMany(\App\Models\LeadList::class); }
    public function pipelineStages() { return $this->hasMany(PipelineStage::class); }
}
