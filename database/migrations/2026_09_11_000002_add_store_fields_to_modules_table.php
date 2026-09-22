<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mini-app store registry: category, developer info, install count.
     */
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('sort_order');
            $table->string('developer_name', 200)->nullable()->after('category');
            $table->string('developer_url', 500)->nullable()->after('developer_name');
            $table->unsignedInteger('install_count')->default(0)->after('developer_url');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['category', 'developer_name', 'developer_url', 'install_count']);
        });
    }
};
