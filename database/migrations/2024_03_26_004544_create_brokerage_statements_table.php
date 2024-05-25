<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brokerage_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->constrained('brokers');
            $table->foreignId('user_id')->constrained('users');
            $table->string('note_number')->unique();
            $table->date('trade_date');
            $table->double('fees_and_taxes'); // Soma das taxas e impostos
            $table->double('net_operations_value'); // Soma das taxas e impostos
            $table->enum('status', ['ANEXADO', 'PROCESSANDO', 'PROCESSADO COM SUCESSO', 'FALHA NO PROCESSAMENTO']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brokerage_statements');
    }
};
