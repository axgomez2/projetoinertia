<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MidiaStatus extends Model
{
    use HasFactory;

    protected $table = 'midia_status';

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
