<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrickStockLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'employee_id',
        'stock_type_id', 
        'action',
        'quantity',
        'stock_date',
        'reference',
        'remarks',
        'remaining' // Added missing field
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stock_date' => 'datetime',
        'quantity' => 'integer',
        'remaining' => 'integer'
    ];

    /**
     * Get the associated employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withDefault([
            'name' => 'Unknown Employee'
        ]);
    }

    /**
     * Get the associated stock type
     */
    public function stockType(): BelongsTo
    {
        return $this->belongsTo(StockType::class)->withDefault([
            'name' => 'Unknown Type'
        ]);
    }

    /**
     * Scope for increase actions
     */
    public function scopeIncreases($query)
    {
        return $query->where('action', 'increase');
    }

    /**
     * Scope for decrease actions
     */
    public function scopeDecreases($query)
    {
        return $query->where('action', 'decrease');
    }
}