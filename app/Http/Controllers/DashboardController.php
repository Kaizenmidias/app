<?php

namespace App\Http\Controllers;

use App\Models\LeadActivity;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        if ($request->routeIs('dashboard')) {
            $period = in_array($request->query('period'), ['yesterday','today','7d','30d','year'], true) ? $request->query('period') : '30d';
            $end = Carbon::today()->endOfDay();
            $start = match ($period) { 'yesterday'=>Carbon::yesterday()->startOfDay(), 'today'=>Carbon::today()->startOfDay(), '7d'=>Carbon::today()->subDays(6)->startOfDay(), 'year'=>Carbon::today()->startOfYear(), default=>Carbon::today()->subDays(29)->startOfDay() };
            $leads = $user->leads()->whereBetween('created_at', [$start,$end])->count();
            $activity = LeadActivity::where('user_id',$user->id)->whereBetween('created_at',[$start,$end])->get(['type','created_at']);
            $contacts = $activity->whereIn('type',['contact','call','message','whatsapp','email'])->count();
            $meetings = $activity->whereIn('type',['meeting','appointment','meeting_scheduled'])->count();
            $closures = $activity->whereIn('type',['closed','won','conversion'])->count();
            $series = collect(CarbonPeriod::create($start->copy()->startOfDay(),'1 day',$end->copy()->startOfDay()))->map(function(Carbon $day) use ($user,$activity){$date=$day->toDateString();$count=function(array $types)use($activity,$date){return $activity->whereIn('type',$types)->filter(fn($a)=>$a->created_at->toDateString()===$date)->count();};return ['date'=>$date,'leads'=>$user->leads()->whereDate('created_at',$date)->count(),'contacts'=>$count(['contact','call','message','whatsapp','email']),'meetings'=>$count(['meeting','appointment','meeting_scheduled']),'closures'=>$count(['closed','won','conversion'])];})->values();
            return response()->view('dashboard.index', compact('period','start','end','leads','contacts','meetings','closures','series'));
        }
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
