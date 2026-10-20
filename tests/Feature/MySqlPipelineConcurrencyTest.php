<?php

namespace Tests\Feature;

use Tests\TestCase;

class MySqlPipelineConcurrencyTest extends TestCase
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

    /** @return array<string,mixed> */
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

    public function test_two_processes_sending_same_lead_produce_one_opportunity(): void
    {
        $result = $this->runScenario('pipeline_send');
        $workers = array_column($result['workers'], 'result');
        $this->assertSame(1, count(array_filter($workers, fn (string $value): bool => $value === 'inserted')));
        $this->assertSame(1, count(array_filter($workers, fn (string $value): bool => $value === 'conflict')));
        $this->assertSame(1, $result['opportunity_count']);
    }

    public function test_two_processes_moving_same_opportunity_keep_one_valid_stage(): void
    {
        $result = $this->runScenario('pipeline_move');
        $this->assertSame(['moved', 'moved'], array_values(array_column($result['workers'], 'result')));
        $this->assertContains($result['opportunity_stage'], $result['destination_stage_ids']);
    }

    public function test_stage_deletion_and_move_are_serialized(): void
    {
        $result = $this->runScenario('pipeline_delete_move');
        $this->assertSame(['deleted', 'moved'], array_values(array_column($result['workers'], 'result')));
        $this->assertFalse($result['source_stage_exists']);
        $this->assertSame($result['destination_stage_ids'][0], $result['opportunity_stage']);
    }

    public function test_concurrent_reorders_leave_unique_positions(): void
    {
        $result = $this->runScenario('pipeline_reorder');
        $this->assertSame(['reordered', 'reordered'], array_values(array_column($result['workers'], 'result')));
        $this->assertTrue($result['positions_unique']);
        $this->assertSame([0, 1, 2], $result['positions']);
    }
}
