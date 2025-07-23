<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'document',
        'document_type',
        'address',
        'city',
        'state',
        'zipcode',
        'website',
        'last_purchase_date',
        'notes',
    ];

    protected $casts = [
        'last_purchase_date' => 'date',
    ];

    public function vinylSecs(): HasMany
    {
        return $this->hasMany(VinylSec::class);
    }

    /**
     * Scope para fornecedores ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
