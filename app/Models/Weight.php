<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weight extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'unit',
    ];

    /**
     * Relacionamento com VinylSecs
     */
    public function vinylSecs()
    {
        return $this->hasMany(VinylSec::class);
    }
}
