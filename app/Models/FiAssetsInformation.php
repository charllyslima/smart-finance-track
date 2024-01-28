<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiAssetsInformation extends Model
{
    use HasFactory;

    protected $table = 'fi_assets_informations';

    protected $fillable = [
        'acronym',
        'trading_name',
        'trading_code',
        'trading_code_others',
        'cnpj',
        'classification',
        'web_site',
        'fund_address',
        'fund_phone_number_ddd',
        'fund_phone_number',
        'fund_phone_number_fax',
        'position_manager',
        'manager_name',
        'company_address',
        'company_phone_number_ddd',
        'company_phone_number',
        'company_phone_number_fax',
        'company_email',
        'company_name',
        'quota_count',
        'quota_date_approved',
        'type_fnet',
        'codes',
        'codes_other',
        'segment',
        'shareholder_name',
        'shareholder_address',
        'shareholder_phone_number_ddd',
        'shareholder_phone_number',
        'shareholder_fax_number',
        'shareholder_email'
    ];
}
