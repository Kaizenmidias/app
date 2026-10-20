<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scraper_capacity', function (Blueprint $table) { $table->unsignedTinyInteger('id')->primary(); $table->unsignedInteger('capacity'); $table->unsignedInteger('used')->default(0); $table->timestamps(); });
        DB::table('scraper_capacity')->insert(['id'=>1,'capacity'=>(int) env('SCRAPER_CONCURRENCY',1),'used'=>0,'created_at'=>now(),'updated_at'=>now()]);
        Schema::create('prospecting_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('scraper_job_id')->nullable()->index();
            $table->json('keywords');
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('search_parameters')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('progress')->default(0);
            $table->boolean('scraper_slot_acquired')->default(false)->index();
            $table->timestamp('polling_claimed_at')->nullable()->index();
            $table->timestamp('cancel_requested_at')->nullable();
            $table->softDeletes();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prospecting_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('website')->nullable();
            $table->string('category')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('source')->default('google_maps');
            $table->string('external_place_id')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'external_place_id']);
        });

        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
            $table->index(['user_id', 'lead_id']);
        });

        Schema::create('prospecting_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prospecting_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('format');
            $table->timestamps();
        });

        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('users')->restrictOnDelete();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('prospecting_exports');
        Schema::dropIfExists('lead_notes');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('prospecting_jobs');
        Schema::dropIfExists('scraper_capacity');
    }
};
