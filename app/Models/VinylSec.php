<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VinylSec extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vinyl_master_id',
        'catalog_number',
        'barcode',
        'internal_code',
        'weight_id',
        'dimension_id',
        'midia_status_id',
        'cover_status_id',
        'supplier_id',
        'stock',
        'price',
        'format',
        'num_discs',
        'speed',
        'edition',
        'notes',
        'is_new',
        'buy_price',
        'promotional_price',
        'is_promotional',
        'promo_starts_at',
        'promo_ends_at',
        'in_stock',
        'is_presale',
        'presale_arrival_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'buy_price' => 'decimal:2',
        'promotional_price' => 'decimal:2',
        'is_new' => 'boolean',
        'is_promotional' => 'boolean',
        'in_stock' => 'boolean',
        'is_presale' => 'boolean',
        'promo_starts_at' => 'datetime',
        'promo_ends_at' => 'datetime',
        'presale_arrival_date' => 'date',
    ];

    /**
     * Relacionamento com VinylMaster
     */
    public function vinylMaster()
    {
        return $this->belongsTo(VinylMaster::class);
    }

    /**
     * Relacionamento com Weight
     */
    public function weight()
    {
        return $this->belongsTo(Weight::class);
    }

    /**
     * Relacionamento com Dimension
     */
    public function dimension()
    {
        return $this->belongsTo(Dimension::class);
    }

    /**
     * Relacionamento com MidiaStatus
     */
    public function midiaStatus()
    {
        return $this->belongsTo(MidiaStatus::class);
    }

    /**
     * Relacionamento com CoverStatus
     */
    public function coverStatus()
    {
        return $this->belongsTo(CoverStatus::class);
    }

    /**
     * Relacionamento com Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relacionamento many-to-many com CatStyleShop
     */
    public function categories()
    {
        return $this->belongsToMany(CatStyleShop::class, 'cat_style_shop_vinyl_master', 'vinyl_master_id', 'cat_style_shop_id');
    }

    /**
     * Gerar código interno único
     */
    public static function generateInternalCode()
    {
        do {
            $code = 'RDV-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('internal_code', $code)->exists());

        return $code;
    }

    /**
     * Verificar se está em promoção
     */
    public function isOnPromotion()
    {
        if (!$this->is_promotional || !$this->promotional_price) {
            return false;
        }

        $now = now();
        
        if ($this->promo_starts_at && $now->lt($this->promo_starts_at)) {
            return false;
        }

        if ($this->promo_ends_at && $now->gt($this->promo_ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Obter preço atual (promocional ou normal)
     */
    public function getCurrentPrice()
    {
        return $this->isOnPromotion() ? $this->promotional_price : $this->price;
    }
}
