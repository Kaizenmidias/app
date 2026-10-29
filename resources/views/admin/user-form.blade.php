@php($selectedAccess = $managedUser?->module_access ?? $modules)
@php($passwordId = $creating ? 'admin-create-password' : 'admin-password-'.$managedUser->id)
@php($confirmationId = $creating ? 'admin-create-password-confirmation' : 'admin-password-confirmation-'.$managedUser->id)
<div class="admin-form-grid">
    <label><span>Foto de perfil (opcional)</span><input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp"></label>
    <label><span>Nome completo</span><input name="name" value="{{ old('name', $managedUser?->name) }}" required autocomplete="name"></label>
    <label><span>E-mail</span><input type="email" name="email" value="{{ old('email', $managedUser?->email) }}" required autocomplete="email"></label>
    <label><span>FunÃ§Ã£o</span><select name="role" required><option value="partner" @selected(old('role',$managedUser?->role ?? 'partner')==='partner')>UsuÃ¡rio</option><option value="admin" @selected(old('role',$managedUser?->role)==='admin')>Administrador</option></select></label>
    <label><span>Status</span><select name="status" required><option value="active" @selected(old('status',$managedUser?->status ?? 'active')==='active')>Ativo</option><option value="blocked" @selected(old('status',$managedUser?->status)==='blocked')>Bloqueado</option></select></label>
    <label><span>{{ $creating ? 'Senha inicial' : 'Nova senha (opcional)' }}</span><div class="password-field"><input id="{{ $passwordId }}" type="password" name="password" @required($creating) minlength="12" autocomplete="new-password"><button class="password-toggle" type="button" data-password-toggle aria-controls="{{ $passwordId }}" aria-label="Mostrar senha" aria-pressed="false"></button></div><small>Nunca exibimos a senha atual. Use no mÃ­nimo 12 caracteres.</small></label>
    <label><span>Confirmar senha</span><div class="password-field"><input id="{{ $confirmationId }}" type="password" name="password_confirmation" @required($creating) minlength="12" autocomplete="new-password"><button class="password-toggle" type="button" data-password-toggle aria-controls="{{ $confirmationId }}" aria-label="Mostrar senha" aria-pressed="false"></button></div></label>
</div>
<fieldset class="admin-access-fieldset"><legend>Acesso aos mÃ³dulos</legend><p>Administradores sempre possuem acesso completo.</p><div class="admin-access-grid">
@foreach(['dashboard'=>'VisÃ£o geral','prospecting'=>'ProspecÃ§Ã£o e suas buscas','leads'=>'Leads','pipeline'=>'Pipeline'] as $key=>$label)
<label><input type="checkbox" name="module_access[]" value="{{ $key }}" @checked(in_array($key, old('module_access',$selectedAccess), true))><span>{{ $label }}</span></label>
@endforeach
</div></fieldset>
@unless($creating)<dl class="admin-account-meta"><div><dt>E-mail verificado</dt><dd>{{ $managedUser->hasVerifiedEmail() ? 'Sim' : 'NÃ£o' }}</dd></div><div><dt>Ãšltimo acesso</dt><dd>{{ $managedUser->last_login_at?->format('d/m/Y H:i') ?? 'Nunca' }}</dd></div><div><dt>Criado em</dt><dd>{{ $managedUser->created_at?->format('d/m/Y H:i') }}</dd></div></dl>@endunless
