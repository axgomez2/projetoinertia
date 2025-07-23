<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoverStatus extends Model
{
    use HasFactory;

    protected $table = 'cover_status';

    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * Relacionamento com VinylSecs
     */
    public function vinylSecs()
    {
        return $this->hasMany(VinylSec::class);
    }
}
