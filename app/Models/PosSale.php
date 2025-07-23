<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'subtotal',
        'discount',
        'shipping',
        'total',
        'payment_method',
        'notes',
        'invoice_number',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer_name ?? $this->user?->name ?? 'Cliente não identificado';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'money' => 'Dinheiro',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'pix' => 'PIX',
            'bank_transfer' => 'Transferência Bancária',
            default => ucfirst($this->payment_method)
        };
    }

    public static function generateInvoiceNumber(): string
    {
        do {
            $number = 'POS-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('invoice_number', $number)->exists());

        return $number;
    }

    public function scopeByPaymentMethod($query, $paymentMethod)
    {
        if ($paymentMethod) {
            return $query->where('payment_method', $paymentMethod);
        }
        return $query;
    }

    public function scopeByDateRange($query, $startDate, $endDate = null)
    {
        if (is_array($startDate)) {
            [$startDate, $endDate] = $startDate;
        }

        if ($startDate && $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        return $query;
    }
}
