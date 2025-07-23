<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dimension extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'width',
        'height',
        'depth',
        'unit',
        'description',
    ];

    protected $casts = [
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'depth' => 'decimal:2',
    ];

    /**
     * Relacionamento com VinylSecs
     */
    public function vinylSecs()
    {
        return $this->hasMany(VinylSec::class);
    }

    /**
     * Obter dimensões formatadas
     */
    public function getFormattedDimensionsAttribute()
    {
        return "{$this->width} x {$this->height} x {$this->depth} {$this->unit}";
    }
}
