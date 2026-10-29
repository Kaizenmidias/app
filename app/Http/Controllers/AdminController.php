<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Models\Lead;
use App\Models\ProspectingJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AdminController extends Controller
{
    private const MODULES = ['dashboard', 'prospecting', 'leads', 'pipeline'];

    public function dashboard()
    {
        return view('admin.dashboard', [
            'users' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'blocked' => User::where('status', 'blocked')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'leads' => Lead::count(),
            'jobs' => ProspectingJob::count(),
        ]);
    }

    public function users(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in(['partner', 'admin'])],
            'status' => ['nullable', Rule::in(['active', 'blocked'])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
        ]);

        $users = User::query()
            ->when($filters['q'] ?? null, fn ($query, $value) => $query->where(
                fn ($nested) => $nested->where('name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%")
            ))
            ->when($filters['role'] ?? null, fn ($query, $value) => $query->where('role', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        return view('admin.users', ['users' => $users, 'modules' => self::MODULES]);
    }

    public function create()
    {
        return redirect()->route('admin.users', ['create' => 1]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedUser($request, null, true);

        DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => $data['status'],
                'module_access' => $data['role'] === 'admin' ? null : $this->moduleAccess($data),
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            if ($request->hasFile('profile_photo')) {
                $user->forceFill(['profile_photo_path' => $request->file('profile_photo')->storeAs('profile-photos/'.$user->id, str()->uuid().'.'.$request->file('profile_photo')->extension(), 'local')])->save();
            }

            $this->audit($request, 'user.created', $user, [
                'role' => $user->role,
                'status' => $user->status,
                'module_access' => $user->module_access,
            ]);
        });

        return redirect()->route('admin.users')->with('status', 'Usuário criado com sucesso.');
    }

    public function edit(User $user)
    {
        return redirect()->route('admin.users', ['user' => $user->id]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validatedUser($request, $user);

        DB::transaction(function () use ($request, $user, $data) {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $this->assertActiveAdminRemains($locked, $data['role'], $data['status']);

            $before = [
                'name' => $locked->name,
                'email' => $locked->email,
                'role' => $locked->role,
                'status' => $locked->status,
                'module_access' => $locked->module_access,
            ];
            $emailChanged = strcasecmp($locked->email, $data['email']) !== 0;
            $attributes = [
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'role' => $data['role'],
                'status' => $data['status'],
                'module_access' => $data['role'] === 'admin' ? null : $this->moduleAccess($data),
            ];

            if ($emailChanged) $attributes['email_verified_at'] = null;
            if (! empty($data['password'])) {
                $attributes['password'] = Hash::make($data['password']);
                $attributes['remember_token'] = str()->random(60);
            }
            if ($request->hasFile('profile_photo')) {
                $oldPhoto = $locked->profile_photo_path;
                $attributes['profile_photo_path'] = $request->file('profile_photo')->storeAs('profile-photos/'.$locked->id, str()->uuid().'.'.$request->file('profile_photo')->extension(), 'local');
                if ($oldPhoto) Storage::disk('local')->delete($oldPhoto);
            }

            $locked->forceFill($attributes)->save();

            if ($locked->status === 'blocked' || ! empty($data['password'])) {
                DB::table('sessions')->where('user_id', $locked->id)->delete();
            }

            $this->audit($request, 'user.updated', $locked, [
                'before' => $before,
                'after' => [
                    'name' => $locked->name,
                    'email' => $locked->email,
                    'role' => $locked->role,
                    'status' => $locked->status,
                    'module_access' => $locked->module_access,
                ],
                'password_changed' => ! empty($data['password']),
            ]);
        });

        return redirect()->route('admin.users')->with('status', 'Usuário atualizado com sucesso.');
    }

    public function toggle(Request $request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $newStatus = $locked->status === 'active' ? 'blocked' : 'active';
            $this->assertActiveAdminRemains($locked, $locked->role, $newStatus);
            $locked->update(['status' => $newStatus]);
            if ($newStatus === 'blocked') DB::table('sessions')->where('user_id', $locked->id)->delete();
            $this->audit($request, 'user.status_changed', $locked, ['status' => $newStatus]);
        });

        return back()->with('status', 'Status do usuário atualizado.');
    }

    public function resendVerification(Request $request, User $user)
    {
        $user->sendEmailVerificationNotification();
        $this->audit($request, 'user.verification_resent', $user);

        return back()->with('status', 'E-mail de verificação solicitado.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $status = Password::sendResetLink(['email' => $user->email]);
        $this->audit($request, 'user.password_reset_requested', $user, ['result' => $status]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Link de redefinição solicitado com sucesso.')
            : back()->withErrors(['admin' => 'Não foi possível enviar o link de redefinição.']);
    }

    public function photo(User $user)
    {
        abort_unless($user->profile_photo_path && Storage::disk('local')->exists($user->profile_photo_path), 404);

        return response()->file(Storage::disk('local')->path($user->profile_photo_path), [
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    private function validatedUser(Request $request, ?User $user = null, bool $creating = false): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(['partner', 'admin'])],
            'status' => ['required', Rule::in(['active', 'blocked'])],
            'module_access' => ['nullable', 'array'],
            'module_access.*' => [Rule::in(self::MODULES)],
            'password' => [$creating ? 'required' : 'nullable', 'confirmed', PasswordRule::min(12)],
            'profile_photo' => ['nullable','file','image','mimes:jpeg,jpg,png,webp','max:2048'],
        ]);
    }

    private function moduleAccess(array $data): array
    {
        return array_values(array_intersect(self::MODULES, $data['module_access'] ?? []));
    }

    private function assertActiveAdminRemains(User $target, string $newRole, string $newStatus): void
    {
        if (! $target->isAdmin() || ! $target->isActive() || ($newRole === 'admin' && $newStatus === 'active')) return;

        $activeAdminIds = User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->lockForUpdate()
            ->pluck('id');

        abort_if($activeAdminIds->count() <= 1, 403, 'Mantenha pelo menos um administrador ativo.');
    }

    private function audit(Request $request, string $action, User $target, array $metadata = []): void
    {
        AdminAuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => $action,
            'target_type' => User::class,
            'target_id' => $target->id,
            'metadata' => $metadata ?: null,
        ]);
    }
}
