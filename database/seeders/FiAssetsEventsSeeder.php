<?php

namespace Database\Seeders;

use App\Models\FiAsset;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Seeder;
use Mockery\Exception;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Pdf;

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
                'idCategoriaDocumento' => 1,
                'idTipoDocumento' => 0,
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
     * @throws PdfNotFound
     * @throws GuzzleException
     */
    public function processDocuments($listDocuments, array $arrDocuments, mixed $fii, mixed $urlBase, Client $client)
    {


        foreach ($listDocuments as $i => $dado) {

            $urlDoc = "$urlBase/fnet/publico/downloadDocumento?id={$dado['id']}";

            $response = $client->request('GET', $urlDoc);
            $pdfContent = base64_decode($response->getBody()->getContents());
            if (empty($pdfContent)) {
                continue;
            }
            $tmpfname = tempnam('.', 'PDF');
            $pdfFileName = $tmpfname . '.pdf';
            file_put_contents($pdfFileName, $pdfContent);
            try {
                $text = (new Pdf('C:\poppler\Library\bin\pdftotext.exe'))
                    ->setPdf($pdfFileName)
                    ->text();

                $searchString = "desdobramento das Cotas";
                $found = false;

                $paragraphs = preg_split('/\n\s*\n/', $text);
                $numbersArray = [];
                foreach ($paragraphs as $paragraph) {
                    $position = strpos($paragraph, $searchString);
                    if ($position !== false) {
                        // Regex para encontrar a razão do desdobramento
                        $regexRazao = '/razão de (\d+:\d+)/';
                        // Regex para encontrar a data
                        $regexData = '/de (\d+ de [a-z]+ de \d{4})/i';

                        // Procurando pela razão do desdobramento
                        if (preg_match($regexRazao, $paragraph, $matchesRazao)) {
                            $razaoDesdobramento = $matchesRazao[1];
                            echo PHP_EOL . "Razão do Desdobramento: " . $razaoDesdobramento . PHP_EOL . PHP_EOL;
                        } else {
                            echo "Razão do Desdobramento não encontrada.\n";
                            print_r($paragraph);
                        }

                        // Procurando pela data
                        if (preg_match($regexData, $paragraph, $matchesData)) {
                            $dataDesdobramento = $matchesData[1];
                            echo "Data do Desdobramento: " . $dataDesdobramento . PHP_EOL . PHP_EOL;
                        } else {
                            echo "Data do Desdobramento não encontrada.\n";
                        }
                        $found = true;
                        break;
                    }
                }

                if ($found) {
//                    print_r($paragraph);
                    echo PHP_EOL;
                    // Exibe os números encontrados
                    foreach ($numbersArray as $number) {
                        echo $number . PHP_EOL;
                    }
                }

                unlink($pdfFileName);
                unlink($tmpfname);
            } catch (Exception $e) {
                print_r($e);
            }
        }
    }

}
