<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BANNED = 'banned';
    public const DEFAULT_STATUS = self::STATUS_ACTIVE;

    // Customer type constants
    public const TYPE_RETAIL = 'Retail';
    public const TYPE_WHOLESALE = 'Wholesale';
    public const TYPE_CONTRACTOR = 'Contractor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'id_number',
        'dob',
        'gender',
        'avatar',
        'customer_type_id',
        'status',
        'country',
        'province',
        'district',
        'sector',
        'cell',
        'village',
        'address',
        'registration_date',
        'last_purchase_date',
        'loyalty_points',
        'credit_limit'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dob' => 'date:Y-m-d',
        'registration_date' => 'date:Y-m-d',
        'last_purchase_date' => 'date:Y-m-d',
        'loyalty_points' => 'integer',
        'credit_limit' => 'decimal:2'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'avatar_url',
        'age',
        'full_address',
        'customer_status',
        'years_as_customer',
        'months_as_customer',
        'is_new_customer'
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the customer type associated with the customer.
     */
       public static function getCustomerType(): array
    {
        return [
            self::TYPE_RETAIL => 'Retail',
            self::TYPE_WHOLESALE => 'Wholesale',
            self::TYPE_CONTRACTOR => 'Contractor'
        ];
    }

    /**
     * Get all sales for the customer.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class);
    }

    /**
     * Get all payments for the customer.
     */


    /**
     * Get the user account associated with the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', self::STATUS_BANNED);
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('customer_type_id', $typeId);
    }

    public function scopeWithPurchasesBetween($query, $startDate, $endDate)
    {
        return $query->whereHas('sales', function($salesQuery) use ($startDate, $endDate) {
            $salesQuery->whereBetween('sale_date', [$startDate, $endDate]);
        });
    }

    public function scopeNewCustomers($query, $days = 30)
    {
        return $query->where('registration_date', '>=', now()->subDays($days));
    }

    public function scopeByLocation($query, $district)
    {
        return $query->where('district', $district);
    }

    public function scopeWithLoyaltyPoints($query, $minPoints)
    {
        return $query->where('loyalty_points', '>=', $minPoints);
    }

    // ==================== ACCESSORS ====================

    public function getAvatarUrlAttribute(): string
    {
        if (!$this->avatar) {
            return asset('images/default-avatar.png');
        }

        return Storage::exists($this->avatar)
            ? Storage::url($this->avatar)
            : asset('images/default-avatar.png');
    }


    public function getAgeAttribute(): ?int
    {
        return $this->dob?->age;
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->village,
            $this->cell,
            $this->sector,
            $this->district,
            $this->province,
            $this->country
        ]));
    }

    public function getCustomerStatusAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_BANNED => 'Banned',
            default => 'Unknown'
        };
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_BANNED => 'Banned'
        ];
    }

    public static function getCustomerTypeOptions(): array
    {
        return [
            self::TYPE_RETAIL => 'Retail',
            self::TYPE_WHOLESALE => 'Wholesale',
            self::TYPE_CONTRACTOR => 'Contractor'
        ];
    }

    public function getYearsAsCustomerAttribute(): float
    {
        if (!$this->registration_date) {
            return 0;
        }
        return round($this->registration_date->diffInYears(now(), true), 1);
    }

    public function getMonthsAsCustomerAttribute(): int
    {
        if (!$this->registration_date) {
            return 0;
        }
        return $this->registration_date->diffInMonths(now());
    }

    public function getIsNewCustomerAttribute(): bool
    {
        if (!$this->registration_date) {
            return false;
        }
        return $this->registration_date->gte(now()->subDays(90));
    }

    // ==================== BUSINESS LOGIC METHODS ====================

    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function deactivate(): bool
    {
        return $this->update(['status' => self::STATUS_INACTIVE]);
    }

    public function ban(): bool
    {
        return $this->update(['status' => self::STATUS_BANNED]);
    }

    public function reinstate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function addLoyaltyPoints(int $points): bool
    {
        return $this->update([
            'loyalty_points' => $this->loyalty_points + $points
        ]);
    }

    public function hasRecentPurchases($days = 30): bool
    {
        return $this->sales()
            ->where('sale_date', '>=', now()->subDays($days))
            ->exists();
    }

    // ==================== HELPER METHODS ====================

    public function canBeDeleted(): bool
    {
        return !$this->sales()->exists() && 
               !$this->payments()->exists() && 
               !$this->creditNotes()->exists();
    }

    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'customer_type' => $this->customerType->name,
            'customer_status' => $this->customer_status,
            'registration_date' => $this->registration_date?->format('Y-m-d'),
            'years_as_customer' => $this->years_as_customer,
            'is_new_customer' => $this->is_new_customer,
            'loyalty_points' => $this->loyalty_points,
            'avatar_url' => $this->avatar_url
        ];
    }

    public function getPurchaseFrequency(): float
    {
        if ($this->months_as_customer == 0) {
            return 0.0;
        }

        $purchaseCount = $this->sales()->count();
        return round($purchaseCount / $this->months_as_customer, 2);
    }

    public function getAveragePurchaseValue(): float
    {
        $totalSales = $this->sales()->sum('amount');
        $purchaseCount = $this->sales()->count();

        return $purchaseCount > 0 
            ? round($totalSales / $purchaseCount, 2)
            : 0.0;
    }

    public function isEligibleForLoyaltyReward(): bool
    {
        return $this->status === self::STATUS_ACTIVE &&
               $this->loyalty_points >= 1000 &&
               $this->hasRecentPurchases(90);
    }
}