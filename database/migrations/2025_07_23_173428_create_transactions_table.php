<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('price_per_day')->unsigned()->default(0);
            $table->integer('total_days')->unsigned()->default(0);
            $table->integer('fee')->unsigned()->default(0);
            $table->integer('total_price')->unsigned()->default(0);
            $table->enum('payment_method', ['down_payment', 'full_payment']);
            $table->enum('payment_status', ['waiting', 'approved', 'canceled'])->default('waiting');
            $table->date('transaction_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
