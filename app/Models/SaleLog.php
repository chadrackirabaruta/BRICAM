<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'user_id',
        'action',
        'details',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function sale()
    {
        return $this->belongsTo(Sales::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}