<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers with type counts for cards
     */
 public function index()
{
    $customers = Customer::orderBy('name')->get();

    // Customer type counts for summary cards
    $customerTypeCounts = Customer::selectRaw('customer_type, COUNT(*) as total')
        ->groupBy('customer_type')
        ->pluck('total', 'customer_type'); // simpler than keyBy

    // Customer status counts for summary cards
    $customerStatusCounts = Customer::selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');

    // Get unique districts for filter dropdown
    $districts = Customer::select('district')->distinct()->pluck('district');

    return view('customers.index', compact(
        'customers',
        'customerTypeCounts',
        'customerStatusCounts',
        'districts'
    ));
}


    /**
     * Show form to create a customer
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'id_number'     => 'nullable|string|max:16|unique:customers',
            'phone'         => 'required|string|max:15|unique:customers',
            'email'         => 'nullable|email|unique:customers',
            'country'       => 'required|string|max:100',
            'dob'           => 'nullable|date|before:today',
            'gender'        => 'nullable|in:Male,Female,Other',
            'avatar'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'customer_type' => 'required|in:Retail,Wholesale,Contractor',
            'status'        => 'required|in:active,inactive,banned',
            'province'      => 'required|string|max:100',
            'district'      => 'required|string|max:100',
            'sector'        => 'required|string|max:100',
            'cell'          => 'required|string|max:100',
            'village'       => 'required|string|max:100',
            'address'       => 'nullable|string|max:255',
            'loyalty_points'=> 'nullable|integer|min:0',
            'credit_limit'  => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('customer_avatars', 'public');
        }

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully!');
    }

    /**
     * Show form to edit customer
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update an existing customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'id_number'     => 'nullable|string|max:16|unique:customers,id_number,' . $customer->id,
            'phone'         => 'required|string|max:15|unique:customers,phone,' . $customer->id,
            'email'         => 'nullable|email|unique:customers,email,' . $customer->id,
            'country'       => 'required|string|max:100',
            'dob'           => 'nullable|date|before:today',
            'gender'        => 'required|in:Male,Female,Other',
            'avatar'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'customer_type' => 'required|in:Retail,Wholesale,Contractor',
            'status'        => 'required|in:active,inactive,banned',
            'province'      => 'required|string|max:100',
            'district'      => 'required|string|max:100',
            'sector'        => 'required|string|max:100',
            'cell'          => 'required|string|max:100',
            'village'       => 'required|string|max:100',
            'address'       => 'nullable|string|max:255',
            'loyalty_points'=> 'nullable|integer|min:0',
            'credit_limit'  => 'nullable|numeric|min:0',
        ]);

        // Handle avatar update
        if ($request->hasFile('avatar')) {
            if ($customer->avatar && Storage::disk('public')->exists($customer->avatar)) {
                Storage::disk('public')->delete($customer->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('customer_avatars', 'public');
        }

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Soft-delete or deactivate a customer
     */
    public function destroy(Customer $customer)
    {
        if ($customer->status === 'inactive') {
            return redirect()->route('customers.index')
                ->with('warning', 'Customer is already inactive!');
        }

        $customer->update(['status' => 'inactive']);

        return redirect()->route('customers.index')
            ->with('success', 'Customer deactivated successfully!');
    }

    /**
     * Show customer profile with additional info
     */
    public function show(Customer $customer)
    {
        $age = $customer->dob ? Carbon::parse($customer->dob)->age : null;

        $customerTypeConfig = [
            'Retail'    => ['badge' => 'bg-info', 'icon' => 'bi-cart', 'label' => 'Retail Customer'],
            'Wholesale' => ['badge' => 'bg-success', 'icon' => 'bi-box-seam', 'label' => 'Wholesale Buyer'],
            'Contractor'=> ['badge' => 'bg-warning text-dark', 'icon' => 'bi-tools', 'label' => 'Contractor'],
        ];

        $addressComponents = array_filter([
            $customer->address,
            $customer->village,
            $customer->cell,
            $customer->sector,
            $customer->district,
            $customer->province,
            $customer->country
        ]);

        $timeline = [
            [
                'date'  => $customer->created_at->format('M Y'),
                'event' => 'Customer Registered',
                'icon'  => 'bi-person-plus',
                'color' => 'primary'
            ],
            [
                'date'  => $customer->updated_at->format('M Y'),
                'event' => 'Last Profile Update',
                'icon'  => 'bi-pencil',
                'color' => 'secondary'
            ]
        ];

        return view('customers.show', [
            'customer' => $customer,
            'age' => $age,
            'customerTypeConfig' => $customerTypeConfig,
            'addressLine' => implode(', ', $addressComponents),
            'timeline' => $timeline,
            'hasContact' => !empty($customer->email) || !empty($customer->phone),
        ]);
    }

    /**
     * Optional helper: return customer type options
     */
    public static function getCustomerTypeOptions()
    {
        return ['Retail' => 'Retail', 'Wholesale' => 'Wholesale', 'Contractor' => 'Contractor'];
    }

    /**
     * Optional helper: return status options
     */
    public static function getStatusOptions()
    {
        return ['active' => 'Active', 'inactive' => 'Inactive', 'banned' => 'Banned'];
    }
}
