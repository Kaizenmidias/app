@extends('layouts.scraper-shell')
@section('title', 'Minha conta · Parceiros Kaizen')
@section('content')
<header class="module-header"><div><p class="module-eyebrow">Conta</p><h1>Minha conta</h1><p class="module-description">Gerencie seus dados de acesso.</p></div></header>
@if(session('status'))<p class="form-status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="form-errors"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="module-card"><h2>Dados pessoais</h2><form method="post" action="{{ route('account.update') }}">@csrf @method('PUT')<label for="account-name">Nome<input id="account-name" name="name" value="{{ old('name',$user->name) }}" required></label><label for="account-email">E-mail<input id="account-email" type="email" name="email" value="{{ old('email',$user->email) }}" required></label><button type="submit">Salvar alterações</button></form></section>
<section class="module-card"><h2>Alterar senha</h2><form method="post" action="{{ route('account.password') }}">@csrf @method('PUT')<label for="current-password">Senha atual<input id="current-password" type="password" name="current_password" required></label><label for="new-password">Nova senha<input id="new-password" type="password" name="password" required></label><label for="password-confirmation">Confirmar nova senha<input id="password-confirmation" type="password" name="password_confirmation" required></label><button type="submit">Atualizar senha</button></form></section>
@endsection
