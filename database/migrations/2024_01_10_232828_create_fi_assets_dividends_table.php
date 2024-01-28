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
        Schema::create('fi_assets_dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fi_asset_id')->constrained('fi_assets');
            $table->double('value');
            $table->date('base_date');
            $table->date('payment_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fi_assets_rendiments');
    }
};
