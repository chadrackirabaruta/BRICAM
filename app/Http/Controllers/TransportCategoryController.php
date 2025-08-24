<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransportRecord;
use App\Models\TransportCategory;
use App\Models\Employee;

class TransportRecordController extends Controller
{
    /**
     * Display a listing of the transport records.
     */
    public function index()
    {
        $records = TransportRecord::with(['employee', 'category'])->latest()->get();
        $employees = Employee::orderBy('name')->get();
        $categories = TransportCategory::orderBy('name')->get();

        return view('transport_records.index', compact('records', 'employees', 'categories'));
    }

    /**
     * Store a newly created transport record in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        // Retrieve unit price from category
        $category = TransportCategory::findOrFail($validated['transport_category_id']);
        $validated['unit_price'] = $category->unit_price;
        $validated['total_price'] = $category->unit_price * $validated['quantity'];

        // Save record
        TransportRecord::create($validated);

        // Save current employee in session (to prefill next form)
        session(['selected_employee' => $validated['employee_id']]);

        return redirect()->route('transport-records.index')->with('success', 'Transport record saved successfully.');
    }

    /**
     * Show the form for editing the specified transport record.
     */
    public function edit($id)
    {
        $record = TransportRecord::with('employee', 'category')->findOrFail($id);
        $employees = Employee::orderBy('name')->get();
        $categories = TransportCategory::orderBy('name')->get();

        return view('transport_records.edit', compact('record', 'employees', 'categories'));
    }

    /**
     * Update the specified transport record.
     */
    public function update(Request $request, $id)
    {
        $record = TransportRecord::findOrFail($id);

        $validated = $this->validateData($request);

        $category = TransportCategory::findOrFail($validated['transport_category_id']);
        $validated['unit_price'] = $category->unit_price;
        $validated['total_price'] = $category->unit_price * $validated['quantity'];

        $record->update($validated);

        return redirect()->route('transport-records.index')->with('success', 'Transport record updated successfully.');
    }

    /**
     * Remove the specified transport record.
     */
    public function destroy($id)
    {
        $record = TransportRecord::findOrFail($id);
        $record->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Transport record deleted successfully.');
    }

    /**
     * Validation logic reused in store/update
     */
    private function validateData(Request $request)
    {
        return $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'transport_category_id' => 'required|exists:transport_categories,id',
            'transport_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'destination' => 'nullable|string|max:255',
        ]);
    }
}