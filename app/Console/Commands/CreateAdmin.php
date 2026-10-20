<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
class CreateAdmin extends Command
{
    protected $signature = 'kaizen:create-admin {email} {--name=}';
    protected $description = 'Create the first Kaizen administrator using an interactive password prompt';
    public function handle(): int
    {
        $email = strtolower($this->argument('email'));
        if (User::where('email',$email)->exists()) { $this->error('E-mail já cadastrado.'); return self::FAILURE; }
        $name = $this->option('name') ?: $this->ask('Nome completo');
        $password = $this->secret('Senha (não será exibida)');
        if (!$password || strlen($password) < 12) { $this->error('A senha precisa ter pelo menos 12 caracteres.'); return self::FAILURE; }
        User::create(['name'=>$name,'email'=>$email,'password'=>Hash::make($password),'role'=>'admin','status'=>'active','email_verified_at'=>now()]);
        $this->info('Administrador criado.'); return self::SUCCESS;
    }
}
