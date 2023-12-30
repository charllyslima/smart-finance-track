<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialTransactionCreateRequest;
use App\Http\Requests\FinancialTransactionFilterRequest;
use App\Models\FinancialTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class FinancialTransactionsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/financial-transactions",
     *     summary="Listar transações financeiras",
     *     description="Retorna a lista paginada de todas as transações financeiras do usuário autenticado, com opção de filtrar por tipo de transação e intervalo de datas (opcionais). Inclui a opção de paginar os resultados.",
     *     tags={"Transações Financeiras"},
     *     @OA\Parameter(
     *         name="transaction_type",
     *         in="query",
     *         description="Filtrar por tipo de transação (DEPOSIT, WITHDRAWAL, INVESTMENT, DIVIDENDS)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"DEPOSIT", "WITHDRAWAL", "INVESTMENT", "DIVIDENDS"},
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filtrar por data de início (formato: 'YYYY-MM-DD')",
     *         required=false,
     *         @OA\Schema(type="string", format="date"),
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filtrar por data de término (formato: 'YYYY-MM-DD', deve ser posterior ou igual à data de início)",
     *         required=false,
     *         @OA\Schema(type="string", format="date"),
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página para a paginação (opcional)",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         description="Quantidade de itens por página (opcional, padrão 10)",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informações das transações financeiras",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="financialTransactions", type="array", @OA\Items(ref="#/components/schemas/FinancialTransaction")),
     *             @OA\Property(property="totalDeposit", type="number", format="float", example=1000.00),
     *             @OA\Property(property="totalWithdraw", type="number", format="float", example=500.00),
     *             @OA\Property(property="totalBalance", type="number", format="float", example=500.00),
     *             @OA\Property(property="totalInvestment", type="number", format="float", example=1000.00),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *     ),
     *     security={{ "bearerAuth": {} }},
     * )
     */
    public function index(FinancialTransactionFilterRequest $request): JsonResponse
    {
        $user_id = Auth::id();

        $baseQuery = FinancialTransaction::where('user_id', $user_id);

        if ($request->has('transaction_type')) {
            $transactionType = $request->input('transaction_type');
            $baseQuery->where('transaction_type', $transactionType);
        }

        if ($request->has('start_date')) {
            $startDate = $request->input('start_date');
            $baseQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($request->has('end_date')) {
            $endDate = $request->input('end_date');
            $baseQuery->whereDate('created_at', '<=', $endDate);
        }

        $pageSize = $request->input('page_size', 10);
        $financialTransactions = $baseQuery->paginate($pageSize);

        $totalDeposit = FinancialTransaction::where('user_id', $user_id)
            ->where('transaction_type', FinancialTransaction::DEPOSIT)
            ->sum('amount');
        $totalWithdraw = FinancialTransaction::where('user_id', $user_id)
            ->where('transaction_type', FinancialTransaction::WITHDRAWAL)
            ->sum('amount');
        $totalInvestment = FinancialTransaction::where('user_id', $user_id)
            ->where('transaction_type', FinancialTransaction::INVESTMENT)
            ->sum('amount');

        $totalBalance = $totalDeposit - $totalWithdraw;

        $responseData = [
            'financialTransactions' => $financialTransactions,
            'totalDeposit' => (float)$totalDeposit,
            'totalWithdraw' => (float)$totalWithdraw,
            'totalBalance' => (float)$totalBalance,
            'totalInvestment' => (float)$totalInvestment,
        ];

        return response()->json($responseData);
    }


    /**
     * @OA\Post(
     *     path="/api/financial-transactions",
     *     summary="Criar transação financeira",
     *     description="Cria uma nova transação financeira para o usuário autenticado.",
     *     tags={"Transações Financeiras"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/FinancialTransactionCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transação financeira criada com sucesso",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao criar transação financeira",
     *     ),
     *     security={{ "bearerAuth": {} }},
     * )
     */
    public function store(FinancialTransactionCreateRequest $request): JsonResponse
    {
        try {
            FinancialTransaction::create($request->validated());
            return response()->json(['message' => 'Transação financeira criada com sucesso'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao criar transação financeira'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/financial-transactions/{id}",
     *     summary="Atualizar transação financeira",
     *     description="Atualiza uma transação financeira existente do usuário autenticado.",
     *     tags={"Transações Financeiras"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da transação financeira a ser atualizada",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/FinancialTransactionCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transação financeira atualizada com sucesso",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Você não tem permissão para acessar este registro",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao atualizar transação financeira",
     *     ),
     *     security={{ "bearerAuth": {} }},
     * )
     */
    public function update(FinancialTransactionCreateRequest $request, int $id): JsonResponse
    {
        try {
            $transactionHistory = FinancialTransaction::findOrFail($id);
            if ($transactionHistory->user_id != Auth::id()) {
                return response()->json(['message' => 'Você não tem permissão para acessar este registro'], 403);
            }

            $transactionHistory->update($request->validated());
            return response()->json(['message' => 'Transação financeira atualizada com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar transação financeira'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/financial-transactions/{id}",
     *     summary="Excluir transação financeira",
     *     description="Exclui uma transação financeira existente do usuário autenticado.",
     *     tags={"Transações Financeiras"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da transação financeira a ser excluída",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transação financeira excluída com sucesso",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Você não tem permissão para acessar este registro",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao excluir transação financeira",
     *     ),
     *     security={{ "bearerAuth": {} }},
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $transactionHistory = FinancialTransaction::findOrFail($id);
            if ($transactionHistory->user_id != Auth::id()) {
                return response()->json(['message' => 'Você não tem permissão para acessar este registro'], 403);
            }

            $transactionHistory->delete();
            return response()->json(['message' => 'Transação financeira excluída com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao excluir transação financeira'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/financial-transactions/{id}",
     *     summary="Obter detalhes da transação financeira",
     *     description="Retorna detalhes de uma transação financeira específica do usuário autenticado.",
     *     tags={"Transações Financeiras"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da transação financeira a ser obtida",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da transação financeira",
     *         @OA\JsonContent(ref="#/components/schemas/FinancialTransaction")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registro não encontrado",
     *     ),
     *     security={{ "bearerAuth": {} }},
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $transactionHistory = FinancialTransaction::where('user_id', Auth::id())
                ->findOrFail($id);

            return response()->json(['financialTransaction' => $transactionHistory]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }
    }

}
