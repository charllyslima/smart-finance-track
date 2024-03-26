<?php

namespace Database\Seeders;

use App\Models\FiAsset;
use App\Models\FiAssetsInformation;
use GuzzleHttp\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FiAssetsInformationsSeeder extends Seeder
{

    const FII = 7;
    const FIAGRO = 34;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $urlBase = env('URI_B3', '');

        $fiis = FiAsset::all();
        $index = 1;
        $total = count($fiis);
        $client = new Client();
        foreach ($fiis as $fii) {
            echo $fii->acronym . '11...........';
            $params = [
                'typeFund' => $fii->type === 'FIAGRO' ? self::FIAGRO : self::FII,
                'identifierFund' => $fii->acronym,
            ];

            $encodeData = base64_encode(json_encode($params, JSON_THROW_ON_ERROR));

            $apiUrl = "{$urlBase}/fundsProxy/fundsCall/GetDetailFundSIG/$encodeData";

            $response = $client->get($apiUrl);

            $data = json_decode($response->getBody(), true);

            if (isset($data['detailFund'])) {
                $detail = $data['detailFund'];
                $shareholder = $data['shareHolder'] ?? [];

                // Update or create the FiAsset record
                FiAssetsInformation::updateOrCreate(
                    [
                        'fi_asset_id' => $fii->id,
                        'trading_name' => $detail['tradingName'],
                        'trading_code' => $detail['tradingCode'],
                        'trading_code_others' => $detail['tradingCodeOthers'],
                        'cnpj' => $detail['cnpj'],
                        'classification' => $detail['classification'],
                        'web_site' => $detail['webSite'],
                        'fund_address' => $detail['fundAddress'],
                        'fund_phone_number_ddd' => $detail['fundPhoneNumberDDD'],
                        'fund_phone_number' => $detail['fundPhoneNumber'],
                        'fund_phone_number_fax' => $detail['fundPhoneNumberFax'],
                        'position_manager' => $detail['positionManager'],
                        'manager_name' => $detail['managerName'],
                        'company_address' => $detail['companyAddress'],
                        'company_phone_number_ddd' => $detail['companyPhoneNumberDDD'],
                        'company_phone_number' => $detail['companyPhoneNumber'],
                        'company_phone_number_fax' => $detail['companyPhoneNumberFax'],
                        'company_email' => $detail['companyEmail'],
                        'company_name' => $detail['companyName'],
                        'quota_count' => $detail['quotaCount'],
                        'quota_date_approved' => Carbon::createFromFormat('d/m/Y', $detail['quotaDateApproved'])->format('Y-m-d'),
                        'type_fnet' => $detail['typeFNET'],
                        'codes' => json_encode($detail['codes']),
                        'codes_other' => $detail['codesOther'],
                        'segment' => $detail['segment'],
                        'shareholder_name' => $shareholder['shareHolderName'],
                        'shareholder_address' => $shareholder['shareHolderAddress'],
                        'shareholder_phone_number_ddd' => $shareholder['shareHolderPhoneNumberDDD'],
                        'shareholder_phone_number' => $shareholder['shareHolderPhoneNumber'],
                        'shareholder_fax_number' => $shareholder['shareHolderFaxNumber'],
                        'shareholder_email' => $shareholder['shareHolderEmail'],
                    ],
                    [
                        'fi_asset_id' => $fii->id,
                        'trading_name' => $detail['tradingName'],
                        'trading_code' => $detail['tradingCode'],
                        'trading_code_others' => $detail['tradingCodeOthers'],
                        'cnpj' => $detail['cnpj'],
                        'classification' => $detail['classification'],
                        'web_site' => $detail['webSite'],
                        'fund_address' => $detail['fundAddress'],
                        'fund_phone_number_ddd' => $detail['fundPhoneNumberDDD'],
                        'fund_phone_number' => $detail['fundPhoneNumber'],
                        'fund_phone_number_fax' => $detail['fundPhoneNumberFax'],
                        'position_manager' => $detail['positionManager'],
                        'manager_name' => $detail['managerName'],
                        'company_address' => $detail['companyAddress'],
                        'company_phone_number_ddd' => $detail['companyPhoneNumberDDD'],
                        'company_phone_number' => $detail['companyPhoneNumber'],
                        'company_phone_number_fax' => $detail['companyPhoneNumberFax'],
                        'company_email' => $detail['companyEmail'],
                        'company_name' => $detail['companyName'],
                        'quota_count' => $detail['quotaCount'],
                        'quota_date_approved' => Carbon::createFromFormat('d/m/Y', $detail['quotaDateApproved'])->format('Y-m-d'),
                        'type_fnet' => $detail['typeFNET'],
                        'codes' => json_encode($detail['codes']),
                        'codes_other' => $detail['codesOther'],
                        'segment' => $detail['segment'],
                        'shareholder_name' => $shareholder['shareHolderName'],
                        'shareholder_address' => $shareholder['shareHolderAddress'],
                        'shareholder_phone_number_ddd' => $shareholder['shareHolderPhoneNumberDDD'],
                        'shareholder_phone_number' => $shareholder['shareHolderPhoneNumber'],
                        'shareholder_fax_number' => $shareholder['shareHolderFaxNumber'],
                        'shareholder_email' => $shareholder['shareHolderEmail'],
                    ]
                );

            }

            echo " OK ({$index}/{$total}) \n";
            $index++;
            sleep(1);
        }
    }
}
