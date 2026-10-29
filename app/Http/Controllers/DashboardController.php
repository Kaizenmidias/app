<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $jobs = $user->prospectingJobs()->get(['status', 'search_parameters']);
        $leads = $user->leads()->get(['phone', 'email', 'website', 'raw_data']);
        $stats = [
            'jobs' => $jobs->count(),
            'completed' => $jobs->where('status', 'ok')->count(),
            'busy' => $jobs->whereIn('status', ['working', 'pending'])->count(),
            'leads' => $leads->count(),
            'phone' => $leads->filter(fn ($lead) => filled($lead->phone))->count(),
            'email' => $leads->filter(fn ($lead) => filled($lead->email))->count(),
            'duplicates' => $jobs->sum(fn ($job) => (int) data_get($job->search_parameters, 'import_report.duplicates', 0)),
            'without_site' => $leads->filter(fn ($lead) => blank($lead->website))->count(),
            'whatsapp_validated' => $leads->filter(fn ($lead) => (bool) data_get($lead->raw_data, 'whatsapp_validated', false))->count(),
        ];
        $html = file_get_contents(resource_path('views/panel.blade.php'));
        $sidebar = view('components.scraper-sidebar')->render();
        $shellVersion = filemtime(public_path('scraper-shell.css'));
        $dashboardVersion = filemtime(public_path('dashboard-shell.css'));
        $panelScriptVersion = filemtime(public_path('app/painel.js'));
        $html = str_replace('<body>', '<body class="dashboard-embedded">'.$sidebar, $html);
        $html = str_replace('<aside class="sidebar liquid">', '<aside class="sidebar liquid dashboard-legacy-sidebar" style="display:none!important;visibility:hidden!important">', $html);
        $html = str_replace(
            '</head>',
            '<meta name="csrf-token" content="'.e(csrf_token()).'"><script>window.KAIZEN_STATS='.json_encode($stats, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';</script><link rel="stylesheet" href="'.asset('scraper-shell.css').'?v='.$shellVersion.'"><link rel="stylesheet" href="'.asset('dashboard-shell.css').'?v='.$dashboardVersion.'"></head>',
            $html,
        );
        $html = str_replace('/app/painel.js', '/app/painel.js?v='.$panelScriptVersion, $html);
        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')->header('Cache-Control', 'no-store, private')->cookie('XSRF-TOKEN', csrf_token(), 120, '/', null, app()->environment('production'), false, false, 'lax');
    }
}
