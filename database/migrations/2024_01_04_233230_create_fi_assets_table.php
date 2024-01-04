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
        Schema::create('fi_assets', function (Blueprint $table) {
            $table->id();
            $table->string("acronym", 4)->unique();
            $table->string("fundName");
            $table->string("companyName");
            $table->enum('type', ['FIAGRO', 'FII']);
            $table->string('segment')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fi_assets');
    }
};
