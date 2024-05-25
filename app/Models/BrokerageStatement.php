<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class BrokerageStatement extends Model
{
    use HasFactory;


    protected $fillable = [
        'broker_id',
        'user_id',
        'note_number',
        'trade_date',
        'net_operations_value',
        'fees_and_taxes',
        'status',
    ];

    /**
     * Get the negotiations for the brokerage statement.
     */
    public function negotiations(): HasMany
    {
        return $this->hasMany(Negotiation::class);
    }
}
