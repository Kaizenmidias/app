<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $html = file_get_contents(resource_path('views/panel.blade.php'));
        $sidebar = view('components.scraper-sidebar')->render();
        $shellVersion = filemtime(public_path('scraper-shell.css'));
        $dashboardVersion = filemtime(public_path('dashboard-shell.css'));
        $html = str_replace('<body>', '<body class="dashboard-embedded">'.$sidebar, $html);
        $html = str_replace('<aside class="sidebar liquid">', '<aside class="sidebar liquid dashboard-legacy-sidebar" style="display:none!important;visibility:hidden!important">', $html);
        $html = str_replace(
            '</head>',
            '<link rel="stylesheet" href="'.asset('scraper-shell.css').'?v='.$shellVersion.'"><link rel="stylesheet" href="'.asset('dashboard-shell.css').'?v='.$dashboardVersion.'"></head>',
            $html,
        );
        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')->cookie('XSRF-TOKEN', csrf_token(), 120, '/', null, app()->environment('production'), false, false, 'lax');
    }
}
