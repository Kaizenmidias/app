<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('instagram')->nullable()->after('website');
            $table->string('linkedin')->nullable()->after('instagram');
            $table->string('sector')->nullable()->after('category');
            $table->decimal('estimated_revenue', 15, 2)->nullable()->after('sector');
            $table->unsignedInteger('employee_count')->nullable()->after('estimated_revenue');
            $table->decimal('budget_value', 15, 2)->nullable()->after('employee_count');
            $table->json('tags')->nullable()->after('budget_value');
            $table->foreignId('responsible_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('responsible_user_id');
            $table->dropColumn(['instagram','linkedin','sector','estimated_revenue','employee_count','budget_value','tags']);
        });
    }
};
