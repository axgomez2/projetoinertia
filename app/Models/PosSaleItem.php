<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_sale_id',
        'vinyl_sec_id',
        'price',
        'quantity',
        'item_discount',
        'item_total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'item_discount' => 'decimal:2',
        'item_total' => 'decimal:2',
    ];

    public function posSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class);
    }

    public function vinylSec(): BelongsTo
    {
        return $this->belongsTo(VinylSec::class);
    }
}
