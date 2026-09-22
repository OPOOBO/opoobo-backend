<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('test_mode_url', 500)->nullable()->after('module_url');
            $table->json('screenshots')->nullable()->after('icon_path');
            $table->json('required_bridge_apis')->nullable()->after('screenshots');
            $table->boolean('ssl_valid')->default(false);
            $table->boolean('url_loads')->default(false);
            $table->boolean('bridge_detected')->default(false);
            $table->string('last_preflight_status', 20)->nullable()->after('bridge_detected');
            $table->timestamp('last_preflight_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'test_mode_url',
                'screenshots',
                'required_bridge_apis',
                'ssl_valid',
                'url_loads',
                'bridge_detected',
                'last_preflight_status',
                'last_preflight_at',
            ]);
        });
    }
};
