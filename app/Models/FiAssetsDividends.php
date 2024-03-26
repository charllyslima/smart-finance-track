<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiAssetsDividends extends Model
{
    use HasFactory;

    protected $fillable = ['value', 'base_date', 'payment_date', 'reference_date', 'amortization'];

    public function fiAsset()
    {
        return $this->belongsTo(FiAsset::class);
    }

}
