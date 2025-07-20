<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecordLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'discogs_id',
        'discogs_url',
        'description',
        'logo',
    ];

    /**
     * Relacionamento many-to-many com vinyl masters
     */
    public function vinylMasters()
    {
        return $this->belongsToMany(VinylMaster::class, 'record_label_vinyl_master');
    }
}
