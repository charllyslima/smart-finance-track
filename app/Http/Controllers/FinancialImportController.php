<?php

namespace App\Http\Controllers;

use App\Models\FiAsset;
use App\Models\FiAssetsEvent;
use App\Models\Operation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FinancialImportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/import-personal",
     *     summary="Importar histórico financeiro da B3 a partir de um arquivo .xlsx",
     *     description="Importa um histórico financeiro da B3 a partir de um arquivo .xlsx, processa cada linha do arquivo e realiza as ações necessárias com os dados.",
     *     operationId="importHistoryB3",
     *     tags={"Financeiro"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Arquivo .xlsx contendo o histórico financeiro da B3",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     description="Arquivo .xlsx",
     *                     type="file",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Arquivo importado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Arquivo importado com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requisição inválida",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="O arquivo deve ser um .xlsx")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao importar arquivo",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao importar arquivo")
     *         )
     *     )
     * )
     */

    public function importHistoryB3(Request $request): JsonResponse
    {
        $validator = Validator::make(
            [
                'file' => $request->file,
                'extension' => strtolower($request->file->getClientOriginalExtension()),
            ],
            [
                'file' => 'required',
                'extension' => 'required|in:xlsx',
            ]
        );

        $file = $request->file('file');

//        try {
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        array_shift($rows);

        foreach ($rows as $row) {

            if ($row[2] === 'Transferência - Liquidação' || $row[2] === 'Atualização') {

                $fund = $this->extractCodeParts($row[3]);

                if ($fund['type'] === 11) {
                    $quantity = (int)$row[5];
                    $fiAssets = FiAsset::where('acronym', $fund['fund'])->first();
                    if ($row[6] === ' - ') {

                        $dataFornecida = Carbon::createFromFormat('d/m/Y', $row[1])->toDateString();

                        $sub = FiAssetsEvent::where('fi_asset_id', $fiAssets->id)
                            ->where('type', 'SUBINSCRIÇÃO')
                            ->where('created_at', '<', $dataFornecida)
                            ->orderBy('created_at', 'desc')
                            ->get();

                        Operation::create([
                            'user_id' => Auth::id(),
                            'fi_asset_id' => $fiAssets->id,
                            'quantity' => $quantity,
                            'price' => $sub->price,
                            'total' => $sub->price * $quantity,
                            'type' => 'purchase',
                            'created_at' => Carbon::createFromFormat('d/m/Y', $row[1])->toDateString()
                        ]);
                    } else {

                        $unitPrice = (double)str_replace(' R$', '', str_replace(',', '.', $row[6]));
                        $total = (double)str_replace(' R$', '', str_replace(',', '.', $row[7]));

                        if ($row[0] === 'Debito') {
                            Operation::create([
                                'user_id' => Auth::id(),
                                'fi_asset_id' => $fiAssets->id,
                                'quantity' => $quantity,
                                'price' => $unitPrice,
                                'total' => $total,
                                'type' => 'sale',
                                'created_at' => Carbon::createFromFormat('d/m/Y', $row[1])->toDateString()
                            ]);
                        } else {
                            Operation::create([
                                'user_id' => Auth::id(),
                                'fi_asset_id' => $fiAssets->id,
                                'quantity' => $quantity,
                                'price' => $unitPrice,
                                'total' => $total,
                                'type' => 'purchase',
                                'created_at' => Carbon::createFromFormat('d/m/Y', $row[1])->toDateString()
                            ]);
                        }
                    }

                }
            }
        }

        return response()->json(['message' => 'Dados importados com sucesso!']);
//        } catch (\Exception $e) {
//            return response()->json(['message' => 'Erro ao importar arquivo'], 500);
//        }
    }

    private function extractCodeParts($fullString)
    {
        if (preg_match("/([a-zA-Z]+11)/", $fullString, $matches)) {
            $code = $matches[1];

            // Em seguida, separar as letras de "11"
            if (preg_match("/([a-zA-Z]+)(11)/", $code, $matches)) {
                return [
                    'fund' => $matches[1],
                    'type' => (int)$matches[2]
                ];
            }
        }

        return null;
    }
}
