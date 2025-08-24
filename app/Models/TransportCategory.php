<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'unit_price'];

    public function transportRecords()
    {
        return $this->hasMany(TransportRecord::class, 'transport_category_id');
    }
}
