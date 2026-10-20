@extends('layouts.scraper-shell')
@section('title', 'Minha conta · Parceiros Kaizen')
@section('content')
<header class="module-header"><div><p class="module-eyebrow">Conta</p><h1>Minha conta</h1><p class="module-description">Gerencie seus dados de acesso.</p></div></header>
@if(session('status'))<p class="form-status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="form-errors"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="module-card"><h2>Dados pessoais</h2><form method="post" action="{{ route('account.update') }}">@csrf @method('PUT')<label>Nome<input name="name" value="{{ old('name',$user->name) }}" required></label><label>E-mail<input type="email" name="email" value="{{ old('email',$user->email) }}" required></label><button type="submit">Salvar dados</button></form></section>
<section class="module-card"><h2>Alterar senha</h2><form method="post" action="{{ route('account.password') }}">@csrf @method('PUT')<label>Senha atual<input type="password" name="current_password" required></label><label>Nova senha<input type="password" name="password" required></label><label>Confirmar nova senha<input type="password" name="password_confirmation" required></label><button type="submit">Atualizar senha</button></form></section>
@endsection
