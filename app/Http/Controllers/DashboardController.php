<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $html = file_get_contents(resource_path('views/panel.blade.php'));
        $sidebar = view('components.scraper-sidebar')->render();
        $header = view('components.account-header')->render();
        $html = str_replace('<body>', '<body class="dashboard-embedded">'.$sidebar.$header, $html);
        $html = str_replace('<aside class="sidebar liquid">', '<aside class="sidebar liquid dashboard-legacy-sidebar" style="display:none!important;visibility:hidden!important">', $html);
        $html = str_replace('</head>', '<link rel="stylesheet" href="/scraper-shell.css"><link rel="stylesheet" href="/dashboard-shell.css"></head>', $html);
        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')->cookie('XSRF-TOKEN', csrf_token(), 120, '/', null, app()->environment('production'), false, false, 'lax');
    }
}
