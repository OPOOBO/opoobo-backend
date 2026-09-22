<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_users', function (Blueprint $table) {
            $table->text('module_token')->nullable()->after('module_email');
        });
    }

    public function down(): void
    {
        Schema::table('module_users', function (Blueprint $table) {
            $table->dropColumn('module_token');
        });
    }
};
