<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('name')->paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:16|unique:customers',
            'phone' => 'required|string|max:15|unique:customers',
            'email' => 'nullable|email|unique:customers',
            'country' => 'required|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'avatar' => 'nullable|image|max:2048',
            'customer_type' => 'required|in:Retail,Wholesale,Contractor',
            'province' => 'required|string',
            'district' => 'required|string',
            'sector' => 'required|string',
            'cell' => 'required|string',
            'village' => 'required|string',
            'address' => 'nullable|string',
        ]);

    if ($request->hasFile('avatar')) {
        $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
    }

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully!');
    }
    public function edit(Customer $customer)
{
    return view('customers.edit', compact('customer'));
}

public function update(Request $request, Customer $customer)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'id_number' => 'nullable|string|max:16|unique:customers,id_number,' . $customer->id,
        'phone' => 'required|string|max:15|unique:customers,phone,' . $customer->id,
        'email' => 'nullable|email|unique:customers,email,' . $customer->id,
        'country' => 'required|string|max:100',
        'dob' => 'nullable|date|before:today',
        'gender' => 'required|in:Male,Female,Other',
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'customer_type' => 'required|in:'.implode(',', array_keys(Customer::getCustomerTypeOptions())),
        'status' => 'required|in:'.implode(',', array_keys(Customer::getStatusOptions())),
        'province' => 'required|string|max:100',
        'district' => 'required|string|max:100',
        'sector' => 'required|string|max:100',
        'cell' => 'required|string|max:100',
        'village' => 'required|string|max:100',
        'address' => 'nullable|string|max:255',
        'loyalty_points' => 'nullable|integer|min:0',
        'credit_limit' => 'nullable|numeric|min:0'
    ]);

    if ($request->hasFile('avatar')) {
        // Delete old avatar if exists
        if ($customer->avatar) {
            Storage::disk('public')->delete($customer->avatar);
        }
        $validated['avatar'] = $request->file('avatar')->store('customer_avatars', 'public');
    }

    // Update customer with validated data
    $customer->update($validated);

    // Add status change logging if needed


    return redirect()->route('customers.index')
        ->with('success', 'Customer updated successfully!');
}

public function destroy(Customer $customer)
{
    // Check if customer is already inactive
    if ($customer->status === 'inactive') {
        return redirect()->route('customers.index')
            ->with('warning', 'Customer is already inactive!');
    }

    // Update status to inactive
    $customer->update(['status' => 'inactive']);


    return redirect()->route('customers.index')
        ->with('success', 'Customer deactivated successfully!');
}


public function show(Customer $customer)
{
    // Calculate age from date of birth if available
    $age = $customer->dob ? Carbon::parse($customer->dob)->age : null;

    // Customer type display configuration
    $customerTypeConfig = [
        'Retail' => [
            'badge' => 'bg-info',
            'icon' => 'bi-cart',
            'label' => 'Retail Customer'
        ],
        'Wholesale' => [
            'badge' => 'bg-success',
            'icon' => 'bi-box-seam',
            'label' => 'Wholesale Buyer'
        ],
        'Contractor' => [
            'badge' => 'bg-warning text-dark',
            'icon' => 'bi-tools',
            'label' => 'Contractor'
        ]
    ];

    // Format address components
    $addressComponents = array_filter([
        $customer->address,
        $customer->village,
        $customer->cell,
        $customer->sector,
        $customer->district,
        $customer->province,
        $customer->country
    ]);

    // Prepare timeline data (example - customize as needed)
    $timeline = [
        [
            'date' => $customer->created_at->format('M Y'),
            'event' => 'Customer Registered',
            'icon' => 'bi-person-plus',
            'color' => 'primary'
        ],
        [
            'date' => $customer->updated_at->format('M Y'),
            'event' => 'Last Profile Update',
            'icon' => 'bi-pencil',
            'color' => 'secondary'
        ]
    ];

    return view('customers.show', [
        'customer' => $customer,
        'age' => $age,
        'customerTypeConfig' => $customerTypeConfig,
        'addressLine' => implode(', ', $addressComponents),
        'timeline' => $timeline,
        'hasContact' => !empty($customer->email) || !empty($customer->phone)
    ]);
}

}

