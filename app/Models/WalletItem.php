<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletItem extends Model
{
    use HasFactory;

    protected $fillable = ['wallet_id', 'fi_asset_id', 'average_value', 'quantity'];
}
