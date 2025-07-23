<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatStyleShop extends Model
{
    use HasFactory;

    protected $table = 'cat_style_shop';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Relacionamento many-to-many com VinylMaster via tabela pivot
     */
    public function vinylMasters()
    {
        return $this->belongsToMany(VinylMaster::class, 'cat_style_shop_vinyl_master', 'cat_style_shop_id', 'vinyl_master_id');
    }
}
