<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prospecting_job_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospecting_job_id')->constrained('prospecting_jobs')->cascadeOnDelete();
            $table->string('provider', 32)->default('apify');
            $table->string('external_id')->nullable()->index();
            $table->string('status', 32)->default('submitting')->index();
            $table->string('request_hash', 64)->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('prospecting_job_attempts'); }
};
