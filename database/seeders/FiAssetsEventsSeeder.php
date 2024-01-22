<?php

namespace Database\Seeders;

use App\Models\FiAsset;
use App\Models\FiAssetsEvent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FiAssetsEventsSeeder extends Seeder
{
    const FII = 7;
    const FIAGRO = 34;

    /**
     * Run the database seeds.
     * @throws GuzzleException
     */
    public function run(): void
    {
        $urlBase = env('URI_B3', '');

        $fiis = FiAsset::all();
        $index = 1;
        $total = count($fiis);
        foreach ($fiis as $fii) {
            echo $fii->acronym . '11...........';
            $params = [
                'identifierFund' => $fii->acronym . '11',
                'typeFund' => $fii->type === 'FIAGRO' ? self::FIAGRO : self::FII,
                'cnpj' => 0,
            ];

            $encodeData = base64_encode(json_encode($params));

            $apiUrl = "{$urlBase}/fundsProxy/fundsCall/GetListedSupplementFunds/$encodeData";

            $client = new Client();
            $response = $client->get($apiUrl);

            $data = json_decode($response->getBody(), true);

            foreach ($data['stockDividends'] ?? [] as $stockDividend) {
                if ($stockDividend['label'] === 'DESDOBRAMENTO') {
                    $number = (double)str_replace(',', '.', $stockDividend['factor']);
                    $multiplicador = ($number / 100) + 1;
                    try {
                        FiAssetsEvent::create([
                            'fi_asset_id' => $fii->id,
                            'type' => 'DESDOBRAMENTO',
                            'multiplier' => $multiplicador,
                            'created_at' => Carbon::createFromFormat('d/m/Y', $stockDividend['lastDatePrior'])
                        ]);
                    }catch (\Exception $e){

                    }
                }
            }

            echo " OK ({$index}/{$total}) \n";
            $index++;
            sleep(1);

        }
    }


}
