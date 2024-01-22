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
        Schema::create('fi_assets_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fi_asset_id')->constrained('fi_assets');
            $table->enum('type', ['DESDOBRAMENTO', 'SUBINSCRIÇÃO']);
            $table->double('multiplier')->nullable();
            $table->double('price')->nullable();
            $table->unique(['fi_asset_id', 'type', 'created_at', 'multiplier']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fi_assets_events');
    }
};
