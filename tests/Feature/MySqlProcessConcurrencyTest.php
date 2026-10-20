<?php

namespace Tests\Feature;

use Tests\TestCase;

class MySqlProcessConcurrencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (
            (getenv('APP_ENV') ?: env('APP_ENV')) !== 'testing'
            || (getenv('MYSQL_TEST_ENABLED') ?: env('MYSQL_TEST_ENABLED')) !== 'true'
            || (getenv('DB_CONNECTION') ?: env('DB_CONNECTION')) !== 'mysql'
            || (getenv('DB_DATABASE') ?: env('DB_DATABASE')) !== (getenv('MYSQL_TEST_DATABASE') ?: env('MYSQL_TEST_DATABASE'))
            || in_array((getenv('DB_DATABASE') ?: env('DB_DATABASE')), ['kaizen_partners', 'production', 'prod'], true)
        ) {
            $this->markTestSkipped('MySQL de teste não habilitado.');
        }
    }

    /** @return array{scenario:string,workers:array<int,array{result:string}>} */
    private function runScenario(string $scenario): array
    {
        $env = [
            'APP_ENV' => 'testing',
            'MYSQL_TEST_ENABLED' => 'true',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '13306',
            'DB_DATABASE' => 'kaizen_partners_test',
            'MYSQL_TEST_DATABASE' => 'kaizen_partners_test',
            'DB_USERNAME' => 'kaizen_test',
            'DB_PASSWORD' => getenv('DB_PASSWORD') ?: (string) config('database.connections.mysql.password'),
        ];
        $command = PHP_BINARY.' '.escapeshellarg(base_path('tests/Support/run-mysql-concurrency.php')).' '.escapeshellarg($scenario);
        $pipes = [];
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path(), $env);
        $this->assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
        $this->assertSame(0, $exit, trim($error));
        $result = json_decode(trim($output), true);
        $this->assertIsArray($result, $error ?: $output);

        return $result;
    }

    public function test_two_processes_only_one_capacity_winner(): void
    {
        $result = $this->runScenario('capacity');
        $this->assertSame(1, count(array_filter($result['workers'], fn (array $worker): bool => $worker['result'] === 'won')));
        $this->assertSame(1, count(array_filter($result['workers'], fn (array $worker): bool => $worker['result'] === 'lost')));
    }

    public function test_two_processes_only_one_claim_winner(): void
    {
        $result = $this->runScenario('claim');
        $this->assertSame(1, count(array_filter($result['workers'], fn (array $worker): bool => $worker['result'] === 'won')));
        $this->assertSame(1, count(array_filter($result['workers'], fn (array $worker): bool => $worker['result'] === 'lost')));
    }

    public function test_unique_external_place_conflict_is_observed(): void
    {
        $result = $this->runScenario('unique');
        $this->assertSame(1, count(array_filter($result['workers'], fn (array $worker): bool => $worker['result'] === 'inserted')));
        $this->assertSame(1, count(array_filter($result['workers'], fn (array $worker): bool => $worker['result'] === 'conflict')));
    }
}
