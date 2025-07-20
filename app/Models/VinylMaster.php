<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VinylMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'discogs_id',
        'description',
        'cover_image',
        'discogs_url',
        'release_year',
        'country',
    ];

    /**
     * Relacionamento many-to-many com artistas
     */
    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'artist_vinyl_master');
    }

    /**
     * Relacionamento many-to-many com gravadoras
     */
    public function recordLabels()
    {
        return $this->belongsToMany(RecordLabel::class, 'record_label_vinyl_master');
    }

    /**
     * Relacionamento one-to-many com faixas
     */
    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    /**
     * Relacionamento polimórfico com produtos
     */
    public function products()
    {
        return $this->morphMany(Product::class, 'productable');
    }
}
