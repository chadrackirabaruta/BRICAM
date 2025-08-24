<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\Employee;
use App\Models\ProductionStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductionController extends Controller
{
    /**
     * Show filtered or recent production records
     */
    public function index(Request $request)
    {
        $employees = Employee::orderBy('name')->get();

        $query = Production::with('employee')->latest();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('production_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('production_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('production_date', '<=', $request->to_date);
        } else {
            $query->whereDate('production_date', Carbon::today());
        }

        $productions = $query->get();

        return view('production.index', compact('productions', 'employees'));

          // ✅ New block: production stock summary
    $stock = ProductionStock::first();
    $totalProduced = $stock->total_quantity ?? 0;
    $remaining = $stock->remaining_quantity ?? 0;
    $used = $totalProduced - $remaining;

    return view('production.productionsummary', compact(
        'employees',
        'productions',
        'totalProduced',
        'used',
        'remaining'
    ));


    }

    /**
     * Show the form to create a production record
     */
    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        return view('production.create', compact('employees'));
    }

    /**
     * Store new production and update central production stock
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'production_date' => 'required|date|before_or_equal:today',
        'quantity' => 'required|integer|min:1',
        'unit_price' => 'required|numeric|min:1',
        'remarks' => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {
        // Step 1: Save production record
        $production = Production::create($validated);

        // Step 2: Update central production stock
        $stock = ProductionStock::firstOrCreate([], [
            'total_quantity' => 0,
            'remaining_quantity' => 0,
        ]);
        $stock->increment('total_quantity', $validated['quantity']);
        $stock->increment('remaining_quantity', $validated['quantity']);

        // Step 3: Get employee & store salary
        $employee = \App\Models\Employee::findOrFail($validated['employee_id']);

        \App\Models\Salary::create([
    'employee_id'   => $employee->id,
    'employee_type' => $employee->employeeType->name ?? 'Unknown', // ✅ FIXED LINE
    'date'          => $validated['production_date'],
    'amount'        => $validated['quantity'] * $validated['unit_price'],
]);

        DB::commit();

        return redirect()->route('productions.index')
            ->with('success', 'Production & salary recorded successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error storing production and salary: ' . $e->getMessage());

        return back()->withInput()
            ->with('error', 'Failed to store production and salary.');
    }
}




    /**
     * Show the edit form for a production record
     */
    public function edit(Production $production)
    {
        $employees = Employee::orderBy('name')->get();
        return view('production.edit', compact('production', 'employees'));
    }

    /**
     * Update production record
     * NOTE: Does not change stock to avoid overediting. Consider locking production after transport!
     */
    public function update(Request $request, Production $production)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'production_date' => 'required|date|before_or_equal:today',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        try {
            $production->update($validated);

            // ⚠️ Optional: Adjust production_stocks if needed here
            // Not included here to avoid creating inconsistencies

            return redirect()->route('productions.index')->with('success', 'Production updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating production: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update production.');
        }
    }

    /**
     * Delete a production record
     * NOTE: This will not reverse stock deduction. Only delete if no transport has used it.
     */
    public function destroy(Production $production)
    {
        try {
            $production->delete();

            // ⚠️ Optional: You may deduct from production_stocks here as well
            return redirect()->route('productions.index')->with('success', 'Production record deleted.');
        } catch (\Exception $e) {
            Log::error('Production deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete record.');
        }
    }


public function summary(Request $request)
{
    $filter = $request->input('filter', 'today');
    $start = null;
    $end = null;

    switch ($filter) {
        case 'week':
            $start = Carbon::now()->startOfWeek();
            $end = Carbon::now()->endOfWeek();
            break;
        case 'month':
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            break;
        case 'year':
            $start = Carbon::now()->startOfYear();
            $end = Carbon::now();
            break;
        case 'custom':
            $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
            $end = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today();
            break;
        case 'today':
        default:
            $start = Carbon::today();
            $end = Carbon::now();
            break;
    }

    // Get quantity produced in the selected period
    $produced = Production::whereBetween('production_date', [$start, $end])->sum('quantity');

    // Get global stock summary
    $stock = ProductionStock::first();
    $total = $stock->total_quantity ?? 0;
    $remaining = $stock->remaining_quantity ?? 0;
    $used = $total - $remaining;

    return view('production.productionsummary', compact(
        'total', 'used', 'remaining', 'produced', 'filter', 'start', 'end'
    ));
}
}