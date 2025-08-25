<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    // ==================== ATTRIBUTES ====================

    protected $fillable = [
        'code','name','email','phone','id_number','dob','gender','avatar',
        'employee_type_id','salary_type_id','country','province','district',
        'sector','cell','village','active','hire_date','termination_date',
        'emergency_contact','position','production_rate'
    ];

    protected $casts = [
        'dob' => 'date:Y-m-d',
        'hire_date' => 'date:Y-m-d',
        'termination_date' => 'date:Y-m-d',
        'active' => 'boolean',
        'production_rate' => 'decimal:2'
    ];

    protected $appends = [
        'avatar_url','age','full_address','employment_status',
        'years_of_service','months_of_service','is_new_hire'
    ];

    // ==================== RELATIONSHIPS ====================

    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class)->withDefault([
            'name' => 'Unknown Type'
        ]);
    }

    public function salaryType(): BelongsTo
    {
        return $this->belongsTo(SalaryType::class)->withDefault([
            'name' => 'Unspecified'
        ]);
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    public function transportRecords(): HasMany
    {
        return $this->hasMany(TransportRecord::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    // ==================== SCOPES ====================

    public function scopeActive($query) { return $query->where('active', true); }
    public function scopeInactive($query) { return $query->where('active', false); }
    public function scopeByType($query, $typeId) { return $query->where('employee_type_id', $typeId); }
    public function scopeHiredBetween($query, $startDate, $endDate) { return $query->whereBetween('hire_date', [$startDate, $endDate]); }
    public function scopeCurrentlyEmployed($query) { return $query->where('active', true)->whereNull('termination_date'); }
    public function scopeTerminated($query) { return $query->whereNotNull('termination_date'); }
    public function scopeWithSalesBetween($query, $startDate, $endDate)
    {
        return $query->whereHas('sales', fn($q) => $q->whereBetween('sale_date', [$startDate, $endDate]));
    }
    public function scopeNewHires($query, $startDate, $endDate) { return $query->whereBetween('hire_date', [$startDate, $endDate]); }
    public function scopeByPosition($query, $position) { return $query->where('position', 'like', "%{$position}%"); }

    // ==================== ACCESSORS ====================

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar && Storage::exists($this->avatar)
            ? Storage::url($this->avatar)
            : asset('images/default-avatar.png');
    }

    public function getAgeAttribute(): ?int { return $this->dob?->age; }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([$this->village,$this->cell,$this->sector,$this->district,$this->province,$this->country]));
    }

    public function getEmploymentStatusAttribute(): string
    {
        if ($this->termination_date) return 'Terminated';
        return $this->active ? 'Active' : 'Inactive';
    }

    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->hire_date) return 0;
        $end = $this->termination_date ?? now();
        return round($this->hire_date->diffInYears($end, true), 1);
    }

    public function getMonthsOfServiceAttribute(): int
    {
        if (!$this->hire_date) return 0;
        $end = $this->termination_date ?? now();
        return $this->hire_date->diffInMonths($end);
    }

    public function getIsNewHireAttribute(): bool
    {
        return $this->hire_date && $this->hire_date->gte(now()->subDays(90));
    }

    // ==================== BUSINESS LOGIC ====================

    public function activate(): bool { return $this->update(['active' => true]); }
    public function deactivate(): bool { return $this->update(['active' => false]); }
    public function terminate($date = null): bool { return $this->update(['termination_date' => $date ?? now(),'active' => false]); }
    public function reinstate(): bool { return $this->update(['termination_date' => null,'active' => true]); }

    public function hasSalesActivity($startDate = null, $endDate = null): bool
    {
        $query = $this->sales();
        if ($startDate && $endDate) $query->whereBetween('sale_date', [$startDate, $endDate]);
        return $query->exists();
    }

    public function canBeDeleted(): bool
    {
        return !$this->sales()->exists() && 
               !$this->productions()->exists() && 
               !$this->transportRecords()->exists();
    }

    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'position' => $this->position,
            'employment_status' => $this->employment_status,
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'years_of_service' => $this->years_of_service,
            'is_new_hire' => $this->is_new_hire,
            'employee_type' => $this->employeeType->name,
            'avatar_url' => $this->avatar_url
        ];
    }

    public function getProductionEfficiency(): float
    {
        if (!$this->production_rate) return 0.0;
        $positionAvg = self::where('position', $this->position)->avg('production_rate');
        return $positionAvg ? round(($this->production_rate / $positionAvg) * 100, 2) : 0.0;
    }

    public function getRecentSalesPerformance(): array
    {
        $sales = $this->sales()
                      ->where('sale_date', '>=', now()->subDays(30))
                      ->selectRaw('SUM(amount) as total, COUNT(*) as count')
                      ->first();
        return [
            'total_sales' => $sales->total ?? 0,
            'sales_count' => $sales->count ?? 0,
            'average_sale' => $sales->count ? $sales->total / $sales->count : 0
        ];
    }

    public function isEligibleForPromotion(): bool
    {
        if ($this->years_of_service < 1) return false;
        $performance = $this->getRecentSalesPerformance();
        if ($performance['total_sales'] < 10000) return false;
        return is_null($this->termination_date) && $this->active;
    }
}
