<?php

namespace App\Http\Controllers;

use App\Models\FiAsset;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class SyncController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/sync-price",
     *     summary="Sincroniza o preço dos ativos financeiros",
     *     description="Sincroniza o preço dos ativos financeiros (FIIs) com uma fonte externa, utilizando uma chave de aplicação para autenticação. Retorna o preço atualizado de cada ativo financeiro.",
     *     operationId="syncPrice",
     *     tags={"Financeiro"},
     *     @OA\Parameter(
     *         name="appKey",
     *         in="path",
     *         required=true,
     *         description="Chave de aplicação para autenticação do serviço.",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preços atualizados com sucesso",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="ticker",
     *                     type="string",
     *                     description="O acrônimo do ativo financeiro."
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="string",
     *                     description="O preço atualizado do ativo financeiro."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Key de acesso incorreta!"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Erro interno do servidor"
     *             )
     *         )
     *     )
     * )
     */
    public function syncPrice(Request $request)
    {

        $urlBase = env('URI_GF', '');
        $fiis = FiAsset::all();
        $startTime = microtime(true);
        foreach ($fiis as $index => $fii) {

            $url = "{$urlBase}/quote/{$fii->acronym}11:BVMF";
            $html = file_get_contents($url);
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($html);
            libxml_clear_errors();
            $xpath = new DOMXPath($doc);
            $finStreamers = $xpath->query("//div[contains(@class, 'AHmHk')]");

            if ($finStreamers->count() > 0) {
                $price = str_replace('R$', '', $finStreamers->item(0)->textContent);
                echo $index . '/' . count($fiis) . PHP_EOL;
            } else {
                echo 'fundo inativo ' . $fii->acronym . PHP_EOL;
            }

            sleep(1);

        }
        $endTime = microtime(true); // Tempo no final da iteração
        $elapsedTime = $endTime - $startTime; // Tempo total da iteração
        echo "Tempo da iteração: " . $elapsedTime . " segundos" . PHP_EOL;
    }
}
