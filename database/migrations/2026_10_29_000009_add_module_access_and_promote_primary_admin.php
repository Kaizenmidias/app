<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('module_access')->nullable()->after('status');
        });

        DB::table('users')
            ->whereRaw('LOWER(email) = ?', ['agenciakaizendesign@gmail.com'])
            ->update(['role' => 'admin', 'status' => 'active']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('module_access');
        });
    }
};
