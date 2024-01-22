<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiAssetsEvent extends Model
{
    use HasFactory;

    protected $table = 'fi_assets_events';

    protected $fillable = ['fi_asset_id', 'type', 'created_at'];

    // Desativa os timestamps se você não estiver usando-os
    // protected $timestamps = false;

    // Relacionamento com a model FiAsset
    public function fiAsset()
    {
        return $this->belongsTo(FiAsset::class);
    }
}
