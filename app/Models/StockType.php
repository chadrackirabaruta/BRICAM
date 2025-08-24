<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockType extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'flow_stage',
        'decrease_from',  // Integer value like 300, 500
        'increase_to'     // Integer value like 300, 500
    ];

    public function brickStock()
    {
        return $this->hasOne(BrickStock::class);
    }

    public function parent()
    {
        return $this->belongsTo(StockType::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(StockType::class, 'parent_id');
    }

    public function canReceiveStock()
    {
        return !is_null($this->increase_to) && $this->increase_to > 0;
    }

    public function canGiveStock()
    {
        return !is_null($this->decrease_from) && $this->decrease_from > 0;
    }

    // Optional helper to trace backward
    public function previousStockType()
    {
        return self::where('increase_to', $this->decrease_from)->orderByDesc('flow_stage')->first();
    }

    public function stockEntries()
{
    return $this->hasMany(BrickStock::class);
}

}
