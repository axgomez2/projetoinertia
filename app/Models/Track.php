<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Track extends Model
{
    use HasFactory;

    protected $fillable = [
        'vinyl_master_id',
        'name',
        'position',
        'duration',
        'youtube_url',
    ];

    /**
     * Relacionamento belongsTo com vinyl master
     */
    public function vinylMaster()
    {
        return $this->belongsTo(VinylMaster::class);
    }
}
