<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
 protected $dates = ['created_at', 'updated_at']; // Laravel will automatically cast these to Carbon instances

    /**
     * Get the salary payments associated with this type.
     */
    public function salaryPayments()
    {
        //return $this->hasMany(SalaryPayment::class);
    }
}