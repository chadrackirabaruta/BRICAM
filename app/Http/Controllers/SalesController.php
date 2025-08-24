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
    public function index(Request $request)
    {
        $query = Sales::with(['customer', 'employee', 'stockType'])
            ->latest('sale_date');

        // Apply filters
        $this->applyFilters($query, $request);
        
        $sales = $query->paginate(25)->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'customers' => Customer::orderBy('name')->get(),
            'employees' => Employee::orderBy('name')->get(),
            'filters' => $request->all()
        ]);
    }

    /**
     * Show the form for creating a new sale
     */
public function create()
    {
        try {
            $lastStageType = StockType::orderByDesc('flow_stage')->first();

            if (!$lastStageType) {
                return back()->with('error', 'No stock types available for sale');
            }

            $availableStock = BrickStock::firstOrCreate(
                ['stock_type_id' => $lastStageType->id],
                ['quantity' => 0]
            );

            // Debugging: Check if data exists
            Log::debug('Customer count: ' . Customer::where('status', 'active')->count());
            Log::debug('Employee count: ' . Employee::where('active', 1)->count());

            return view('sales.create', [
                'stockType' => $lastStageType,
                'availableStock' => $availableStock->quantity,
                'customers' => Customer::where('status', 'active')->orderBy('name')->get(),
                'employees' => Employee::where('active', 1)->orderBy('name')->get(),
                'paymentMethods' => $this->getPaymentMethods()
            ]);

        } catch (\Exception $e) {
            Log::error('Sales create error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load sales form');
        }}



    /**
     * Store a newly created sale
     */
    public function store(Request $request)
    {
      $validated = $this->validateSaleRequest($request);
    $lastStageType = StockType::orderByDesc('flow_stage')->firstOrFail();

        return DB::transaction(function () use ($validated, $lastStageType) {
            $this->checkStockAvailability($lastStageType, $validated['quantity']);
            
            $sale = $this->createSaleRecord($validated, $lastStageType);
            $this->updateStock($lastStageType, $validated, $sale->id);

           

            return redirect()
                ->route('sales.receipt', $sale)
                ->with('success', 'Sale completed successfully!');


        });
    }

    /**
     * Display the specified sale
     */
    public function show(Sales $sale)
    {
        return view('sales.show', [
            'sale' => $sale->load(['customer', 'employee', 'stockType'])
        ]);
    }

    /**
     * Show the form for editing the specified sale
     */
public function edit(Sales $sale)
{
    // Authorization check - ensure user can edit this sale
    // $this->authorize('update', $sale); // Uncomment if using policies

    // Get current stock information
    $stockInfo = $this->getStockInformation($sale);

    return view('sales.edit', [
        'sale' => $sale,
        'customers' => Customer::orderBy('name')->get(), // Removed ->active()
        'employees' => Employee::orderBy('name')->get(),  // Removed ->salesStaff()
        'paymentMethods' => $this->getPaymentMethods(),
        'availableStock' => $stockInfo['available'],
        'stockType' => $stockInfo['type'],
        'originalQuantity' => $sale->quantity,
        'originalUnitPrice' => $sale->unit_price,
    ]);
}
protected function getStockInformation(Sales $sale)
{
    try {
        $stockType = StockType::find($sale->stock_type_id);
        
        $availableStock = $stockType 
            ? ($stockType->current_stock + $sale->quantity) 
            : 0;

        return [
            'type' => $stockType,
            'available' => $availableStock
        ];
    } catch (\Exception $e) {
        return [
            'type' => null,
            'available' => 0
        ];
    }
}




    /**
     * Update the specified sale
     */
    public function update(Request $request, Sales $sale)
    {
        $validated = $this->validateSaleRequest($request);

        return DB::transaction(function () use ($validated, $sale) {
            $originalQuantity = $sale->quantity;
            $quantityDifference = $validated['quantity'] - $originalQuantity;

            if ($quantityDifference > 0) {
                $this->checkStockAvailability($sale->stockType, $quantityDifference);
            }

            $this->updateSaleRecord($sale, $validated);
            $this->adjustStock($sale->stockType, $quantityDifference, $sale->id);

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Sale updated successfully!');
        });
    }

    /**
     * Remove the specified sale
     */
    public function destroy(Sales $sale)
    {
        DB::transaction(function () use ($sale) {
            $this->restoreStock($sale);
            $sale->delete();
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale deleted successfully!');
    }

    /**
     * Generate a receipt for the sale
     */
    public function receipt(Sales $sale)
    {
        return view('sales.receipt', [
            'sale' => $sale->load(['customer', 'employee', 'stockType'])
        ]);
    }

    // ============ PRIVATE METHODS ============ //

    private function applyFilters($query, $request)
    {
        return $query->when($request->date, fn($q, $date) => $q->whereDate('sale_date', $date))
            ->when($request->customer_id, fn($q, $id) => $q->where('customer_id', $id))
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id));
    }

    private function validateSaleRequest(Request $request)
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'employee_id' => 'required|exists:employees,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:' . implode(',', $this->getPaymentMethods()),
            'notes' => 'nullable|string|max:500',
        ]);
    }

    private function checkStockAvailability(StockType $stockType, int $quantity)
    {
        $stock = BrickStock::firstOrCreate(
            ['stock_type_id' => $stockType->id],
            ['quantity' => 0]
        );

        if ($stock->quantity < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$stock->quantity}, Requested: {$quantity}");
        }
    }

    private function createSaleRecord(array $data, StockType $stockType)
    {
        return Sales::create([
            'customer_id' => $data['customer_id'],
            'employee_id' => $data['employee_id'],
            'stock_type_id' => $stockType->id,
            'sale_date' => now()->toDateString(),
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'total_price' => $data['quantity'] * $data['unit_price'],
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function updateSaleRecord(Sales $sale, array $data)
    {
        $sale->update([
            'customer_id' => $data['customer_id'],
            'employee_id' => $data['employee_id'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'total_price' => $data['quantity'] * $data['unit_price'],
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function updateStock(StockType $stockType, array $data, int $saleId)
    {
        $stock = BrickStock::where('stock_type_id', $stockType->id)->first();
        $stock->decrement('quantity', $data['quantity']);

        BrickStockLog::create([
            'employee_id' => $data['employee_id'],
            'stock_type_id' => $stockType->id,
            'action' => 'decrease',
            'quantity' => $data['quantity'],
            'stock_date' => now()->toDateString(),
            'reference' => $saleId,
            'remarks' => 'Sold to customer'
        ]);
    }
private function adjustStock(StockType $stockType, int $quantityDifference, int $saleId)
{
    if ($quantityDifference !== 0) {
        $stock = BrickStock::where('stock_type_id', $stockType->id)->first();

        if ($stock) {
            $stock->decrement('quantity', $quantityDifference);
        }

        $employeeId = Auth::check() ? Auth::id() : null;

        BrickStockLog::create([
            'employee_id'   => $employeeId,
            'stock_type_id' => $stockType->id,
            'action'        => $quantityDifference > 0 ? 'decrease' : 'increase',
            'quantity'      => abs($quantityDifference),
            'stock_date'    => now()->toDateString(),
            'reference'     => $saleId,
            'remarks'       => 'Sale quantity adjustment'
        ]);
    }
}

private function restoreStock(Sales $sale)
{
    $stock = BrickStock::where('stock_type_id', $sale->stock_type_id)->first();

    if ($stock) {
        // ✅ Increase quantity in DB only
        $stock->increment('quantity', $sale->quantity);
    }

    $employeeId = $sale->employee_id ?? (Auth::check() ? Auth::id() : null);

    BrickStockLog::create([
        'employee_id'   => $employeeId,
        'stock_type_id' => $sale->stock_type_id,
        'action'        => 'correction', // ✅ NOT 'increase'
        'quantity'      => $sale->quantity,
        'stock_date'    => now()->toDateString(),
        'reference'     => $sale->id,
        'remarks'       => 'Sale deleted, stock reverted'
    ]);
}

    private function getPaymentMethods()
    {
        return ['cash', 'credit', 'mobile_money', 'bank_transfer'];
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


