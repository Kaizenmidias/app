@extends('layouts.auth')
@section('content')
@if(session('status'))
<p class="auth-success">{{ session('status') }}</p>
@endif
@if($errors->any())
<div class="auth-error" role="alert">{{ $errors->first() }}</div>
@endif
<form method="post" action="{{ route('login') }}">
@csrf
<div class="auth-field">
<label class="auth-sr-only" for="email">E-mail</label>
<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg>
<input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="E-mail" autocomplete="username" required autofocus>
</div>
<div class="auth-field">
<label class="auth-sr-only" for="password">Senha</label>
<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4M12 14v3"/></svg>
<input id="password" name="password" type="password" placeholder="Senha" autocomplete="current-password" required>
</div>
<label class="auth-remember"><input type="checkbox" name="remember"> Lembrar-me</label>
<button class="auth-button" type="submit">Entrar <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12h18m-8-8 8 8-8 8"/></svg></button>
</form>
<div class="auth-links">
@if(filter_var(env('REGISTRATION_ENABLED', false), FILTER_VALIDATE_BOOL))<a href="{{ route('register') }}">Cadastre-se</a>@else<span class="auth-link-disabled" aria-disabled="true" title="Cadastro indisponível">Cadastre-se</span>@endif
<a href="{{ route('password.request') }}">Esqueceu sua senha?</a>
</div>
@endsection
