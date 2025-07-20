<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'product_type_id',
        'productable_id',
        'productable_type',
    ];

    /**
     * Relacionamento polimórfico
     */
    public function productable()
    {
        return $this->morphTo();
    }
}
