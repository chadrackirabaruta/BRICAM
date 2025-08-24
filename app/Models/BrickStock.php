<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrickStock extends Model
{
    protected $fillable = ['stock_type_id', 'quantity'];

    protected $casts = [
        'quantity' => 'integer'
    ];

    public function stockType()
    {
        return $this->belongsTo(StockType::class);
    }

    public function increase($amount)
    {
        $this->quantity += $amount;
        return $this->save();
    }

    public function decrease($amount)
    {
        if ($this->quantity < $amount) {
            throw new \Exception("Insufficient stock quantity");
        }

        $this->quantity -= $amount;
        return $this->save();
    }

    public function getCurrentQuantityAttribute()
    {
        return $this->quantity;
    }
}
