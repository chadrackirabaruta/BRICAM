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
    public function index()
    {
        $employees = Employee::with(['employeeType', 'salaryType'])
            ->latest()
            ->get();

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $employeeTypes = EmployeeType::all();
        $salaryTypes = SalaryType::all();

        return view('employees.create', compact('employeeTypes', 'salaryTypes'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'name'              => 'required|string|max:255',
        'id_number'         => 'nullable|string|size:16|unique:employees,id_number',
        'email'             => 'required|email|max:255|unique:employees,email',
        'phone'             => 'nullable|string|regex:/^07[2,3,8,9]\d{7}$/|size:10|unique:employees,phone',
        'dob'               => 'required|date|before:today',
        'gender'            => 'required|in:Male,Female',
        'avatar'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'status'            => 'sometimes|in:active,inactive', // Optional status field
        'employee_type_id'  => 'required|exists:employee_types,id',
        'salary_type_id'    => 'required|exists:salary_types,id',
        'country'           => 'required|string|max:100',
        'province'          => 'required|string',
        'district'          => 'required|string',
        'sector'            => 'required|string',
        'cell'              => 'required|string',
        'village'           => 'required|string',
    ], [
        'id_number.unique'  => 'The ID number is already registered.',
        'email.unique'      => 'The email address is already in use.',
        'phone.unique'      => 'The phone number already exists.',
        'status.in'         => 'Status must be either active or inactive.',
    ]);

    if ($request->hasFile('avatar')) {
        $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
    }

    // Set default status if not provided
    if (!isset($validated['status'])) {
        $validated['status'] = 'active';
    }

    // Sync status with active field
    $validated['active'] = $validated['status'] === 'active';

    // Set hire date if not provided
    if (!isset($validated['hire_date'])) {
        $validated['hire_date'] = now();
    }

    Employee::create($validated);

    return redirect()->route('employees.index')->with('success', 'Employee created successfully!');
}
    public function edit(Employee $employee)
    {
        $employeeTypes = EmployeeType::all();
        $salaryTypes = SalaryType::all();

        return view('employees.edit', compact('employee', 'employeeTypes', 'salaryTypes'));
    }
public function update(Request $request, Employee $employee)
{
    $validated = $request->validate([
        'name'              => 'required|string|max:255',
        'id_number'         => 'nullable|string|size:16|unique:employees,id_number,' . $employee->id,
        'email'             => 'required|email|max:255|unique:employees,email,' . $employee->id,
        'phone'             => 'nullable|string|regex:/^07[2,3,8,9]\d{7}$/|size:10|unique:employees,phone,' . $employee->id,
        'dob'               => 'required|date|before:today',
        'gender'            => 'required|in:Male,Female',
        'avatar'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'status'            => 'required|in:active,inactive', // Simple active/inactive status
        'employee_type_id'  => 'required|exists:employee_types,id',
        'salary_type_id'    => 'required|exists:salary_types,id',
        'country'           => 'required|string|max:100',
        'province'          => 'required|string',
        'district'          => 'required|string',
        'sector'            => 'required|string',
        'cell'              => 'required|string',
        'village'           => 'required|string',
    ], [
        'id_number.unique'  => 'This ID number already exists.',
        'email.unique'      => 'This email is already taken.',
        'phone.unique'      => 'This phone number is already assigned.',
        'status.in'         => 'Status must be either active or inactive.',
    ]);

    if ($request->hasFile('avatar')) {
        // Delete old avatar
        if ($employee->avatar && Storage::disk('public')->exists($employee->avatar)) {
            Storage::disk('public')->delete($employee->avatar);
        }

        $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
    }

    // Update both status and active fields for consistency
    $validated['active'] = $validated['status'] === 'active';

    $employee->update($validated);

    return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
}

    public function destroy(Employee $employee)
    {
        $employee->update(['active' => false]);

        return redirect()->route('employees.index')->with('success', 'Employee deactivated successfully.');
    }

    // Single view with age
    public function show(Employee $employee)
    {
        $now = Carbon::now();
        $age = $employee->dob ? $employee->dob->age : null;

        return view('employees.show', compact('employee', 'age'));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}