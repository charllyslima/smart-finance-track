<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiAsset extends Model
{
    use HasFactory;

    protected $table = 'fi_assets';

    protected $fillable = ['id', 'acronym', 'fundName', 'companyName', 'fundCategory'];

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }

    public function dividends()
    {
        return $this->hasMany(FiAssetsDividends::class);
    }

    public function events()
    {
        return $this->hasMany(FiAssetsEvent::class);
    }

    public function information()
    {
        return $this->hasOne(FiAssetsInformation::class);
    }
}
