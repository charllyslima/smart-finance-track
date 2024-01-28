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
        Schema::create('fi_assets_informations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fi_asset_id')->constrained('fi_assets');
            $table->string('acronym')->nullable();
            $table->string('trading_name')->nullable();
            $table->string('trading_code')->nullable();
            $table->string('trading_code_others')->nullable();
            $table->string('cnpj')->nullable();
            $table->text('classification')->nullable();
            $table->string('web_site')->nullable();
            $table->text('fund_address')->nullable();
            $table->string('fund_phone_number_ddd')->nullable();
            $table->string('fund_phone_number')->nullable();
            $table->string('fund_phone_number_fax')->nullable();
            $table->string('position_manager')->nullable();
            $table->string('manager_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone_number_ddd')->nullable();
            $table->string('company_phone_number')->nullable();
            $table->string('company_phone_number_fax')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_name')->nullable();
            $table->bigInteger('quota_count')->nullable();
            $table->date('quota_date_approved')->nullable();
            $table->string('type_fnet')->nullable();
            $table->json('codes')->nullable();
            $table->json('codes_other')->nullable();
            $table->string('segment')->nullable();
            $table->string('shareholder_name')->nullable();
            $table->text('shareholder_address')->nullable();
            $table->string('shareholder_phone_number_ddd')->nullable();
            $table->string('shareholder_phone_number')->nullable();
            $table->string('shareholder_fax_number')->nullable();
            $table->string('shareholder_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fi_assets_informations');
    }
};
