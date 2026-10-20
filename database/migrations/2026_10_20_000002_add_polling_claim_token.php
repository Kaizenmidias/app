<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('prospecting_jobs', function(Blueprint $table){ $table->uuid('polling_claim_token')->nullable()->after('polling_claimed_at')->index(); }); }
 public function down(): void { Schema::table('prospecting_jobs', function(Blueprint $table){ $table->dropIndex(['polling_claim_token']); $table->dropColumn('polling_claim_token'); }); }
};
