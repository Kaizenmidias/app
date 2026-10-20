<?php

namespace App\Console\Commands;

use App\Support\TestingTargetGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TestingMigrate extends Command
{
    protected $signature = 'testing:migrate';
    protected $description = 'Executa migrations somente no MySQL isolado de testes';

    public function handle(): int
    {
        try { TestingTargetGuard::validateConnection(DB::connection()); }
        catch (\Throwable) { $this->error('Conexão de teste rejeitada.'); return self::FAILURE; }
        return Artisan::call('migrate', ['--force' => true]) === 0 ? self::SUCCESS : self::FAILURE;
    }
}
