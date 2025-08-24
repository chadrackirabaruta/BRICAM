<?php

// app/Http/Controllers/SalaryCalendarController.php
namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeType;
  use Symfony\Component\HttpFoundation\StreamedResponse;
class SalaryController extends Controller
{

public function Salaries(Request $request)
{
    $monthInput = $request->input('month', now()->format('Y-m'));
    [$year, $month] = explode('-', $monthInput);
    $employeeTypeFilter = $request->input('employee_type');

    $employeeTypes = EmployeeType::orderBy('name')->get();

    $employeeQuery = Employee::with('employeeType')->orderBy('name');
    if ($employeeTypeFilter) {
        $employeeQuery->whereHas('employeeType', function ($q) use ($employeeTypeFilter) {
            $q->where('name', $employeeTypeFilter);
        });
    }
    $employees = $employeeQuery->get();

    $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
    $end = $start->copy()->endOfMonth();
    $daysInMonth = $start->daysInMonth; // ✅ Add this line

    $salaryQuery = Salary::whereBetween('date', [$start, $end]);
    
    // Filter salaries by employee type if specified
    if ($employeeTypeFilter) {
        $employeeIds = $employees->pluck('id')->toArray();
        $salaryQuery->whereIn('employee_id', $employeeIds);
    }
    
    $salaries = $salaryQuery->get();

    $calendarData = [];
    $dailyTotals = [];

    foreach ($employees as $employee) {
        $row = [
            'employee' => $employee,
            'days' => [],
            'total' => 0,
        ];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');

            $dayAmount = $salaries
                ->where('employee_id', $employee->id)
                ->where('date', $currentDate)
                ->sum('amount');

            $row['days'][$day] = $dayAmount;
            $row['total'] += $dayAmount;

            $dailyTotals[$day] = ($dailyTotals[$day] ?? 0) + $dayAmount;
        }

        $calendarData[] = $row;
    }

    return view('payroll.salary', compact(
        'year', 'month', 'daysInMonth', 'calendarData', 'dailyTotals', 'employeeTypes'
    ));
  }



  public function salaryCalendar(Request $request)
{
    // Get month and year from request or default to current
    $month = $request->input('month', now()->month);
    $year = $request->input('year', now()->year);
    $employeeTypeFilter = $request->input('employee_type');
    
    // Handle month-year input format (e.g., "2024-12")
    if ($request->has('month') && strpos($request->input('month'), '-') !== false) {
        $monthYear = explode('-', $request->input('month'));
        $year = (int)$monthYear[0];
        $month = (int)$monthYear[1];
    }
    
    $daysInMonth = Carbon::create($year, $month)->daysInMonth;
    
    // Get employee types for filter
    $employeeTypes = EmployeeType::orderBy('name')->get();
    
    // Get employees based on filter
    $employeesQuery = Employee::active()
        ->with('employeeType')
        ->orderBy('name');
    
    if ($employeeTypeFilter) {
        $employeesQuery->whereHas('employeeType', function($q) use ($employeeTypeFilter) {
            $q->where('name', $employeeTypeFilter);
        });
    }
    
    $employees = $employeesQuery->get();
    
    // Initialize arrays
    $calendarData = [];
    $dailyTotals = array_fill(1, $daysInMonth, 0);
    $weeklyTotals = [];
    
    // Process each employee
    foreach ($employees as $employee) {
        // Skip if employee is null or has no name
        if (!$employee || empty($employee->name)) {
            continue;
        }
        
        $employeeData = [
            'employee' => $employee,
            'days' => array_fill(1, $daysInMonth, 0),
            'total' => 0,
            'weekly_totals' => []
        ];
        
        // Get salaries for this employee for the month
        $salaries = Salary::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function($salary) {
                return Carbon::parse($salary->date)->day;
            });
        
        // Calculate weekly totals
        $currentWeekSum = 0;
        $weekNumber = 1;
        
        // Fill in the salary data for each day
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::create($year, $month, $day);
            $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 6 = Saturday
            
            $amount = 0;
            if (isset($salaries[$day])) {
                $amount = (float)$salaries[$day]->amount;
            }
            
            $employeeData['days'][$day] = $amount;
            $employeeData['total'] += $amount;
            $dailyTotals[$day] += $amount;
            $currentWeekSum += $amount;
            
            // Check if we're at the end of a week (Saturday) or end of month
            if ($dayOfWeek == 6 || $day == $daysInMonth) {
                $employeeData['weekly_totals'][$weekNumber] = $currentWeekSum;
                
                // Initialize weekly total if not exists
                if (!isset($weeklyTotals[$weekNumber])) {
                    $weeklyTotals[$weekNumber] = 0;
                }
                $weeklyTotals[$weekNumber] += $currentWeekSum;
                
                $currentWeekSum = 0;
                $weekNumber++;
            }
        }
        
        // Only add employee data if they have a valid name
        if (!empty($employee->name)) {
            $calendarData[] = $employeeData;
        }
    }
    
    // Calculate week boundaries for display
    $weekBoundaries = [];
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = Carbon::create($year, $month, $day);
        $dayOfWeek = $currentDate->dayOfWeek;
        
        if ($dayOfWeek == 6 || $day == $daysInMonth) {
            $weekNumber = ceil($day / 7);
            $weekBoundaries[$day] = $weekNumber;
        }
    }
    $weeklySum = $weeklyTotals; 
    return view('payroll.salary-calendar', compact(
        'calendarData',
        'dailyTotals',
        'weeklyTotals',
        'weekBoundaries',
        'year',
        'month',
        'daysInMonth',
        'employeeTypes',
          'weeklySum' 
    ));
}

// Export CSV function
public function exportCsv(Request $request)
{
    $month = $request->input('month', now()->month);
    $year = $request->input('year', now()->year);
    $employeeTypeFilter = $request->input('employee_type');
    
    // Handle month-year input format
    if ($request->has('month') && strpos($request->input('month'), '-') !== false) {
        $monthYear = explode('-', $request->input('month'));
        $year = (int)$monthYear[0];
        $month = (int)$monthYear[1];
    }
    
    $daysInMonth = Carbon::create($year, $month)->daysInMonth;
    
    // Get the same data as calendar view
    $employeesQuery = Employee::active()
        ->with('employeeType')
        ->orderBy('name');
    
    if ($employeeTypeFilter) {
        $employeesQuery->whereHas('employeeType', function($q) use ($employeeTypeFilter) {
            $q->where('name', $employeeTypeFilter);
        });
    }
    
    $employees = $employeesQuery->get();
    
    // Create CSV content
    $csvData = [];
    
    // Header row
    $header = ['#', 'Employee Name'];
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = Carbon::create($year, $month, $day);
        $dayOfWeek = $currentDate->dayOfWeek;
        
        $header[] = $day . $currentDate->format('\\S\\u\\p');
        
        // Add weekly total column after Saturday or last day
        if ($dayOfWeek == 6 || $day == $daysInMonth) {
            $weekNumber = ceil($day / 7);
            $header[] = "Week {$weekNumber}";
        }
    }
    $header[] = 'Monthly Total';
    $csvData[] = $header;
    
    // Employee rows
    $dailyTotals = array_fill(1, $daysInMonth, 0);
    $weeklyTotals = [];
    
    foreach ($employees as $index => $employee) {
        if (!$employee || empty($employee->name)) {
            continue;
        }
        
        $row = [$index + 1, $employee->name];
        
        // Get salaries for this employee
        $salaries = Salary::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function($salary) {
                return Carbon::parse($salary->date)->day;
            });
        
        $monthlyTotal = 0;
        $currentWeekSum = 0;
        $weekNumber = 1;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::create($year, $month, $day);
            $dayOfWeek = $currentDate->dayOfWeek;
            
            $amount = isset($salaries[$day]) ? (float)$salaries[$day]->amount : 0;
            $row[] = number_format($amount, 0);
            
            $monthlyTotal += $amount;
            $currentWeekSum += $amount;
            $dailyTotals[$day] += $amount;
            
            // Add weekly total after Saturday or last day
            if ($dayOfWeek == 6 || $day == $daysInMonth) {
                $row[] = number_format($currentWeekSum, 0);
                
                if (!isset($weeklyTotals[$weekNumber])) {
                    $weeklyTotals[$weekNumber] = 0;
                }
                $weeklyTotals[$weekNumber] += $currentWeekSum;
                
                $currentWeekSum = 0;
                $weekNumber++;
            }
        }
        
        $row[] = number_format($monthlyTotal, 0);
        $csvData[] = $row;
    }
    
    // Total row
    $totalRow = ['', 'TOTAL PER DAY'];
    $weekNumber = 1;
    $currentWeekSum = 0;
    
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = Carbon::create($year, $month, $day);
        $dayOfWeek = $currentDate->dayOfWeek;
        
        $totalRow[] = number_format($dailyTotals[$day], 0);
        $currentWeekSum += $dailyTotals[$day];
        
        if ($dayOfWeek == 6 || $day == $daysInMonth) {
            $totalRow[] = number_format($currentWeekSum, 0);
            $currentWeekSum = 0;
            $weekNumber++;
        }
    }
    
    $totalRow[] = number_format(array_sum($dailyTotals), 0);
    $csvData[] = $totalRow;
    
    // Generate CSV file
    $filename = "salary_calendar_{$year}_{$month}.csv";
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    $callback = function() use ($csvData) {
        $file = fopen('php://output', 'w');
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}
public function wagesIndex(Request $request)
{
    $perPage = $request->input('per_page', 15);
    $search = $request->input('search');
    $typeFilter = $request->input('employee_type'); // Can be 'all', null, or a valid ID
    $monthFilter = $request->input('month');
    $yearFilter = $request->input('year');
    $employeeIdFilter = $request->input('employee_id');
    $amountMin = $request->input('amount_min');
    $amountMax = $request->input('amount_max');

    $wages = Salary::with(['employee', 'employee.employeeType'])
        ->when($search, function ($query) use ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        })
        ->when($typeFilter && $typeFilter !== 'all', function ($query) use ($typeFilter) {
            $query->whereHas('employee.employeeType', function ($q) use ($typeFilter) {
                $q->where('id', $typeFilter);
            });
        })
        ->when($employeeIdFilter, function ($query) use ($employeeIdFilter) {
            $query->where('employee_id', $employeeIdFilter);
        })
        ->when($monthFilter, function ($query) use ($monthFilter) {
            $query->whereMonth('date', $monthFilter);
        })
        ->when($yearFilter, function ($query) use ($yearFilter) {
            $query->whereYear('date', $yearFilter);
        })
        ->when($amountMin, function ($query) use ($amountMin) {
            $query->where('amount', '>=', $amountMin);
        })
        ->when($amountMax, function ($query) use ($amountMax) {
            $query->where('amount', '<=', $amountMax);
        })
        ->orderByDesc('date')
        ->paginate($perPage);

    // Get all active employees for filters
    $allEmployees = Employee::active()
        ->with('employeeType')
        ->orderBy('name')
        ->get();

    // Get employees for add/edit forms
    $employees = Employee::active()
        ->orderBy('name')
        ->get();

    // Get employee types with an 'All' option
    $employeeTypes = EmployeeType::orderBy('name')->pluck('name', 'id');
    $employeeTypes = collect(['all' => 'All Types'])->union($employeeTypes);
    $defaultEmployeeTypeId = EmployeeType::where('name', 'default')->value('id') ?? 3; // fallback if not found



    // Get available years from wages data
    $years = Salary::selectRaw('YEAR(date) as year')
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    return view('payroll.wages', compact(
        'wages',
        'employees',
        'allEmployees',
        'employeeTypes',
        'years',
        'defaultEmployeeTypeId'
    ));
}

    
    /**
     * Store a newly created wage record
     */
   public function storeWage(Request $request)
{
    $validated = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'amount' => 'required|numeric|min:0|max:9999999',
        'date' => 'required|date|before_or_equal:today',
        'notes' => 'nullable|string|max:500',
    ]);

    try {
        // Check for existing wage for same employee on same date
        $existing = Salary::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $validated['date'])
            ->exists();

        if ($existing) {
            return back()
                ->withInput()
                ->with('error', 'A wage for this employee has already been recorded on this date.');
        }

        DB::transaction(function () use ($validated) {
            $employee = Employee::findOrFail($validated['employee_id']);

            Salary::create([
                'employee_id' => $employee->id,
                'employee_type' => $employee->type,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Optional: add salary history tracking logic here
        });

        return redirect()
            ->route('payroll.wages.index')
            ->with('success', 'Wage recorded successfully.');

    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Failed to record wage: ' . $e->getMessage());
    }
}

    /**
     * Update the specified wage record
     */
    public function updateWage(Request $request, Salary $wage)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:9999999',
            'date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $wage->update($validated);

            return redirect()
                ->route('payroll.wages.index')
                ->with('success', 'Wage updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update wage: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified wage record
     */
    public function destroyWage(Salary $wage)
    {
        try {
            $wage->delete();

            return redirect()
                ->route('payroll.wages.index')
                ->with('success', 'Wage deleted successfully.');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete wage: ' . $e->getMessage());
        }
    }

    /**
     * Export wages to CSV
     */
    public function exportWages(Request $request)
    {
        $wages = Salary::with('employee')
            ->orderByDesc('date')
            ->get();

        $fileName = 'wages_export_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($wages) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Employee Name',
                'Employee Type',
                'Amount',
                'Date',
                'Notes'
            ]);
            
            // Data rows
            foreach ($wages as $wage) {
                fputcsv($file, [
                    $wage->employee->name,
                    $wage->employee_type,
                    $wage->amount,
                    $wage->date,
                    $wage->notes
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
}

