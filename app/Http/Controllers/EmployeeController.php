<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\SalaryType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees with type counts for summary cards.
     */
public function index(Request $request)
{
    // ==================== EMPLOYEE QUERY ====================
    $query = Employee::with(['employeeType', 'salaryType']);

    // Filter: search by name, email, phone, or ID number
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('id_number', 'like', "%{$search}%");
        });
    }

    // Filter: by employee type
    if ($request->filled('employee_type_id')) {
        $query->where('employee_type_id', $request->employee_type_id);
    }

    // Filter: by status
    if ($request->filled('status')) {
        $status = $request->status === 'active' ? 1 : 0;
        $query->where('active', $status);
    }

    // Load all employees (client-side DataTables)
  $employees = $query->latest()->paginate(10); // 10 per page, can adjust


    // ==================== SUMMARY DATA ====================
    // Employee type counts for cards
    $employeeTypeCounts = Employee::selectRaw('employee_type_id, COUNT(*) as total')
        ->groupBy('employee_type_id')
        ->with('employeeType')
        ->get();

    // Total employees for summary card
    $totalEmployees = $employees->count();

    // ==================== PASS DATA TO VIEW ====================
    return view('employees.index', compact('employees', 'employeeTypeCounts', 'totalEmployees'));
}



    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $employeeTypes = EmployeeType::all();
        $salaryTypes = SalaryType::all();

        return view('employees.create', compact('employeeTypes', 'salaryTypes'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'id_number'         => 'nullable|string|size:16|unique:employees,id_number',
            'email'             => 'required|email|max:255|unique:employees,email',
            'phone'             => 'nullable|string|regex:/^07[2389]\d{7}$/|size:10|unique:employees,phone',
            'dob'               => 'required|date|before:today',
            'gender'            => 'required|in:Male,Female',
            'avatar'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'            => 'sometimes|in:active,inactive',
            'employee_type_id'  => 'required|exists:employee_types,id',
            'salary_type_id'    => 'required|exists:salary_types,id',
            'country'           => 'required|string|max:100',
            'province'          => 'required|string',
            'district'          => 'required|string',
            'sector'            => 'required|string',
            'cell'              => 'required|string',
            'village'           => 'required|string',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Default status
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['active'] = $validated['status'] === 'active';

        // Default hire date
        $validated['hire_date'] = $validated['hire_date'] ?? now();

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully!');
    }

    /**
     * Show the form for editing an employee.
     */
    public function edit(Employee $employee)
    {
        $employeeTypes = EmployeeType::all();
        $salaryTypes = SalaryType::all();

        return view('employees.edit', compact('employee', 'employeeTypes', 'salaryTypes'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'id_number'         => 'nullable|string|size:16|unique:employees,id_number,' . $employee->id,
            'email'             => 'required|email|max:255|unique:employees,email,' . $employee->id,
            'phone'             => 'nullable|string|regex:/^07[2389]\d{7}$/|size:10|unique:employees,phone,' . $employee->id,
            'dob'               => 'required|date|before:today',
            'gender'            => 'required|in:Male,Female',
            'avatar'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'            => 'required|in:active,inactive',
            'employee_type_id'  => 'required|exists:employee_types,id',
            'salary_type_id'    => 'required|exists:salary_types,id',
            'country'           => 'required|string|max:100',
            'province'          => 'required|string',
            'district'          => 'required|string',
            'sector'            => 'required|string',
            'cell'              => 'required|string',
            'village'           => 'required|string',
        ]);

        // Handle avatar upload and delete old
        if ($request->hasFile('avatar')) {
            if ($employee->avatar && Storage::disk('public')->exists($employee->avatar)) {
                Storage::disk('public')->delete($employee->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $validated['active'] = $validated['status'] === 'active';

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
    }

    /**
     * Deactivate an employee.
     */
    public function destroy(Employee $employee)
    {
        $employee->update(['active' => false]);

        return redirect()->route('employees.index')->with('success', 'Employee deactivated successfully.');
    }

    /**
     * Show details of a single employee including age.
     */
    public function show(Employee $employee)
    {
        $age = $employee->dob ? Carbon::parse($employee->dob)->age : null;

        return view('employees.show', compact('employee', 'age'));
    }
}
