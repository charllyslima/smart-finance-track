<?php

namespace Database\Seeders;

use App\Models\FiAssetsValue;
use GuzzleHttp\Client;
use Illuminate\Database\Seeder;
use App\Models\FiAsset;
use Illuminate\Support\Carbon;

class FiAssetsValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $urlBase = env('URI_SI', '');
        $apiUrl = "$urlBase/fii/tickerprice";

        $fiis = FiAsset::all();
        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Host' => 'statusinvest.com.br',
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0',
                'Accept-Language' => 'pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3',
                'Connection' => 'keep-alive',
            ]
        ]);
        $index = 1;
        $total = count($fiis);
        foreach ($fiis as $fii) {
            echo $fii->acronym . '11...........';
            $params = [
                'ticker' => $fii->acronym . '11',
                'type' => 4,
                'currences[]' => 1,
            ];
            $response = $client->request('POST', $apiUrl, [
                'form_params' => $params
            ]);

            $data = json_decode($response->getBody(), true);
            if (isset($data[0])) {
                foreach ($data[0]['prices'] as $price) {
                    FiAssetsValue::create([
                        'fi_asset_id' => $fii->id,
                        'value' => $price['price'],
                        'created_at' => Carbon::createFromFormat('d/m/y H:i', $price['date']),
                    ]);
                }
            }
            echo " OK ({$index}/{$total}) \n";
            $index++;
            sleep(1);

        }


    }
}
