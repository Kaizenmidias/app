<?php
namespace App\Http\Controllers;

use App\Models\ProspectingExport;
use App\Models\ProspectingJob;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        return ProspectingExport::where('user_id', $request->user()->id)->latest()->paginate(25);
    }

    public function download(Request $request, ProspectingJob $job)
    {
        abort_unless($job->user_id === $request->user()->id, 404);
        $export = ProspectingExport::create(['user_id' => $request->user()->id, 'prospecting_job_id' => $job->id, 'format' => 'csv']);
        $head = ['name','phone','email','website','category','address','latitude','longitude','external_place_id'];
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $head);
        $job->leads()->chunk(500, function ($leads) use ($stream, $head) {
            foreach ($leads as $lead) {
                fputcsv($stream, array_map(fn ($key) => $lead->{$key}, $head));
            }
        });
        rewind($stream);
        return response(stream_get_contents($stream), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="kaizen-export-'.$export->id.'.csv"',
        ]);
    }
}
