<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Production;
use App\Models\Employee;
use Carbon\Carbon;

class ProductionReportController extends Controller
{
    public function index(Request $request)
    {
        // Get all employees for the filter dropdown
        $employees = Employee::orderBy('name')->get();

        // Base query
        $query = Production::with('employee');

        // Filter: By Employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter: Dates
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('production_date', [
                $request->from_date,
                $request->to_date
            ]);
        } elseif (!$request->filled('from_date') && !$request->filled('to_date')) {
            // Default: Current Month
            $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth   = Carbon::now()->endOfMonth()->toDateString();

            $query->whereBetween('production_date', [$startOfMonth, $endOfMonth]);

            // Merge into request so UI shows current month in form
            $request->merge([
                'from_date' => $startOfMonth,
                'to_date'   => $endOfMonth
            ]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('production_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('production_date', '<=', $request->to_date);
        }

        // Fetch data
        $productions = $query->orderBy('production_date', 'desc')->get();

        // Totals
        $totalQuantity = $productions->sum('quantity');
        $totalValue    = $productions->sum(fn($p) => $p->quantity * $p->unit_price);

        // Chart + Grouped data
        $grouped = $productions->groupBy('employee.name');

        $chartLabels  = $grouped->keys()->toArray();
        $chartValues  = $grouped->map(fn($rows) => $rows->sum('quantity'))->values()->toArray();
        $chartAmounts = $grouped->map(fn($rows) => $rows->sum(fn($p) => $p->quantity * $p->unit_price))->values()->toArray();

        // Return to view
        return view('production.report', [
            'productions'   => $productions,
            'employees'     => $employees,
            'totalQuantity' => $totalQuantity,
            'totalValue'    => $totalValue,
            'chartLabels'   => $chartLabels,
            'chartValues'   => $chartValues,
            'chartAmounts'  => $chartAmounts,
        ]);
    }
}