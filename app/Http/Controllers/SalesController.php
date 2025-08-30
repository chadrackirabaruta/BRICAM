<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\StockType;
use App\Models\BrickStock;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use App\Models\BrickStockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\SaleReceipt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityHelper;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
   use Barryvdh\Snappy\Facades\SnappyPdf;

use Mpdf\Config\FontVariables;



class SalesController extends Controller
{
    /**
     * Display a paginated list of sales with filters
     */

    /**
     * Display a listing of sales.
     */
public function index(Request $request)
{
    // Load sales with customer, user (as employee), and stock type
    $query = Sales::with(['customer', 'user', 'stockType'])
        ->latest('sale_date');

    // Apply filters
    $this->applyFilters($query, $request);

    $sales = $query->paginate(25)->withQueryString();

    return view('sales.index', [
        'sales'     => $sales,
        'customers' => Customer::orderBy('name')->get(),
        'employees' => \App\Models\User::orderBy('name')->get(), // users instead of employees
        'filters'   => $request->all(),
    ]);
}



    /**
     * Show the form for creating a new sale.
     */
public function create()
{
    try {
        // Get the latest stock type ready for sale
        $stockType = StockType::orderByDesc('flow_stage')->first();

        if (!$stockType) {
            return back()->with('error', 'No stock types available for sale.');
        }

        // Get available stock for this type or initialize to 0
        $availableStock = BrickStock::firstOrCreate(
            ['stock_type_id' => $stockType->id],
            ['quantity' => 0]
        );

        // Optional debug (commented out in production)
        // Log::debug('Active customers: ' . Customer::where('status', 'active')->count());
        // Log::debug('Active employees/users: ' . \App\Models\User::count());

        // Prepare data for the create sale form
        return view('sales.create', [
            'stockType'      => $stockType,
            'availableStock' => $availableStock->quantity,
            'customers'      => Customer::where('status', 'active')->orderBy('name')->get(),
            'paymentMethods' => $this->getPaymentMethods(),
            // Employee/user selection removed; employee_id will always be logged-in user
        ]);

    } catch (\Exception $e) {
        Log::error('Error loading sales create form: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Failed to load sales form.');
    }
}


    /**
     * Store a newly created sale.
     */
public function store(Request $request)
{
    try {
        // Validate request & ensure quantity does not exceed stock
        $validated = $this->validateSaleRequest($request);

        // Set employee_id to logged-in user
        $validated['employee_id'] = Auth::id();

        // Get latest stock type ready for sale
        $stockType = StockType::orderByDesc('flow_stage')->first();

        if (!$stockType) {
            return back()
                ->withInput()
                ->withErrors(['stock' => 'No stock type available for sale.']);
        }

        $sale = DB::transaction(function () use ($validated, $stockType) {

            // Check stock availability
            $this->checkStockAvailability($stockType, $validated['quantity']);

            // Create the sale record
            $sale = $this->createSaleRecord($validated, $stockType);

            // Update stock & log the decrease
            $this->updateStock(
                $stockType,
                $validated['quantity'],
                $sale->id,
                $validated['employee_id']
            );

            return $sale;
        });

        return redirect()
            ->route('sales.receipt', $sale->id)
            ->with('success', 'Sale completed successfully!');

    } catch (\Throwable $e) {
        Log::error('Sale failed: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);

        return back()
            ->withInput()
            ->withErrors([
                'debug' => $e->getMessage(),
            ]);
    }
}



    /**
     * Display the specified sale.
     */
    public function show(Sales $sale)
    {
        return view('sales.show', [
            'sale' => $sale->load(['customer', 'employee', 'stockType'])
        ]);
    }

    /**
     * Show the form for editing the specified sale.
     */
// Show the form for editing a sale
public function edit(Sales $sale)
{
    // Restrict edit if more than 1 hour passed since sale
    // Restrict edit if more than 5 seconds passed since sale (for testing)
   if ($sale->created_at->diffInMinutes(now()) > 30){
        return redirect()
            ->back()
            ->with('error', '⏰ Edit time expired! This sale can no longer be edited.');
}



    // Find the stock type for this sale
    $stockType = StockType::findOrFail($sale->stock_type_id);

    // Get current stock from warehouse only
    $stock = BrickStock::firstOrCreate(
        ['stock_type_id' => $sale->stock_type_id],
        ['quantity' => 0]
    );

    // Available stock user sees = current stock only
    // Backend validation will later allow (current stock + original quantity)
    $availableStock = $stock->quantity;

    return view('sales.edit', [
        'sale'           => $sale,
        'stockType'      => $stockType,
        'availableStock' => $availableStock,
        'customers'      => Customer::orderBy('name')->get(),
        'employees'      => \App\Models\User::orderBy('name')->get(),
        'paymentMethods' => $this->getPaymentMethods(),
        'maxEditableQty' => $stock->quantity + $sale->quantity, // pass max limit for safety
    ]);
}



    protected function getStockInformation(Sales $sale)
    {
        try {
            $stockType = StockType::find($sale->stock_type_id);

            // Assuming 'current_stock' is maintained on StockType.
            $availableStock = $stockType
                ? ($stockType->current_stock + $sale->quantity)
                : 0;

            return [
                'type'      => $stockType,
                'available' => $availableStock
            ];
        } catch (\Exception $e) {
            return [
                'type'      => null,
                'available' => 0
            ];
        }
    }

    /**
     * Update the specified sale.
     * Note: employee_id remains as the original creator by default.
     */
public function update(Request $request, Sales $sale)
{
    $validated = $request->validate([
        'customer_id'    => 'required|exists:customers,id',
        'quantity'       => 'required|integer|min:1',
        'unit_price'     => 'required|numeric|min:0',
        'payment_method' => 'required|string|in:cash,bank,mobile,credit',
        'notes'          => 'nullable|string|max:500',
    ]);

    DB::transaction(function () use ($sale, $validated) {

        // Lock stock row to prevent race conditions
        $stock = BrickStock::where('stock_type_id', $sale->stock_type_id)
            ->lockForUpdate()
            ->firstOrFail();

        // Compute max possible quantity = current stock + original sale
        $maxQuantity = $stock->quantity + $sale->quantity;

        if ($validated['quantity'] > $maxQuantity) {
            throw ValidationException::withMessages([
                'quantity' => "Cannot sell more than {$maxQuantity} units (current stock + original sale)."
            ]);
        }

        // Calculate difference
        $quantityDifference = $validated['quantity'] - $sale->quantity;

        // Update sale
        $sale->update([
            'customer_id'    => $validated['customer_id'],
            'quantity'       => $validated['quantity'],
            'unit_price'     => $validated['unit_price'],
            'total_price'    => $validated['quantity'] * $validated['unit_price'],
            'payment_method' => $validated['payment_method'],
            'notes'          => $validated['notes'] ?? null,
        ]);

        // Adjust stock safely
        if ($quantityDifference !== 0) {
            if ($quantityDifference > 0) {
                $stock->decrement('quantity', $quantityDifference);
                $action = 'decrease';
            } else {
                $stock->increment('quantity', abs($quantityDifference));
                $action = 'increase';
            }

            // Log stock movement
         BrickStockLog::create([
    'employee_id'   => $this->resolveEmployeeId(),
    'stock_type_id' => $sale->stock_type_id,
    'action'        => $action,
    'quantity'      => abs($quantityDifference),
    'stock_date'    => now()->toDateString(),
    'reference'     => $sale->id,
    'remarks'       => 'Sale quantity updated',
]);

        }
    });

    return redirect()
        ->route('sales.receipt', $sale)
        ->with('success', 'Sale updated successfully!');
}



private function getLastStockQuantity(): int
{
    $stockType = StockType::orderByDesc('flow_stage')->first();

    if (!$stockType) {
        return 0; // no stock types defined yet
    }

    $stock = BrickStock::firstOrCreate(
        ['stock_type_id' => $stockType->id],
        ['quantity' => 0]
    );

    return $stock->quantity;
}







    /**
     * Remove the specified sale.
     */
 public function destroy(Sales $sale)
{
    // Restrict delete if more than 1 hour passed since sale
    // (use diffInSeconds(now()) > 5 for testing)
    if ($sale->created_at->diffInMinutes(now()) > 30) {
        return redirect()
            ->back()
            ->with('error', '⏰ Return time expired! This sale can no longer be deleted.');
    }

    try {
        DB::transaction(function () use ($sale) {
            $this->restoreStock($sale);
            $sale->delete();
        });

        return redirect()
            ->route('sales.index')
            ->with('success', '🗑️ Sale Returned successfully!');
    } catch (\Throwable $e) {
        Log::error('Sales destroy error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()
            ->back()
            ->with('error', '❌ Failed to delete sale.');
    }
}


    /**
     * Generate a receipt for the sale.
     */
    public function receipt(Sales $sale)
    {
        return view('sales.receipt', [
            'sale' => $sale->load(['customer', 'employee', 'stockType'])
        ]);
    }

    // ============ PRIVATE METHODS ============ //

    private function applyFilters($query, Request $request)
    {
        return $query
            ->when($request->date, fn($q, $date) => $q->whereDate('sale_date', $date))
            ->when($request->customer_id, fn($q, $id) => $q->where('customer_id', $id))
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id));
    }
protected function validateSaleRequest(Request $request, Sales $sale = null)
{
    // Current stock for last stock type
    $currentStock = $sale
        ? BrickStock::firstWhere('stock_type_id', $sale->stock_type_id)?->quantity ?? 0
        : $this->getLastStockQuantity();

    // Allow original sale quantity + available stock
    $availableStock = $sale ? ($currentStock + $sale->quantity) : $currentStock;

    return $request->validate([
        'customer_id'    => 'required|exists:customers,id',
        'quantity'       => "required|integer|min:1|max:{$availableStock}",
        'unit_price'     => 'required|numeric|min:0',
        'payment_method' => 'required|string|in:cash,credit,bank,mobile',
        'notes'          => 'nullable|string|max:500',
    ], [
        'quantity.max' => "Cannot exceed available stock ({$availableStock}).",
    ]);
}









private function checkStockAvailability(StockType $stockType, int $requiredQuantity, int $saleId = null)
{
    // Ensure the stock record exists
    $stock = BrickStock::firstOrCreate(
        ['stock_type_id' => $stockType->id],
        ['quantity' => 0]
    );

    // Check if requested quantity exceeds available stock
    if ($requiredQuantity > $stock->quantity) {
        $referenceInfo = $saleId ? " (Sale ID: {$saleId})" : "";
        throw ValidationException::withMessages([
            'quantity' => "Insufficient stock{$referenceInfo}. Available: {$stock->quantity}, Requested: {$requiredQuantity}."
        ]);
    }
}

private function createSaleRecord(array $data, StockType $stockType): Sales
{
    $sale = Sales::create([
        'customer_id'    => $data['customer_id'],
        'employee_id'    => $data['employee_id'], // logged-in user
        'stock_type_id'  => $stockType->id,
        'sale_date'      => now()->toDateString(),
        'quantity'       => max(1, (int) $data['quantity']),
        'unit_price'     => $data['unit_price'],
        'total_price'    => $data['quantity'] * $data['unit_price'],
        'payment_method' => $data['payment_method'],
        'notes'          => $data['notes'] ?? null,
        'reference_number' => Sales::generateReferenceNumber(), // ensures unique reference
    ]);

    // Optional: log creation for debugging
    Log::debug("Sale created: ID {$sale->id}, Customer {$sale->customer_id}, Employee {$sale->employee_id}");

    return $sale;
}


/**
 * Update an existing sale record without changing the original employee.
 */
private function updateSaleRecord(Sales $sale, array $data)
{
    $sale->update([
        'customer_id'    => $data['customer_id'],
        'quantity'       => $data['quantity'],
        'unit_price'     => $data['unit_price'],
        'total_price'    => $data['quantity'] * $data['unit_price'],
        'payment_method' => $data['payment_method'],
        'notes'          => $data['notes'] ?? null,
    ]);

    // Optional: Log sale update
    Log::info("Sale #{$sale->id} updated: quantity={$data['quantity']}, unit_price={$data['unit_price']}");
}

/**
 * Deduct stock when a new sale is created.
 * Ensures stock cannot go below zero and logs the transaction.
 */
private function updateStock(StockType $stockType, int $quantity, int $saleId, ?int $employeeId)
{
    $stock = BrickStock::firstOrCreate(
        ['stock_type_id' => $stockType->id],
        ['quantity' => 0]
    );

    if ($quantity > $stock->quantity) {
        throw new \Exception("Insufficient stock. Available: {$stock->quantity}, Requested: {$quantity}");
    }

    $stock->decrement('quantity', $quantity);

    BrickStockLog::create([
        'employee_id'   => $employeeId,
        'stock_type_id' => $stockType->id,
        'action'        => 'decrease',
        'quantity'      => $quantity,
        'stock_date'    => now()->toDateString(),
        'reference'     => $saleId,
        'remarks'       => 'Sold to customer',
    ]);

    $stockType->refresh();
}




private function adjustStock(StockType $stockType, int $quantityDifference, int $saleId)
{
    if ($quantityDifference === 0) return;

    $stock = BrickStock::firstOrCreate(
        ['stock_type_id' => $stockType->id],
        ['quantity' => 0]
    );

    // Prevent decreasing stock below zero
    if ($quantityDifference > 0 && $quantityDifference > $stock->quantity) {
        throw ValidationException::withMessages([
            'quantity' => "Cannot increase sale by {$quantityDifference} units. Only {$stock->quantity} units available."
        ]);
    }

    if ($quantityDifference > 0) {
        $stock->decrement('quantity', $quantityDifference);
        $action = 'decrease';
    } else {
        $stock->increment('quantity', abs($quantityDifference));
        $action = 'increase';
    }

    BrickStockLog::create([
        'employee_id'   => $this->resolveEmployeeId(),
        'stock_type_id' => $stockType->id,
        'action'        => $action,
        'quantity'      => abs($quantityDifference),
        'stock_date'    => now()->toDateString(),
        'reference'     => $saleId,
        'remarks'       => 'Sale quantity adjustment',
    ]);

    $stockType->refresh();
}



    private function restoreStock(Sales $sale)
    {
        $stock = BrickStock::firstOrCreate(
            ['stock_type_id' => $sale->stock_type_id],
            ['quantity' => 0]
        );

        // Increase quantity back
        $stock->increment('quantity', $sale->quantity);

        $employeeId = $sale->employee_id ?? $this->resolveEmployeeId();

        BrickStockLog::create([
            'employee_id'   => $employeeId,
            'stock_type_id' => $sale->stock_type_id,
            'action'        => 'correction', // not 'increase'
            'quantity'      => $sale->quantity,
            'stock_date'    => now()->toDateString(),
            'reference'     => $sale->id,
            'remarks'       => 'Sale deleted, stock reverted',
        ]);
    }

    /**
     * Resolve the current app "employee id" for the logged-in user.
     * - If you have an employees table linked by user_id, it will use that.
     * - Otherwise, it falls back to Auth::id().
     */
private function resolveEmployeeId(): ?int
{
    return Auth::check() ? Auth::id() : null;
}

    /**
     * Payment methods list used in validation and forms.
     * Adjust as needed.
     */
    private function getPaymentMethods(): array
    {
        return ['cash', 'bank', 'mobile', 'credit'];
    }


  

    // app/Models/StockType.php
public function scopeReadyForSale($query)
{
    return $query->orderByDesc('flow_stage');
}

//email

public function showEmailForm(Sales $sale)
{
    return view('sales.email-form', [
        'sale' => $sale,
        'defaultEmail' => $sale->customer->email,
        'defaultSubject' => 'Your Receipt #' . $sale->reference_number
    ]);
}

public function sendReceiptEmail(Request $request, Sales $sale)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'subject' => 'required|string|max:100',
        'message' => 'nullable|string|max:500'
    ]);

    try {
        Mail::to($validated['email'])
            ->send(new SaleReceipt($sale, $validated['subject'], $validated['message']));
            
        return redirect()->route('sales.receipt', $sale)
            ->with('success', 'Receipt has been emailed successfully!');
    } catch (\Exception $e) {
        return back()->withInput()
            ->with('error', 'Failed to send email: ' . $e->getMessage());
    }
}
public function emailReceipt(Request $request, Sales $sale)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'subject' => 'required|string|max:100',
        'message' => 'required|string'
    ]);

    try {
        Mail::to($validated['email'])
            ->send(new SaleReceipt($sale, $validated['subject'], $validated['message']));

        return back()
            ->with('success', 'Receipt has been emailed successfully!');
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Failed to send email: ' . $e->getMessage());
    }}

// Optional quick-send method
public function quickSendEmail(Sales $sale)
{
    try {
        Mail::to($sale->customer->email)
            ->send(new SaleReceipt(
                $sale,
                'Your Receipt #' . $sale->reference_number
            ));
            
        return back()->with('success', 'Receipt emailed to customer!');
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to send email: ' . $e->getMessage());
    }
}

//sales pdf
public function downloadPdf(Sales $sale)
{

    $snappy = app('snappy.pdf');
    $html = '<h1>Test PDF</h1>';
    
    return response(
        $snappy->getOutputFromHtml($html),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="test.pdf"',
        ]
    );

    
}
//report
public function report(Request $request)
{
    $query = Sales::with(['customer', 'employee', 'stockType'])->latest('sale_date');
    $this->applySalesFilters($query, $request);

    $sales = $query->get();

    return view('reports.sales-report', [
        'sales' => $sales,
        'summary' => [
            'total_quantity' => $sales->sum('quantity'),
            'total_sales'    => $sales->sum('total_price')
        ],
        'customers'  => Customer::orderBy('name')->get(),
        'employees'  => Employee::orderBy('name')->get(),
        'filters'    => $request->all()
    ]);
}

//filter
/**
 * Apply advanced filters to the sales report query.
 */
private function applySalesFilters($query, Request $request)
{
    return $query
        ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
            $q->whereBetween('sale_date', [$request->start_date, $request->end_date]);
        })
        ->when($request->filled('customer_id'), fn($q) => $q->where('customer_id', $request->customer_id))
        ->when($request->filled('employee_id'), fn($q) => $q->where('employee_id', $request->employee_id))
        ->when($request->filled('stock_type_id'), fn($q) => $q->where('stock_type_id', $request->stock_type_id))
        ->when($request->filled('payment_method'), fn($q) => $q->where('payment_method', $request->payment_method))
        ->when($request->filled('min_amount'), fn($q) => $q->where('total_price', '>=', $request->min_amount))
        ->when($request->filled('max_amount'), fn($q) => $q->where('total_price', '<=', $request->max_amount));
}

public function reportPdf(Request $request)
{
    $query = Sales::with(['customer', 'employee', 'stockType'])->latest('sale_date');
    $this->applySalesFilters($query, $request);

    $sales = $query->get();

    $summary = [
        'total_quantity' => $sales->sum('quantity'),
        'total_sales' => $sales->sum('total_price'),
    ];

    return view('sales.sales-print', compact('sales', 'summary'));
}

 

public function pdf($id)
{
    $sale = Sales::with(['customer', 'employee', 'stockType'])->findOrFail($id);

    $pdf = SnappyPdf::loadView('sales.receipt', compact('sale'))
        ->setPaper('a4')
        ->setOption('margin-top', 10)
        ->setOption('margin-bottom', 10);

    return $pdf->download('receipt_' . $sale->reference_number . '.pdf');
}




}


