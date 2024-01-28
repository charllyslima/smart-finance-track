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
        $urlBase = env('URI_CVM', '');

        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Host' => 'statusinvest.com.br',
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0',
                'Accept-Language' => 'pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3',
                'Connection' => 'keep-alive',
            ]
        ]);

        $fiis = FFiAsset::with('information')
            ->get()
            ->map(function ($asset) {
                $result = new \stdClass;
                $result->id = $asset->id;
                $result->cnpj = $asset->information->cnpj ?? null;
                $result->acronym = $asset->acronym ?? null;
                return $result;
            });

        $index = 1;
        $total = count($fiis);
        foreach ($fiis as $fii) {
            $apiUrl = "$urlBase/fnet/publico/abrirGerenciadorDocumentosCVM";
            echo $fii->acronym . '11...........';

            $params = [
                'd' => 4,
                's' => 0, // PAGINA (AUMENTA DE 100 EM 100) - 0, 100, 200, 300, 400, 500, 600
                'l' => 100, // POR PAGINA
                'o' => [
                    ['dataEntrega' => 'desc']
                ],
                'cnpjFundo' => $fii->cnpj,
                'idCategoriaDocumento' => 0,
                'idTipoDocumento' => 0,
                'idEspecieDocumento' => 0,
                '_' => 1706408552268,
            ];

            $response = $client->request('GET', $apiUrl, [
                'form_params' => $params
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data)) {
                foreach ($data['assetEarningsModels'] as $dividend) {
                    $baseDate = Carbon::createFromFormat('d/m/Y', $dividend['ed']);
                    $paymentDate = Carbon::createFromFormat('d/m/Y', $dividend['pd']);

                    FiAssetsDividends::firstOrCreate([
                        'fi_asset_id' => $fii->id,
                        'base_date' => $baseDate,
                        'payment_date' => $paymentDate,
                        'value' => (double)$dividend['sv']
                    ], [
                        'fi_asset_id' => $fii->id,
                        'base_date' => $baseDate,
                        'payment_date' => $paymentDate,
                        'value' => (double)$dividend['sv']
                    ]);
                }
            }
            echo " OK ({$index}/{$total}) \n";
            $index++;
            sleep(1);
        }
    }
}
