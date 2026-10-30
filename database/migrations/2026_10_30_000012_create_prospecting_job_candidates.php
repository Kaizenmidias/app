<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prospecting_job_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospecting_job_id')->constrained('prospecting_jobs')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('external_place_id');
            $table->string('disposition', 24)->index();
            $table->timestamps();
            $table->unique(['prospecting_job_id', 'external_place_id'], 'prospecting_candidate_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('prospecting_job_candidates'); }
};
