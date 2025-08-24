<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'transport_category_id',
        'stock_type_id',
        'production_reference',
        'transport_date',
        'production_date',
        'quantity',
        'unit_price',
        'total_price',
        'destination',
        'source_location',
        'status',
        'reference_number',
        'remarks'
    ];

    protected $casts = [
        'transport_date' => 'datetime:Y-m-d',
        'production_date' => 'datetime:Y-m-d',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'deleted_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Relationship Fixes
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withDefault([
            'name' => '[Deleted Employee]'
        ]);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransportCategory::class, 'transport_category_id')
            ->withDefault([
                'name' => 'Uncategorized'
            ]);
    }

    public function stockType(): BelongsTo
    {
        return $this->belongsTo(StockType::class)
            ->withDefault([
                'name' => 'Unknown Type'
            ]);
    }

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class, 'production_reference', 'id')
            ->withDefault([
                'reference_number' => 'N/A'
            ]);
    }


    public function getTotalAttribute(): float
    {
        return (float)($this->unit_price * $this->quantity);
    }

    public function getRouteAttribute(): ?string
    {
        return $this->source_location && $this->destination 
            ? "{$this->source_location} → {$this->destination}"
            : null;
    }
    

    // Status Checkers
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transport_date', [
            $startDate->startOfDay(),
            $endDate->endOfDay()
        ]);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Business Logic
    public function markCompleted(): bool
    {
        return $this->update(['status' => self::STATUS_COMPLETED]);
    }

 public function cancel(?string $remarks = null): bool
{
    return $this->update([
        'status' => self::STATUS_CANCELLED,
        'remarks' => $remarks ?? $this->remarks
    ]);
}
}