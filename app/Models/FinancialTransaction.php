<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="FinancialTransaction",
 *     description="Modelo de transação financeira",
 * )
 */
class FinancialTransaction extends Model
{

    /**
     * @OA\Property(
     *     property="id",
     *     description="ID da transação financeira",
     *     type="integer",
     *     example=1
     * )
     * @OA\Property(
     *      property="transaction_type",
     *      description="Tipo de transação (DEPOSIT, WITHDRAWAL, INVESTMENT, DIVIDENDS)",
     *      type="string",
     *      example="DEPOSIT"
     *  )
     * @OA\Property(
     *      property="transaction_date",
     *      description="Data da transação no formato 'YYYY-MM-DD'",
     *      type="string",
     *      example="1956-01-01"
     *  )
     * @OA\Property(
     *      property="amount",
     *      description="Valor da transação",
     *      type="string",
     *      example="100.00"
     *  )
     */

    use HasFactory;

    const WITHDRAWAL = 'WITHDRAWAL';
    const DEPOSIT = 'DEPOSIT';
    const INVESTMENT = 'INVESTMENT';


    protected $fillable = ['user_id', 'transaction_type', 'amount', 'transaction_date'];

    protected $hidden = ['user_id'];

    protected $attributes = [
        'transaction_date' => 'Data da Operacao',
        'amount' => 'Valor',
        'transaction_type' => 'Tipo de Operação',
    ];
}
