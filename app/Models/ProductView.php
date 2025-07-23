<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductView extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByDateRange($query, $startDate, $endDate = null)
    {
        if (is_array($startDate)) {
            [$startDate, $endDate] = $startDate;
        }

        if ($startDate && $endDate) {
            return $query->whereBetween('viewed_at', [$startDate, $endDate]);
        }
        return $query;
    }

    public function scopeUniqueViews($query)
    {
        return $query->selectRaw('product_id, COUNT(DISTINCT COALESCE(user_id, ip_address)) as unique_views')
                    ->groupBy('product_id');
    }
}
