<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'fi_asset_id', 'quantity', 'price', 'total', 'type', 'created_at'];

    public function fiAsset()
    {
        return $this->belongsTo(FiAsset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
