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
     * Relacionamento one-to-many com VinylSecs
     */
    public function vinylSecs()
    {
        return $this->hasMany(VinylSec::class);
    }

    /**
     * Relacionamento many-to-many com categorias
     */
    public function categories()
    {
        return $this->belongsToMany(CatStyleShop::class, 'cat_style_shop_vinyl_master', 'vinyl_master_id', 'cat_style_shop_id');
    }

    /**
     * Relacionamento one-to-one com o primeiro VinylSec associado
     */
    public function vinylSec()
    {
        return $this->hasOne(VinylSec::class);
    }

    /**
     * Relacionamento polimórfico com produtos
     */
    public function products()
    {
        return $this->morphMany(Product::class, 'productable');
    }

    /**
     * Get optimized cover image URL
     */
    public function getOptimizedCoverImageAttribute()
    {
        if (!$this->cover_image) {
            return asset('images/vinyl-placeholder.svg');
        }

        // If it's already a full URL (from Discogs), return as is
        if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
            return $this->cover_image;
        }

        // For local images, ensure proper path
        $imagePath = $this->cover_image;
        if (!str_starts_with($imagePath, 'storage/')) {
            $imagePath = 'storage/' . ltrim($imagePath, '/');
        }

        return asset($imagePath);
    }

    /**
     * Get current price (promotional or regular)
     */
    public function getCurrentPriceAttribute()
    {
        if (!$this->vinylSec) {
            return null;
        }

        return $this->vinylSec->getCurrentPrice();
    }

    /**
     * Check if vinyl is complete (has vinylSec)
     */
    public function getIsCompleteAttribute()
    {
        return $this->vinylSec !== null;
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        if (!$this->vinylSec) {
            return 'Incompleto';
        }

        if ($this->vinylSec->is_promotional && $this->vinylSec->isOnPromotion()) {
            return 'Promocional';
        }

        if ($this->vinylSec->in_stock && $this->vinylSec->stock > 0) {
            return 'Em Estoque';
        }

        return 'Fora de Estoque';
    }
}
