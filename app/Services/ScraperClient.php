<?php
namespace App\Services;
use App\Models\Lead;
use App\Models\ProspectingJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class ScraperClient
{
    private function http() { $http=Http::baseUrl(config('kaizen.scraper.base_url'))->timeout(config('kaizen.scraper.timeout')); if($key=config('kaizen.scraper.api_key')) $http=$http->withHeaders(['X-API-Key'=>$key]); return $http; }
    public function create(ProspectingJob $job): string { $r=$this->http()->post('/api/v1/jobs',array_merge($job->search_parameters??[],['name'=>'kaizen-'.$job->id,'keywords'=>$job->keywords,'lat'=>(string)$job->latitude,'lon'=>(string)$job->longitude])); if($r->failed()||!$r->json('id')) throw new RuntimeException('Scraper rejected the job.'); return $r->json('id'); }
    public function status(ProspectingJob $job): array { $r=$this->http()->get('/api/v1/jobs/'.$job->scraper_job_id); if($r->failed()) throw new RuntimeException('Scraper status unavailable.'); return $r->json(); }
    public function import(ProspectingJob $job, ?string $claimToken=null, ?callable $renewClaim=null): int {
        $r=$this->http()->get('/api/v1/jobs/'.$job->scraper_job_id.'/download'); if($r->failed()) throw new RuntimeException('Scraper results unavailable.');
        $rows=array_map('str_getcsv',preg_split('/\r\n|\n|\r/',trim($r->body()))); if(!$rows) return 0; $head=array_shift($rows); $count=0;
        foreach($rows as $row){ if(count($row)<2) continue; $data=array_combine($head,array_pad($row,count($head),'')); if(!$data) continue; $external=$data['place_id']??$data['cid']??null; if(!$external) continue;
            if($renewClaim) { $renewed=$renewClaim(); if(!$renewed) break; }
            $written=DB::transaction(function() use($job,$claimToken,$external,$data){
                if($claimToken && !ProspectingJob::whereKey($job->id)->where('polling_claim_token',$claimToken)->lockForUpdate()->exists()) return false;
                Lead::updateOrCreate(['user_id'=>$job->user_id,'external_place_id'=>$external],['prospecting_job_id'=>$job->id,'name'=>$data['title']??'','phone'=>$data['phone']??null,'email'=>$data['emails']??null,'website'=>$data['website']??null,'category'=>$data['category']??null,'address'=>$data['address']??null,'latitude'=>is_numeric($data['latitude']??null)?$data['latitude']:null,'longitude'=>is_numeric($data['longitude']??null)?$data['longitude']:null,'source'=>'google_maps','raw_data'=>$data]); return true;
            }); if(!$written) break; $count++; }
        return $count;
    }
}
