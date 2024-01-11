<?php

namespace Database\Seeders;

use App\Models\FiAsset;
use App\Models\FiAssetsDividends;
use GuzzleHttp\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FiAssetsDividendsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $urlBase = env('URI_SI', '');
        $apiUrl = "$urlBase/fii/companytickerprovents";

        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Host' => 'statusinvest.com.br',
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0',
                'Accept-Language' => 'pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3',
                'Connection' => 'keep-alive',
            ]
        ]);
        $fiis = FiAsset::all();
        $index = 1;
        $total = count($fiis);
        foreach ($fiis as $fii) {
            echo $fii->acronym . '11...........';
            $params = [
                'ticker' => $fii->acronym . '11',
                'chartProventsType' => 2,
            ];

            $response = $client->request('GET', $apiUrl, [
                'form_params' => $params
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data)) {
                foreach ($data['assetEarningsModels'] as $dividend) {
                    $baseDate = Carbon::createFromFormat('d/m/Y', $dividend['ed']);
                    $paymentDate = Carbon::createFromFormat('d/m/Y', $dividend['pd']);

                    FiAssetsDividends::create([
                        'fi_asset_id' => $fii->id,
                        'value' => (double)$dividend['sv'],
                        'base_date' => $baseDate,
                        'payment_date' => $paymentDate
                    ]);
                }
            }
            echo " OK ({$index}/{$total}) \n";
            $index++;
            sleep(1);
        }
    }
}
