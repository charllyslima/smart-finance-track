<?php

namespace App\Http\Requests;

use App\Models\FinancialTransaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="FinancialTransactionCreateRequest",
 *     description="Modelo de requisição para criar uma transação financeira",
 * )
 */
class FinancialTransactionCreateRequest extends FormRequest
{

    /**
     * @OA\Property(
     *     property="user_id",
     *     description="ID do usuário",
     *     type="integer",
     *     example=1
     * )
     */
    public $user_id;

    /**
     * @OA\Property(
     *     property="transaction_type",
     *     description="Tipo de transação",
     *     type="string",
     *     enum={"DEPOSIT", "WITHDRAWAL", "INVESTMENT", "DIVIDENDS"},
     *     example="DEPOSIT"
     * )
     */
    public $transaction_type;

    /**
     * @OA\Property(
     *     property="amount",
     *     description="Valor da transação",
     *     type="number",
     *     example=100.00
     * )
     */
    public $amount;

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData(): array
    {
        $this->merge(['user_id' => Auth::id()]);

        return $this->all();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $user_id = Auth::id();

        return [
            'user_id' => 'required|exists:users,id|in:' . $user_id,
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => "required|in:" . FinancialTransaction::DEPOSIT . "," . FinancialTransaction::WITHDRAWAL,
            'transaction_date' => 'required|date'
        ];
    }
}
