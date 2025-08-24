<?php

namespace App\Http\Controllers;

use App\Models\EmployeeType;
use App\Models\SalaryType;
use App\Models\TransportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;

class CategoryManagementController extends Controller
{
    /**
     * Display all categories with statistics
     */
    public function index()
    {
        // Cache frequently accessed data for better performance
        $employeeTypes = Cache::remember('employee-types', now()->addDay(), function () {
            return EmployeeType::orderBy('name')->get();
        });

        $salaryTypes = Cache::remember('salary-types', now()->addDay(), function () {
            return SalaryType::orderBy('name')->get();
        });

        $transportCategories = Cache::remember('transport-categories', now()->addDay(), function () {
            return TransportCategory::orderBy('name')->get();
        });

        return view('categories.index', [
            'employeeTypes' => $employeeTypes,
            'salaryTypes' => $salaryTypes,
            'transportCategories' => $transportCategories,
            'totalEmployeeTypes' => $employeeTypes->count(),
            'totalSalaryTypes' => $salaryTypes->count(),
            'totalTransportCategories' => $transportCategories->count(),
        ]);
    }

    // ======================
    // EMPLOYEE TYPE METHODS
    // ======================

    /**
     * Show the form for creating a new employee type
     */
    public function createEmployeeType()
    {
        return view('categories.employee_types.create');
    }

    /**
     * Store a new employee type
     */
    public function storeEmployeeType(Request $request)
    {
        $validated = $this->validateEmployeeTypeRequest($request);
        
        EmployeeType::create($validated);
        
        $this->clearCategoryCache('employee-types');
        
        return Redirect::route('categories.index')
            ->with('success', 'Employee type created successfully');
    }

    /**
     * Show the form for editing an employee type
     */
    public function editEmployeeType(EmployeeType $employeeType)
    {
        return view('categories.employee_types.edit', compact('employeeType'));
    }

    /**
     * Update an employee type
     */
    public function updateEmployeeType(Request $request, EmployeeType $employeeType)
    {
        $validated = $this->validateEmployeeTypeRequest($request, $employeeType->id);
        
        $employeeType->update($validated);
        
        $this->clearCategoryCache('employee-types');
        
        return Redirect::route('categories.index')
            ->with('success', 'Employee type updated successfully');
    }

    /**
     * Delete an employee type
     */
    public function destroyEmployeeType(EmployeeType $employeeType)
    {
        $employeeType->delete();
        
        $this->clearCategoryCache('employee-types');
        
        return Redirect::route('categories.index')
            ->with('success', 'Employee type deleted successfully');
    }

    // ======================
    // SALARY TYPE METHODS
    // ======================

    /**
     * 
     * 
     * 
     * 
     * Show the form for creating a new salary type
     */
    public function createSalaryType()
    {
        return view('categories.salary_types.create');
    }

    /**
     * Store a new salary type
     */
    public function storeSalaryType(Request $request)
    {
        $validated = $this->validateSalaryTypeRequest($request);
        
        SalaryType::create($validated);
        
        $this->clearCategoryCache('salary-types');
        
        return Redirect::route('categories.index')
            ->with('success', 'Salary type created successfully');
    }

    /**
     * Show the form for editing a salary type
     */
    public function editSalaryType(SalaryType $salaryType)
    {
        return view('categories.salary_types.edit', compact('salaryType'));
    }

    /**
     * Update a salary type
     */
    public function updateSalaryType(Request $request, SalaryType $salaryType)
    {
        $validated = $this->validateSalaryTypeRequest($request, $salaryType->id);
        
        $salaryType->update($validated);
        
        $this->clearCategoryCache('salary-types');
        
        return Redirect::route('categories.index')
            ->with('success', 'Salary type updated successfully');
    }

    /**
     * Delete a salary type
     */
    public function destroySalaryType(SalaryType $salaryType)
    {
        $salaryType->delete();
        
        $this->clearCategoryCache('salary-types');
        
        return Redirect::route('categories.index')
            ->with('success', 'Salary type deleted successfully');
    }

    // ======================
    // TRANSPORT CATEGORY METHODS
    // ======================

    /**
     * Show the form for creating a new transport category
     */
    public function createTransportCategory()
    {
        return view('categories.transport_categories.create');
    }

    /**
     * Store a new transport category
     */
    public function transportCategoriesStore (Request $request)
    {
        $validated = $this->validateTransportCategoryRequest($request);
        
        TransportCategory::create($validated);
        
        $this->clearCategoryCache('transport-categories');
        
        return Redirect::route('categories.index')
            ->with('success', 'Transport category created successfully');
    }

    /**
     * Show the form for editing a transport category
     */
    public function editTransportCategory(TransportCategory $transportCategory)
    {
        return view('categories.transport_categories.edit', compact('transportCategory'));
    }

    /**
     * Update a transport category
     */
    public function transportCategoriesUpdate(Request $request, TransportCategory $transportCategory)
    {
        $validated = $this->validateTransportCategoryRequest($request, $transportCategory->id);
        
        $transportCategory->update($validated);
        
        $this->clearCategoryCache('transport-categories');
        
        return Redirect::route('categories.index')
            ->with('success', 'Transport category updated successfully');
    }

    /**
     * Delete a transport category
     */
    public function transportCategoriesDestroy(TransportCategory $transportCategory)
    {
        $transportCategory->delete();
        
        $this->clearCategoryCache('transport-categories');
        
        return Redirect::route('categories.index')
            ->with('success', 'Transport category deleted successfully');
    }

    // ======================
    // PRIVATE HELPER METHODS
    // ======================

    /**
     * Validate employee type request
     */
    private function validateEmployeeTypeRequest(Request $request, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:employee_types,name'
        ];

        if ($id) {
            $rules['name'] .= ',' . $id;
        }

        return $request->validate($rules);
    }

    /**
     * Validate salary type request
     */
    private function validateSalaryTypeRequest(Request $request, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:salary_types,name'
        ];

        if ($id) {
            $rules['name'] .= ',' . $id;
        }

        return $request->validate($rules);
    }

    /**
     * Validate transport category request
     */
    private function validateTransportCategoryRequest(Request $request, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:transport_categories,name',
            'unit_price' => 'required|numeric|min:0'
        ];

        if ($id) {
            $rules['name'] .= ',' . $id;
        }

        return $request->validate($rules);
    }

    /**
     * Clear cached categories
     */
    private function clearCategoryCache($cacheKey)
    {
        Cache::forget($cacheKey);
    }
}