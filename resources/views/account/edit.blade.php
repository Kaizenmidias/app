@extends('layouts.scraper-shell')
@section('title', 'Minha conta · Parceiros Kaizen')
@push('styles')
<link rel="stylesheet" href="{{ asset('account.css') }}?v=158">
@endpush
@section('content')
<div class="account-page">
<header class="module-header"><div><p class="module-eyebrow">Conta</p><h1>Minha conta</h1><p class="module-description">Gerencie seus dados de acesso.</p></div></header>
@if(session('status'))<p class="form-status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="form-errors"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="module-card profile-card"><h2>Foto de perfil</h2><div class="profile-preview">@if($user->profile_photo_path)<img src="{{ route('account.photo') }}" alt="Foto de {{ $user->name }}" width="72" height="72">@else<span class="scraper-shell-profile-avatar">{{ mb_strtoupper(mb_substr(trim($user->name ?: 'P'), 0, 1)) }}</span>@endif</div><form method="post" action="{{ route('account.photo.update') }}" enctype="multipart/form-data"><label for="profile-photo">Selecionar foto<input id="profile-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label><button type="submit">Salvar foto</button></form>@if($user->profile_photo_path)<form method="post" action="{{ route('account.photo.remove') }}">@csrf @method('DELETE')<button type="submit" class="button-secondary">Remover foto</button></form>@endif</section>
<section class="module-card"><h2>Dados pessoais</h2><form method="post" action="{{ route('account.update') }}">@csrf @method('PUT')<label for="account-name">Nome<input id="account-name" name="name" value="{{ old('name',$user->name) }}" required></label><label for="account-email">E-mail<input id="account-email" type="email" name="email" value="{{ old('email',$user->email) }}" required></label><button type="submit">Salvar alterações</button></form></section>
<section class="module-card"><h2>Alterar senha</h2><form method="post" action="{{ route('account.password') }}">@csrf @method('PUT')<label for="current-password">Senha atual<input id="current-password" type="password" name="current_password" required></label><label for="new-password">Nova senha<input id="new-password" type="password" name="password" required></label><label for="password-confirmation">Confirmar nova senha<input id="password-confirmation" type="password" name="password_confirmation" required></label><button type="submit">Atualizar senha</button></form></section>
</div>
@endsection
