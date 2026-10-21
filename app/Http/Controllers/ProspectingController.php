<?php
namespace App\Http\Controllers;
use App\Jobs\RunScrapingJob;
use App\Models\ProspectingJob;
use Illuminate\Http\Request;
use App\Services\ScraperClient;

class ProspectingController extends Controller
{
    public function __invoke(Request $request)
    {
        $html = file_get_contents(resource_path('views/prospecting.html'));
        $shell = view("components.scraper-sidebar")->render();
        $shellVersion = filemtime(public_path('scraper-shell.css'));
        $prospectingVersion = filemtime(public_path('prospecting-shell.css'));
        $html = str_replace('<body>', '<body class="prospecting-embedded">'.$shell, $html);
        // Keep the original panel as the scraper's functional surface. Its
        // legacy header is hidden by scoped CSS; IDs and event targets remain.
        $html = str_replace('<aside class="sidebar glass" id="panel">', '<aside class="sidebar glass prospecting-functional-panel" id="panel">', $html);
        $html = str_replace(
            '</head>',
            '<link rel="stylesheet" href="'.asset('scraper-shell.css').'?v='.$shellVersion.'"><link rel="stylesheet" href="'.asset('prospecting-shell.css').'?v='.$prospectingVersion.'"></head>',
            $html,
        );
        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')->cookie('XSRF-TOKEN', csrf_token(), 120, '/', null, app()->environment('production'), false, false, 'lax');
    }    public function store(Request $request) {
        $data=$request->validate(['keywords'=>'required|array|min:1','keywords.*'=>'string|max:255','location'=>'nullable|string|max:255','latitude'=>'nullable|numeric','longitude'=>'nullable|numeric','search_parameters'=>'nullable|array']);
        $job=$request->user()->prospectingJobs()->create(['keywords'=>$data['keywords'],'location'=>$data['location']??null,'latitude'=>$data['latitude']??null,'longitude'=>$data['longitude']??null,'search_parameters'=>$data['search_parameters']??[],'status'=>'pending']);
        RunScrapingJob::dispatch($job->id); return response()->json(['id'=>$job->id,'status'=>$job->status],202);
    }
    public function index(Request $request) { return $request->user()->prospectingJobs()->latest()->paginate(20); }
    public function show(Request $request, ProspectingJob $job) { $this->authorize('view', $job); return $job->load('leads'); }
    public function legacyIndex(Request $request) { return response()->json($request->user()->prospectingJobs()->latest()->get()->map(fn($j)=>['ID'=>$j->id,'Name'=>$j->location ?: 'Busca','Date'=>$j->created_at,'Status'=>$j->status,'Data'=>array_merge($j->search_parameters??[],['keywords'=>$j->keywords,'lat'=>$j->latitude,'lon'=>$j->longitude,'radius'=>($j->search_parameters['radius']??0),'depth'=>($j->search_parameters['depth']??5),'email'=>($j->search_parameters['email']??false)])])); }
    public function legacyStore(Request $request) { $data=$request->validate(['keywords'=>'required|array|min:1','lat'=>'required','lon'=>'required','max_time'=>'required|integer','depth'=>'nullable|integer','radius'=>'nullable|integer','email'=>'nullable|boolean','name'=>'nullable|string']); $job=$request->user()->prospectingJobs()->create(['keywords'=>$data['keywords'],'location'=>$data['name']??null,'latitude'=>$data['lat'],'longitude'=>$data['lon'],'search_parameters'=>$data,'status'=>'pending']); \App\Jobs\RunScrapingJob::dispatch($job->id); return response()->json(['id'=>$job->id],201); }
    public function legacyStatus(Request $request, ProspectingJob $job) { $this->authorize('view', $job); return response()->json(['ID'=>$job->id,'Name'=>$job->location,'Date'=>$job->created_at,'Status'=>$job->status,'Data'=>array_merge($job->search_parameters??[],['keywords'=>$job->keywords,'lat'=>$job->latitude,'lon'=>$job->longitude])]); }
    public function legacyDownload(Request $request, ProspectingJob $job) { $this->authorize('view', $job); $rows=$job->leads()->get(); $head=['title','phone','emails','website','category','address','latitude','longitude','place_id','review_rating','review_count']; $out=fopen('php://temp','r+'); fputcsv($out,$head); foreach($rows as $lead){$r=$lead->raw_data??[]; fputcsv($out,array_map(fn($k)=>$r[$k]??match($k){'title'=>$lead->name,'phone'=>$lead->phone,'emails'=>$lead->email,'website'=>$lead->website,'category'=>$lead->category,'address'=>$lead->address,'latitude'=>$lead->latitude,'longitude'=>$lead->longitude,'place_id'=>$lead->external_place_id,default=>''},$head));} rewind($out); return response(stream_get_contents($out),200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename="kaizen-job-'.$job->id.'.csv"']); }
    public function legacyDestroy(Request $request, ProspectingJob $job) { $this->authorize('delete', $job); if(in_array($job->status,['working','pending','unknown'],true)){ $job->update(['status'=>'cancel_requested','cancel_requested_at'=>now()]); return response()->json(['status'=>'cancel_requested'],202); } $job->delete(); return response()->noContent(); }
}
