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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade'); // Menghubungkan dengan tabel transaksi
            $table->string('payment_method')->nullable();
            $table->string('payment_status');
            $table->dateTime('payment_date')->nullable();
            $table->dateTime('expired_date')->nullable();
            $table->integer('total_price'); // Untuk harga total dengan format desimal
            $table->string('token')->nullable(); // Token bisa kosong
            $table->timestamps();
            $table->softDeletes(); // Untuk fitur soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
