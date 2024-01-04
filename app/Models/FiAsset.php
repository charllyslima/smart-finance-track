<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiAsset extends Model
{
    use HasFactory;

    protected $table = 'fi_assets';

    protected $fillable = ['acronym', 'fundName', 'companyName'];
}
