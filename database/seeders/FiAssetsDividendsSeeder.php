<?php

namespace Database\Seeders;

use App\Models\FiAsset;
use App\Models\FiAssetsDividends;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Smalot\PdfParser\Parser;

class FiAssetsDividendsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @throws GuzzleException
     */
    public function run(): void
    {

        $urlBase = env('URI_CVM', '');

        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0',
                'Accept-Language' => 'pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3',
                'Connection' => 'keep-alive',
            ]
        ]);

        $fiis = FiAsset::with('information')
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
            $start_time = microtime(true);
            $page = 0;
            $limit = null;
            $draw = 1;
            $apiUrl = "$urlBase/fnet/publico/pesquisarGerenciadorDocumentosDados";
            echo $fii->acronym . '11...........';

            $arrDocuments = [];
            $arrDocuments[$fii->acronym] = [];

            $this->getArrDocuments($limit, $page, $draw, $fii, $apiUrl, $client, $arrDocuments, $urlBase);
            $end_time = microtime(true);
            echo " OK ({$index}/{$total}) \n";
            $execution_time = $end_time - $start_time;
            echo "Tempo de execução: " . $execution_time . " segundos" . PHP_EOL;
            $index++;
            sleep(1);
        }
    }

    /**
     * @param float|null $limit
     * @param int $page
     * @param int $draw
     * @param mixed $fii
     * @param string $apiUrl
     * @param Client $client
     * @param array $arrDocuments
     * @param mixed $urlBase
     * @return array|void
     * @throws GuzzleException
     */
    public function getArrDocuments(?float $limit, int $page, int $draw, mixed $fii, string $apiUrl, Client $client, array $arrDocuments, mixed $urlBase)
    {
        while ($limit === null || $page < $limit) {
            $params = [
                'd' => $draw,
                's' => $page * 100, // PAGINA (AUMENTA DE 100 EM 100) - 0, 100, 200, 300, 400, 500, 600
                'l' => 100, // POR PAGINA
                'o' => [
                    ['dataEntrega' => 'desc']
                ],
                'cnpjFundo' => $fii->cnpj,
                'idCategoriaDocumento' => 14,
                'idTipoDocumento' => 41,
                'idEspecieDocumento' => 0,
                '_' => 1706408552268,
            ];
            $queryString = http_build_query($params);
            $fullUrl = $apiUrl . '?' . $queryString;

            $response = $client->request('GET', $fullUrl);

            $draw++;
            $data = json_decode($response->getBody(), true);

            if (isset($data)) {
                $page++;

                if (is_null($limit)) {
                    $limit = ceil($data['recordsTotal'] / 100);
                }

                $this->processDocuments($data['data'], $arrDocuments, $fii, $urlBase, $client);
            }
        }
    }

    /**
     * @param $listDocuments
     * @param array $arrDocuments
     * @param mixed $fii
     * @param mixed $urlBase
     * @param Client $client
     * @return array|void
     * @throws GuzzleException
     */
    public function processDocuments($listDocuments, array $arrDocuments, mixed $fii, mixed $urlBase, Client $client)
    {

        foreach ($listDocuments as $i => $dado) {

            if (substr_count($dado['dataReferencia'], '/') === 1) {
                $dado['dataReferencia'] = '01/' . $dado['dataReferencia'];
            }

            if (substr_count($dado['dataReferencia'], ':') === 1) {
                $dt = explode(' ', $dado['dataReferencia']);
                $dado['dataReferencia'] = $dt[0];
            }


            $dtReference = Carbon::createFromFormat('d/m/Y', (string)$dado['dataReferencia']);

            $formattedDate = $dtReference->format('Y-m-d');
            $checkExist = FiAssetsDividends::where(['reference_date' => $formattedDate])->exists();

            if (!$checkExist && $dado['situacaoDocumento'] === 'A') {

                sleep(1);
                $arrDocuments[$fii->acronym][] = $dado['id'];
                $urlDoc = "$urlBase/fnet/publico/downloadDocumento?id={$dado['id']}";

                $response2 = $client->request('GET', $urlDoc);

                $base64Content = $response2->getBody()->getContents();

                $xmlContent = base64_decode($base64Content);

                $xmlObject = simplexml_load_string($xmlContent);

                try {
                    $amortization = false;
                    $informeRendimento = $xmlObject->InformeRendimentos;

                    if (isset($informeRendimento->Rendimento->ValorProventoCota)) {
                        $valorProvento = (double)$informeRendimento->Rendimento->ValorProventoCota;
                        $baseDate = Carbon::createFromFormat('Y-m-d', (string)$informeRendimento->Rendimento->DataBase);
                        $paymentDate = Carbon::createFromFormat('Y-m-d', (string)$informeRendimento->Rendimento->DataPagamento);
                    } else if (isset($informeRendimento->Provento->Rendimento->ValorProvento)) {
                        $valorProvento = (double)$informeRendimento->Provento->Rendimento->ValorProvento;
                        $baseDate = Carbon::createFromFormat('Y-m-d', (string)$informeRendimento->Provento->Rendimento->DataBase);
                        $paymentDate = Carbon::createFromFormat('Y-m-d', (string)$informeRendimento->Provento->Rendimento->DataPagamento);
                    } else if (isset($informeRendimento->Amortizacao->ValorProventoCota)) {
                        $valorProvento = (double)$informeRendimento->Amortizacao->ValorProventoCota;
                        $baseDate = Carbon::createFromFormat('Y-m-d', (string)$informeRendimento->Amortizacao->DataBase);
                        $paymentDate = Carbon::createFromFormat('Y-m-d', (string)$informeRendimento->Amortizacao->DataPagamento);
                        $amortization = true;
                    } else if (isset($informeRendimento->Provento->Amortizacao->ValorProvento)) {
                        $valorProvento = (double)$informeRendimento->Provento->Amortizacao->ValorProvento;
                        $baseDate = Carbon::createFromFormat('Y-m-d', (string)$informeRendimento->Provento->Amortizacao->DataBase);
                        $paymentDate = Carbon::createFromFormat('Y-m-d', (string)$informeRendimento->Provento->Amortizacao->DataPagamento);
                        $amortization = true;
                    } else {
                        echo 'TIPO NAO RECONHECIDO' . PHP_EOL;
                        print_r($xmlObject);
                        exit;
                    }


                    FiAssetsDividends::firstOrCreate([
                        'fi_asset_id' => $fii->id,
                        'base_date' => $baseDate->format('Y-m-d'),
                        'payment_date' => $paymentDate->format('Y-m-d'),
                        'value' => $valorProvento,
                        'reference_date' => $formattedDate,
                        'amortization' => $amortization
                    ], [
                        'fi_asset_id' => $fii->id,
                        'base_date' => $baseDate,
                        'payment_date' => $paymentDate,
                        'value' => $valorProvento,
                        'reference_date' => $formattedDate,
                        'amortization' => $amortization
                    ]);
                } catch (\Exception $e) {
                    print_r($e);
                    print_r($xmlObject);
                    exit;
                }
            }
        }
    }
}





















































































