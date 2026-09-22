<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // card, bank
            $table->string('provider'); // visa, mastercard, gtbank, etc.
            $table->string('last_four');
            $table->string('expiry_month')->nullable();
            $table->string('expiry_year')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number_masked')->nullable(); // •••• 3456
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
