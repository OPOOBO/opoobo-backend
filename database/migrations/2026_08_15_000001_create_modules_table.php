<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->string('description')->nullable();
            $table->string('api_base_url', 500);
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('check_endpoint', 200)->nullable();
            $table->string('create_endpoint', 200)->nullable();
            $table->string('change_password_endpoint', 200)->nullable();
            $table->json('required_fields')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
