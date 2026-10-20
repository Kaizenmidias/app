@extends('layouts.kaizen')
@section('content')
<h1>Recuperar senha</h1>
@if(session('status'))
<p class="success">{{ session('status') }}</p>
@endif
@if($errors->any())
<div class="error">{{ $errors->first() }}</div>
@endif
<form method="post" action="{{ route('password.email') }}">
@csrf
<label>E-mail</label><input name="email" type="email" required><button>Enviar link</button>
</form>
<div class="links"><a href="{{ route('login') }}">Voltar ao login</a></div>
@endsection
