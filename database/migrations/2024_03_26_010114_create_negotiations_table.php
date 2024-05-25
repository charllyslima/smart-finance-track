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
        Schema::create('negotiations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brokerage_statement_id')->constrained('brokerage_statements');
            $table->string('acronym', 4);
            $table->foreign('acronym')->references('acronym')->on('fi_assets');
            $table->double('quantity');
            $table->double('price');
            $table->double('total');
            $table->enum('type', ['purchase', 'sale']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('negotiations');
    }
};
