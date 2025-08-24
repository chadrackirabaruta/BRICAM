<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_type', // e.g. 'daily', 'monthly', 'contract'
        'date',
        'amount',
    ];

    /**
     * Relationship to Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Accessor: Format salary date (optional)
     */
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->date)->format('d M Y');
    }

    /**
     * Scope: Filter by month
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->whereYear('date', $year)
                     ->whereMonth('date', $month);
    }

    /**
     * Scope: Filter by employee type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('employee_type', $type);
    }
    public function scopeForPeriod($query, $year, $month = null)
{
    $query = $query->whereYear('date', $year);
    if ($month) {
        $query->whereMonth('date', $month);
    }}
}
