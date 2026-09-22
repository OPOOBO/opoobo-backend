<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('review_status', 20)->default('pending')->after('is_active');
            $table->text('review_notes')->nullable()->after('review_status');
            $table->timestamp('reviewed_at')->nullable()->after('review_notes');
            $table->string('reviewed_by', 100)->nullable()->after('reviewed_at');
            $table->boolean('admin_tested')->default(false);
            $table->timestamp('admin_tested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'review_status',
                'review_notes',
                'reviewed_at',
                'reviewed_by',
                'admin_tested',
                'admin_tested_at',
            ]);
        });
    }
};
