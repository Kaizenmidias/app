<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prospecting_jobs', function (Blueprint $table) {
            $table->timestamp('submission_intent_at')->nullable()->index();
            $table->uuid('submission_attempt_token')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('prospecting_jobs', function (Blueprint $table) {
            $table->dropUnique(['submission_attempt_token']);
            $table->dropColumn(['submission_intent_at', 'submission_attempt_token']);
        });
    }
};
