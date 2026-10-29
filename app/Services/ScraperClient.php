<?php

namespace App\Services;

use App\Models\{Lead, ProspectingJob};
use Illuminate\Support\Facades\{DB, Http};
use RuntimeException;

class ScraperClient
{
    private function legacyHttp()
    {
        $http = Http::baseUrl(config('kaizen.scraper.base_url'))->timeout(config('kaizen.scraper.timeout'));
        return config('kaizen.scraper.api_key') ? $http->withHeaders(['X-API-Key' => config('kaizen.scraper.api_key')]) : $http;
    }
    private function apify(ProspectingJob $job)
    {
        $token = $job->user?->apify_api_token;
        if (!$token) throw new RuntimeException('Configure seu token da Apify em Minha conta antes de iniciar uma busca.');
        return Http::baseUrl(config('kaizen.apify.base_url', 'https://api.apify.com/v2'))->withToken($token)->timeout((int) config('kaizen.apify.timeout', 90));
    }

    public function create(ProspectingJob $job): string
    {
        if (!$job->user?->apify_api_token) {
            $response = $this->legacyHttp()->post('/api/v1/jobs', array_merge($job->search_parameters ?? [], ['name' => 'kaizen-'.$job->id, 'keywords' => $job->keywords, 'lat' => (string) $job->latitude, 'lon' => (string) $job->longitude]));
            if ($response->failed() || !$response->json('id')) throw new RuntimeException('Scraper rejected the job.');
            return $response->json('id');
        }
        $params = $job->search_parameters ?? [];
        $max = (int) ($params['max_results'] ?? match ((int) ($params['depth'] ?? 5)) { 1 => 20, 4 => 50, 8 => 100, default => 50 });
        $terms = array_values(array_filter(array_map('strval', (array) $job->keywords), fn ($value) => trim($value) !== ''));
        if (!$terms) {
            $terms = array_values(array_filter(array_map('strval', (array) data_get($params, 'keywords', [])), fn ($value) => trim($value) !== ''));
        }
        if (!$terms && filled($job->location)) $terms = [(string) $job->location];
        if (!$terms) throw new RuntimeException('A busca precisa de pelo menos um termo.');
        $input = [
            'searchStringsArray' => $terms,
            'categoryFilterWords' => $terms,
            'locationQuery' => $job->location,
            'maxCrawledPlacesPerSearch' => $max,
            'language' => ($params['lang'] ?? 'pt') === 'pt' ? 'pt-BR' : ($params['lang'] ?? 'en'),
            'scrapePlaceDetailPage' => true,
            'scrapeContacts' => (bool) ($params['email'] ?? false),
            'maxImages' => 0,
            'maxReviews' => 0,
            'includeWebResults' => false,
            'scrapeDirectories' => false,
        ];
        $response = $this->apify($job)->post('/acts/'.config('kaizen.apify.actor', 'compass~crawler-google-places').'/runs', $input);
        if ($response->failed() || !$response->json('data.id') || !$response->json('data.defaultDatasetId')) throw new RuntimeException('A Apify recusou a busca.');
        return 'apify:'.$response->json('data.id').':'.$response->json('data.defaultDatasetId');
    }

    public function status(ProspectingJob $job): array
    {
        if (!str_starts_with((string) $job->scraper_job_id, 'apify:')) {
            $response = $this->legacyHttp()->get('/api/v1/jobs/'.$job->scraper_job_id);
            if ($response->failed()) throw new RuntimeException('Scraper status unavailable.');
            return $response->json();
        }
        [, $run] = explode(':', $job->scraper_job_id, 3);
        $response = $this->apify($job)->get('/actor-runs/'.$run);
        if ($response->failed()) throw new RuntimeException('Status da Apify indisponível.');
        $status = strtoupper((string) $response->json('data.status', 'RUNNING'));
        return ['status' => match ($status) { 'SUCCEEDED' => 'completed', 'FAILED', 'ABORTED', 'TIMED-OUT' => 'failed', default => 'working' }, 'progress' => $status === 'SUCCEEDED' ? 100 : 50];
    }

    public function import(ProspectingJob $job, ?string $claimToken = null, ?callable $renewClaim = null): ScraperImportOutcome
    {
        if (!str_starts_with((string) $job->scraper_job_id, 'apify:')) return $this->importLegacy($job, $claimToken, $renewClaim);
        [, , $dataset] = explode(':', $job->scraper_job_id, 3);
        $response = $this->apify($job)->get('/datasets/'.$dataset.'/items', ['format' => 'json', 'clean' => 'true']);
        if ($response->failed()) throw new RuntimeException('Resultados da Apify indisponíveis.');
        $items = $response->json();
        if (!is_array($items)) throw new RuntimeException('Formato de resultado inválido na Apify.');
        $rows = array_map(fn ($item) => [
            'place_id' => $item['placeId'] ?? $item['place_id'] ?? $item['cid'] ?? null,
            'title' => $item['title'] ?? $item['name'] ?? '',
            'phone' => $item['phone'] ?? $item['phoneUnformatted'] ?? null,
            'emails' => is_array($item['emails'] ?? null) ? implode(', ', $item['emails']) : ($item['email'] ?? null),
            'website' => $item['website'] ?? $item['websiteUrl'] ?? null,
            'category' => is_array($item['categories'] ?? null) ? implode(', ', $item['categories']) : ($item['categoryName'] ?? null),
            'address' => $item['address'] ?? $item['street'] ?? null,
            'latitude' => data_get($item, 'location.lat', $item['latitude'] ?? null),
            'longitude' => data_get($item, 'location.lng', $item['longitude'] ?? null),
            'raw_data' => $item,
        ], $items);
        return $this->persistRows($job, $rows, $claimToken, $renewClaim);
    }

    private function importLegacy(ProspectingJob $job, ?string $claimToken, ?callable $renewClaim): ScraperImportOutcome
    {
        $response = $this->legacyHttp()->get('/api/v1/jobs/'.$job->scraper_job_id.'/download');
        if ($response->failed()) throw new RuntimeException('Scraper results unavailable.');
        $stream = fopen('php://temp', 'r+'); fwrite($stream, $response->body()); rewind($stream);
        $head = fgetcsv($stream); if ($head === false || $head === [null]) { fclose($stream); return new ScraperImportOutcome(0, 0, 0, true); }
        $rows = []; $rejected = 0; $max = (int) config('kaizen.scraper.import_max_rows', 10000); $n = 0;
        while (($row = fgetcsv($stream)) !== false) { if ($row === [null] || (count($row) === 1 && trim((string) $row[0]) === '')) continue; if (++$n > $max) throw new RuntimeException('Scraper results exceed the configured row limit.'); if (count($row) > count($head)) { $rejected++; continue; } $data = array_combine($head, array_pad($row, count($head), '')); if (!$data) { $rejected++; continue; } $place = trim((string) ($data['place_id'] ?? '')); $cid = trim((string) ($data['cid'] ?? '')); $rows[] = ['place_id' => $place !== '' ? $place : ($cid !== '' ? $cid : null), 'title' => $data['title'] ?? '', 'phone' => $data['phone'] ?? null, 'emails' => $data['emails'] ?? null, 'website' => $data['website'] ?? null, 'category' => $data['category'] ?? null, 'address' => $data['address'] ?? null, 'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null, 'raw_data' => $data]; }
        fclose($stream);
        $outcome = $this->persistRows($job, $rows, $claimToken, $renewClaim);
        return new ScraperImportOutcome($outcome->accepted, $outcome->updated, $outcome->rejected + $rejected, $outcome->complete, $outcome->ownershipLost);
    }

    private function persistRows(ProspectingJob $job, array $rows, ?string $claimToken, ?callable $renewClaim): ScraperImportOutcome
    {
        $accepted = $updated = $rejected = 0;
        foreach ($rows as $data) {
            $external = trim((string) ($data['place_id'] ?? ''));
            if ($external === '') { $rejected++; continue; }
            if ($renewClaim && !$renewClaim()) return new ScraperImportOutcome($accepted, $updated, $rejected, false, true);
            $old = DB::transaction(function () use ($job, $claimToken, $external, $data) {
                if ($claimToken && !ProspectingJob::whereKey($job->id)->where('polling_claim_token', $claimToken)->lockForUpdate()->exists()) return null;
                $lead = Lead::firstOrCreate(['user_id' => $job->user_id, 'external_place_id' => $external], ['lead_list_id' => $job->lead_list_id, 'prospecting_job_id' => $job->id, 'name' => $data['title'] ?? '', 'phone' => $data['phone'] ?? null, 'email' => $data['emails'] ?? null, 'website' => $data['website'] ?? null, 'category' => $data['category'] ?? null, 'address' => $data['address'] ?? null, 'latitude' => is_numeric($data['latitude'] ?? null) ? $data['latitude'] : null, 'longitude' => is_numeric($data['longitude'] ?? null) ? $data['longitude'] : null, 'source' => 'apify_google_places', 'raw_data' => $data['raw_data'] ?? $data]);
                return !$lead->wasRecentlyCreated;
            });
            if ($old === null) return new ScraperImportOutcome($accepted, $updated, $rejected, false, true);
            $old ? $updated++ : $accepted++;
        }
        return new ScraperImportOutcome($accepted, $updated, $rejected, true);
    }
}
