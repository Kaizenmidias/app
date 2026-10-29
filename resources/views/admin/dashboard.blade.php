@extends('layouts.scraper-shell')
@section('title', 'Administração · Parceiros Kaizen')
@push('styles')<link rel="stylesheet" href="{{ asset('admin.css') }}?v={{ filemtime(public_path('admin.css')) }}">@endpush
@section('content')
<header class="module-header admin-header"><div><p class="module-eyebrow">Administração</p><h1>Visão administrativa</h1><p class="module-description">Acompanhe usuários e a utilização geral do Parceiros Kaizen.</p></div><a class="admin-button admin-button-primary" href="{{ route('admin.users') }}">Gerenciar usuários</a></header>
<section class="admin-metrics" aria-label="Resumo administrativo"><article><span>Usuários</span><strong>{{ $users }}</strong></article><article><span>Ativos</span><strong>{{ $active }}</strong></article><article><span>Bloqueados</span><strong>{{ $blocked }}</strong></article><article><span>Administradores</span><strong>{{ $admins }}</strong></article><article><span>Leads</span><strong>{{ $leads }}</strong></article><article><span>Buscas</span><strong>{{ $jobs }}</strong></article></section>
<section class="admin-panel"><div><p class="module-eyebrow">Gestão</p><h2>Usuários e acessos</h2><p>Cadastre contas, controle status, função e módulos disponíveis para cada usuário.</p></div><a class="admin-button admin-button-secondary" href="{{ route('admin.users') }}">Abrir usuários</a></section>
@endsection
