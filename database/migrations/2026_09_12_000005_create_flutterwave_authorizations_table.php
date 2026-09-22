<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flutterwave_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('authorization_code');
            $table->string('card_type');
            $table->string('last_four');
            $table->string('exp_month')->nullable();
            $table->string('exp_year')->nullable();
            $table->string('bank_name')->nullable();
            $table->boolean('is_reusable')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'authorization_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flutterwave_authorizations');
    }
};
