<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeType extends Model
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
     * Get the employees associated with this type.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}