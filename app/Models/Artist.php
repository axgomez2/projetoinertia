<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'discogs_id',
        'profile',
        'images',
        'discogs_url',
    ];

    /**
     * Relacionamento many-to-many com vinyl masters
     */
    public function vinylMasters()
    {
        return $this->belongsToMany(VinylMaster::class, 'artist_vinyl_master');
    }
}
