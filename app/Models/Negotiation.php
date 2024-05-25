<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negotiation extends Model
{
    use HasFactory;

    protected $fillable = [
        'brokerage_statement_id',
        'acronym',
        'quantity',
        'price',
        'total',
        'type',
        'created_at',
    ];
}
