<?php

namespace App\Http\Controllers;

use App\Models\FiAssetsEvent;
use App\Models\Operation;
use App\Models\Wallet;
use App\Models\WalletItem;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class WalletController extends Controller
{


    /**
     * @OA\Get(
     *     path="/api/wallet/consolidator",
     *     summary="Calcula o valor médio de compra de fundos imobiliários",
     *     description="Retorna o valor médio de compra de cada fundo imobiliário na carteira do usuário, considerando todas as compras até a data limite especificada.",
     *     operationId="walletConsolidator",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dados da carteira retornados com sucesso",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="acronym", type="string", example="XYZ1"),
     *                 @OA\Property(property="averagePurchasePrice", type="number", format="double", example=123.45)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos fornecidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data inválida fornecida")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         )
     *     )
     * )
     */
    public function walletConsolidator(): void
    {
        $userId = Auth::id();

        $startDate = $this->getFirstOperationDate();
        $endDate = new DateTime('now');

        for ($date = $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $formattedDate = $date->format('Y-m-d');
            $operations = $this->fetchUserOperations($userId, '2024-01-18');

            if ($operations->count() > 0) {
                $dataWallet = $this->processOperations($operations);
                if ($dataWallet) {
                    echo "Fundo | Valor médio | quantidade" . PHP_EOL;
                    foreach ($dataWallet as $acr => $fund) {
                        if($fund['realQuantity'] > 0) {
                            echo $acr . "11 | " . $fund['averagePurchasePrice'] . " | " . $fund['realQuantity'] . PHP_EOL;
                        }
                    }
                    exit;
                    print_r($dataWallet);
                    exit;
                    $this->displayResults($dataWallet, $formattedDate, $userId);
                }
            }

        }


    }

    /**
     * @throws \Exception
     */
    private function getFirstOperationDate(): DateTime
    {
        $firstOperation = Operation::orderBy('created_at', 'asc')->first();
        return $firstOperation ? new DateTime($firstOperation->created_at) : new DateTime('now');
    }

    private function fetchUserOperations($userId, $date): Collection|array
    {

//        DB::listen(function ($query) {
//            // Imprime a consulta SQL
//            echo $query->sql . PHP_EOL;
//
//        });

        return Operation::with('fiAsset')
            ->join('fi_assets', 'fi_assets.id', '=', 'operations.fi_asset_id')
            ->where('operations.user_id', $userId)
            ->where('operations.created_at', '<=', $date)
//            ->where('fi_assets.acronym', '=', 'CPTS')
            ->select(
                'operations.id',
                'operations.fi_asset_id',
                'operations.created_at',
                'operations.total',
                'operations.quantity',
                'operations.type as operation_type',
                'fi_assets.acronym'
            )
            ->orderBy('fi_assets.acronym')
            ->orderBy('operations.created_at')
            ->get();
    }


    private function processOperations($operations): array
    {
        $dataWallet = [];
        $stockSplitByFund = [];

        foreach ($operations as $operation) {
            $fiAssetAcronym = $operation->fiAsset->acronym;
            $fiAssetId = $operation->fi_asset_id;

            if (!isset($dataWallet[$fiAssetAcronym])) {
                $dataWallet[$fiAssetAcronym] = $this->initializeFundData();
                $stockSplitByFund[$fiAssetId] = $this->loadFundSplits($fiAssetId);
            }

            $dataWallet[$fiAssetAcronym]['idFund'] = $fiAssetId;
            $this->applyStockSplits($dataWallet[$fiAssetAcronym], $stockSplitByFund[$fiAssetId], $operation);
            $this->updateFundData($dataWallet[$fiAssetAcronym], $operation);

        }

        return $dataWallet;
    }

    private function initializeFundData(): array
    {
        return [
            'idFund' => 0,
            'totalSpent' => 0,
            'totalQuantity' => 0,
            'averagePurchasePrice' => 0,
            'realQuantity' => 0,
            'splits' => [],
        ];
    }

    private function loadFundSplits($fiAssetId)
    {
        return FiAssetsEvent::where('fi_asset_id', $fiAssetId)
            ->where('type', 'DESDOBRAMENTO')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function applyStockSplits(&$fundData, $stockSplits, $operation): void
    {

        foreach ($stockSplits as $split) {
            if ($split->created_at <= $operation->created_at && !in_array($split, $fundData['splits'], true)) {
                $fundData['splits'][] = $split;
                $fundData['totalQuantity'] *= $split->multiplier;
                $fundData['realQuantity'] *= $split->multiplier;
            }
        }
    }

    private function updateFundData(&$fundData, $operation): void
    {

        if ($operation->operation_type === 'purchase') {
            $fundData['totalSpent'] += $operation->total;
            $fundData['totalQuantity'] += (int)$operation->quantity;
            $fundData['realQuantity'] += (int)$operation->quantity;
        } else {
            $fundData['realQuantity'] -= $operation->quantity;
        }

        if ($fundData['totalQuantity'] > 0) {
            $fundData['averagePurchasePrice'] = $fundData['totalSpent'] / $fundData['totalQuantity'];
        }
    }

    private function displayResults($dataWallet, $dataC, $userId): void
    {
        $wallet = Wallet::firstOrCreate([
            'user_id' => $userId,
            'created_at' => $dataC,
        ], [
            'user_id' => $userId,
            'created_at' => $dataC,
            'updated_at' => $dataC
        ]);

        if ($wallet->wasRecentlyCreated) {
            foreach ($dataWallet as $acronym => $data) {
                if ((int)$data['realQuantity'] > 0) {
                    try {
                        WalletItem::create([
                            'wallet_id' => $wallet->id,
                            'fi_asset_id' => $data['idFund'],
                            'average_value' => $data['averagePurchasePrice'],
                            'quantity' => $data['realQuantity'],
                        ]);
                    } catch (\Exception $e) {
                        $data['created'] = $dataC;
                        print_r($data);
                        exit;
                    }
                    // echo $acronym . ' | ' . number_format($data['averagePurchasePrice'], 3, ',', '') . ' | ' . $data['realQuantity']
                }
            }
        }
    }
}
