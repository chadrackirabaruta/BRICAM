<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Sales extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_id',
        'employee_id',
        'stock_type_id',
        'sale_date',
        'quantity',
        'unit_price',
        'total_price',
        'payment_method',
        'notes',
        'status',
        'reference_number'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sale_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    /**
     * The attributes with default values.
     */
    protected $attributes = [
        'status' => 'completed',
        'payment_method' => 'cash'
    ];

    /**
     * Relationships
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withDefault([
            'name' => '[Deleted Customer]'
        ]);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withDefault([
            'name' => '[Unknown Employee]'
        ]);
    }

    public function stockType(): BelongsTo
    {
        return $this->belongsTo(StockType::class)->withDefault([
            'name' => '[Unknown Type]'
        ]);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class)->orderByDesc('created_at');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SaleLog::class)->latest();
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('sale_date', '>=', now()->subDays($days));
    }

    /**
     * Accessors & Mutators
     */
    public function getBalanceAttribute(): float
    {
        if ($this->payment_method !== 'credit') {
            return 0;
        }
        return max(0, $this->total_price - $this->payments()->sum('amount'));
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_price, 2);
    }

    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = max(1, (int) $value);
    }

    /**
     * Model Events
     */
    protected static function booted()
    {
        static::creating(function ($sale) {
            if (empty($sale->reference_number)) {
                $sale->reference_number = static::generateReferenceNumber();
            }
            
            if (empty($sale->sale_date)) {
                $sale->sale_date = now();
            }
        });

        static::created(function ($sale) {
            try {
                $sale->logs()->create([
                    'action' => 'created',
                    'details' => 'Sale created',
                    'user_id' => $sale->resolveLogUserId()
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to create sale log: ".$e->getMessage());
            }
        });

        static::updated(function ($sale) {
            $changes = collect($sale->getChanges())
                ->except(['updated_at', 'reference_number'])
                ->toArray();

            if (!empty($changes)) {
                $sale->logs()->create([
                    'action' => 'updated',
                    'details' => json_encode($changes),
                    'user_id' => $sale->resolveLogUserId()
                ]);
            }
        });
    }

    /**
     * Helper Methods
     */
    public function resolveLogUserId(): ?int
    {
        if (Auth::check()) {
            return Auth::id();
        }

        if ($this->employee_id) {
            return $this->employee_id;
        }

        return config('system.default_user_id', 1); // Fallback to admin user
    }

    public static function generateReferenceNumber(): string
    {
        $prefix = config('sales.reference_prefix', 'SALE-');
        $date = now()->format('Ymd');
        
        $lastSale = static::where('reference_number', 'like', $prefix.$date.'%')
            ->orderByDesc('id')
            ->first();

        $sequence = $lastSale ? 
            (int) substr($lastSale->reference_number, -4) + 1 : 1;

        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }

    public function addPayment(float $amount, string $method, ?string $notes = null): SalePayment
    {
        return $this->payments()->create([
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'notes' => $notes,
            'recorded_by' => $this->resolveLogUserId()
        ]);
    }
}