<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Production extends Model
{
    protected $fillable = [
        'employee_id',
        'production_date',
        'quantity',
        'product_type',
        'unit_price',
        'reference_number',
        'status',
        'remarks'
    ];

    protected $casts = [
        'production_date' => 'date',
        'quantity' => 'integer'
    ];

    protected $appends = ['available_quantity'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function transports()
    {
        return $this->hasMany(TransportRecord::class, 'production_reference');
    }

    public function getAvailableQuantityAttribute()
    {
        if (!$this->relationLoaded('transports')) {
            return $this->quantity - $this->transports()->sum('quantity');
        }

        return $this->quantity - $this->transports->sum('quantity');
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->whereRaw('quantity > (
            SELECT COALESCE(SUM(quantity), 0) 
            FROM transport_records 
            WHERE transport_records.production_reference = productions.id
        )');
    }

    public function scopeOfProductType(Builder $query, string $type)
    {
        return $query->where('product_type', $type);
    }

    public function scopeBeforeDate(Builder $query, $date)
    {
        return $query->where('production_date', '<=', $date);
    }

    public function hasAvailable(int $quantity): bool
    {
        return $this->available_quantity >= $quantity;
    }
}
