<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'shipping_address',
        'billing_address',
        'notes',
        'processed_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'processed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->user->name ?? 'N/A';
    }

    public function getCustomerEmailAttribute(): string
    {
        return $this->user->email ?? 'N/A';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado',
            default => ucfirst($this->status)
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'failed' => 'Falhou',
            'refunded' => 'Reembolsado',
            default => ucfirst($this->payment_status)
        };
    }

    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        if ($paymentStatus) {
            return $query->where('payment_status', $paymentStatus);
        }
        return $query;
    }

    public function scopeByDateRange($query, $startDate, $endDate = null)
    {
        // Handle array input from predefined ranges
        if (is_array($startDate)) {
            [$startDate, $endDate] = $startDate;
        }

        if ($startDate && $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        return $query;
    }

    public function scopeByCustomer($query, $customerId)
    {
        if ($customerId) {
            return $query->where('user_id', $customerId);
        }
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        return $query;
    }
}
