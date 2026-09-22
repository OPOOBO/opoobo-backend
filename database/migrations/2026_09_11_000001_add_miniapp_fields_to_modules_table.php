<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mini-app store registry fields. A module with a module_url opens
     * inside the OPOOBO WebView container instead of native screens.
     */
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('module_url', 500)->nullable()->after('website_url');
            $table->string('version', 20)->default('1.0.0')->after('module_url');
            $table->json('permissions')->nullable()->after('version');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->integer('sort_order')->default(0)->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['module_url', 'version', 'permissions', 'is_featured', 'sort_order']);
        });
    }
};
