<?php

namespace Database\Seeders;

use App\Models\FiAsset;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Seeder;
use GuzzleHttp\Client;
use JsonException;

class FiAssetsSeeder extends Seeder
{
    const FII = 7;
    const FIAGRO = 34;

    /**
     * Run the database seeds.
     * @throws GuzzleException
     */
    public function run(): void
    {
        $this->seedTrusts(self::FII);
        $this->seedTrusts(self::FIAGRO);
    }

    /**
     * Seed trusts based on fund type.
     *
     * @param int $fundType
     * @throws GuzzleException
     * @throws JsonException
     */
    private function seedTrusts(int $fundType): void
    {

        $urlBase = env('URI_B3', '');

        $params = [
            'typeFund' => $fundType,
            'pageNumber' => 1,
            'pageSize' => 500,
        ];

        $encodeData = base64_encode(json_encode($params, JSON_THROW_ON_ERROR));

        $apiUrl = "{$urlBase}/fundsProxy/fundsCall/GetListedFundsSIG/$encodeData";

        // Use o Guzzle para fazer a requisição HTTP
        $client = new Client();
        $response = $client->get($apiUrl);

        // Obtenha os dados da resposta como array
        $data = json_decode($response->getBody(), true);

        // Itera sobre os resultados
        foreach ($data['results'] as $item) {
            // Verifica se o registro já existe na tabela
            $existingTrust = FiAsset::where('acronym', $item['acronym'])->first();

            // Se não existir, cria um novo registro
            if (!$existingTrust) {

                FiAsset::create([
                    'acronym' => $item['acronym'],
                ],
                    [
                        'acronym' => $item['acronym'],
                        'fundName' => $item['fundName'],
                        'companyName' => $item['companyName'],
                        'fundCategory' => $fundType === self::FII ? 'FII' : 'FIAGRO',
                    ]);
            }
        }
    }
}
