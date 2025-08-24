<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TransportRecord;
use App\Models\TransportCategory;
use App\Models\Employee;
use App\Models\StockType;
use App\Models\BrickStock;
use App\Models\BrickStockLog;
use App\Rules\PositiveStock;
use App\Models\Production;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\ProductionStock;


class TransportRecordController extends Controller
{
    /**
     * Display index page with production-connected records
     */
public function index(Request $request)
{
    try {
        // 📥 Inputs
        $filter      = $request->input('filter'); // today, week, month, year
        $employeeId  = $request->input('employee_filter') ?? $request->input('employee_id');
        $dateRange   = $request->input('date_range');
        $date        = $request->input('date', now()->toDateString());

        // 📅 Date filtering
        if ($dateRange && Str::contains($dateRange, ' - ')) {
            [$startRaw, $endRaw] = explode(' - ', $dateRange);
            $startDate = Carbon::parse($startRaw)->startOfDay();
            $endDate   = Carbon::parse($endRaw)->endOfDay();
        } elseif ($request->date_range === 'custom') {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);
            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $endDate   = Carbon::parse($validated['end_date'])->endOfDay();
        } else {
            switch ($filter) {
                case 'today':
                    $startDate = Carbon::now()->startOfDay();
                    $endDate   = Carbon::now()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate   = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate   = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate   = Carbon::now()->endOfYear();
                    break;
                default:
                    $startDate = Carbon::parse($date)->startOfDay();
                    $endDate   = Carbon::parse($date)->endOfDay();
                    break;
            }
        }

        // 📚 Load supporting data
        $employees   = Employee::orderBy('name')->get();
        $categories  = TransportCategory::orderBy('name')->get();
        $stockTypes  = StockType::orderBy('flow_stage')->get();

        // 🏭 Production logs
        $productions = Production::with('employee')
            ->whereBetween('production_date', [$startDate, $endDate])
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->get();

        // 📦 Brick stock logs
        $logs = BrickStockLog::whereBetween('stock_date', [$startDate, $endDate])
            ->with(['employee', 'stockType'])
            ->get()
            ->groupBy('stock_type_id');

        // 📊 Individual employee summary for specific day
        $summary = collect();
        $total = 0;
        if ($request->filled('employee_id') && $startDate->equalTo($endDate)) {
            $summary = TransportRecord::with(['category', 'employee', 'production'])
                ->where('employee_id', $request->input('employee_id'))
                ->whereDate('transport_date', $startDate->toDateString())
                ->get()
                ->groupBy('category.name')
                ->mapWithKeys(function ($group, $categoryName) use (&$total) {
                    $qty = $group->sum('quantity');
                    $unit = $group->first()->unit_price ?? 0;
                    $subtotal = $qty * $unit;
                    $total += $subtotal;

                    return [
                        $categoryName => [
                            'quantity'   => $qty,
                            'unit_price' => $unit,
                            'subtotal'   => $subtotal,
                        ]
                    ];
                });
        }

        // 🧾 Summary grouped by employee
        $summaryPerEmployee = TransportRecord::select(
                'employee_id',
                'transport_category_id',
                'production_reference',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('unit_price'),
                DB::raw('SUM(total_price) as total_price')
            )
            ->whereBetween('transport_date', [$startDate, $endDate])
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->when(!Schema::hasColumn('transport_records', 'deleted_at'), fn($q) => $q->withoutGlobalScopes())
            ->with(['employee', 'category', 'production'])
            ->groupBy('employee_id', 'transport_category_id', 'production_reference', 'unit_price')
            ->get()
            ->groupBy(fn($item) => $item->employee->name ?? 'Unknown Employee');

        $grandTotal = $summaryPerEmployee->sum(fn($records) => $records->sum('total_price'));

        return view('transport_records.index', compact(
            'employees',
            'categories',
            'stockTypes',
            'productions',
            'date',
            'employeeId',
            'filter',
            'dateRange',
            'summary',
            'summaryPerEmployee',
            'grandTotal'
        ));
    } catch (\Throwable $e) {
        Log::channel('stock')->error('Transport index error: ' . $e->getMessage());
        return back()->with('error', 'Failed to load transport records: ' . $e->getMessage());
    }
}

        // Summary per employee grouped by name
   

    /**
     * Store multiple transport records with production validation
     */
public function storeBulk(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'transport_date' => 'required|date',
        'records' => 'required|array|min:1',
        'records.*.transport_category_id' => 'required|exists:transport_categories,id',
        'records.*.quantity' => 'required|integer|min:1',
        'records.*.destination' => 'nullable|string|max:255',
        'records.*.brick_status' => 'nullable|exists:stock_types,id',
    ]);

    DB::beginTransaction();

    try {
        foreach ($request->records as $recordInput) {

            $category = TransportCategory::findOrFail($recordInput['transport_category_id']);
            $quantity = $recordInput['quantity'];
            $unitPrice = $category->unit_price;
            $stockTypeId = $recordInput['brick_status'] ?? null;
            $stockType = $stockTypeId ? StockType::find($stockTypeId) : null;

            // Handle missing stock type gracefully
            if ($stockTypeId && !$stockType) {
                throw new \Exception("Stock type not found (ID: {$stockTypeId}).");
            }

            // ✅ STEP 1: Deduct from ProductionStock if it's a Mabisi stage (flow_stage == 1)
            if ($stockType && $stockType->flow_stage == 1) {
                $productionStock = \App\Models\ProductionStock::first();

                if (!$productionStock || $productionStock->remaining_quantity < $quantity) {
                    $available = $productionStock->remaining_quantity ?? 0;
                    throw new \Exception("Ushaka gutwara bricks {$quantity}, ariko asigaye ni {$available} muri production stock.");
                }

                $productionStock->remaining_quantity -= $quantity;
                $productionStock->save(); 
            }

            // ✅ STEP 2: Record Transport
            $transport = TransportRecord::create([
                'employee_id'           => $request->employee_id,
                'transport_category_id' => $recordInput['transport_category_id'],
                'transport_date'        => $request->transport_date,
                'quantity'              => $quantity,
                'unit_price'            => $unitPrice,
                'total_price'           => $unitPrice * $quantity,
                'destination'           => $recordInput['destination'] ?? null,
                'stock_type_id'         => $stockTypeId,
            ]);

         
            // ✅ Step 2.1: Store salary for transporter
$employee = \App\Models\Employee::findOrFail($request->employee_id);

\App\Models\Salary::create([
    'employee_id'   => $employee->id,
    'employee_type' => $employee->employeeType->name ?? 'Unknown',
    'date'          => $request->transport_date,
    'amount'        => $transport->total_price,
]);


            // ✅ STEP 3: Handle stock stage transitions
            if ($stockType) {
                $transition = $this->getStockIdTransition($stockType->name);

                // ➖ FROM STOCK transition
                if (!empty($transition['from_id'])) {
                    $fromStock = BrickStock::firstOrCreate(
                        ['stock_type_id' => $transition['from_id']],
                        ['quantity' => 0]
                    );

                    if ($fromStock->quantity < $quantity) {
                        throw new \Exception("Stock idahagije muri '{$transition['from_name']}'. Ihari: {$fromStock->quantity}, Ushaka: {$quantity}");
                    }

                    $this->updateStockQtyById($transition['from_id'], -$quantity);
                    $this->logStockChange($request->employee_id, $transition['from_id'], 'decrease', $quantity, $request->transport_date, $transport->id);
                }

                // ➕ TO STOCK transition
                if (!empty($transition['to_id'])) {
                    $this->updateStockQtyById($transition['to_id'], $quantity);
                    $this->logStockChange($request->employee_id, $transition['to_id'], 'increase', $quantity, $request->transport_date, $transport->id);
                }
            }
        }

        DB::commit();

        return redirect()->route('transport-records.index', [
            'employee_id' => $request->employee_id,
            'date' => $request->transport_date,
        ])->with('success', 'Ibikorwa byose byakiriwe neza!');
        
    } catch (\Exception $e) {
        DB::rollBack();

        Log::channel('stock')->error('Bulk transport failed', [
            'error' => $e->getMessage(),
            'employee_id' => $request->employee_id ?? null,
            'transport_date' => $request->transport_date ?? null,
            'records' => $request->records ?? [],
        ]);

        return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
    }
}

/**
 * Get stock flow transition based on flow_stage
 * Resolves dynamically: current item = TO, previous = FROM
 */
protected function getStockIdTransition(string $toName): array
{
    // Get the TO stock type
    $to = StockType::where('name', $toName)->first();

    if (!$to) {
        return ['from_id' => null, 'to_id' => null];
    }

    // Get the previous stock type by flow_stage
    $from = StockType::where('flow_stage', '<', $to->flow_stage)
        ->orderByDesc('flow_stage')
        ->first();

    return [
        'from_id' => $from?->id,
        'to_id'   => $to->id,
        'from_name' => $from?->name,
        'to_name' => $to->name,
        'flow_stage' => $to->flow_stage
    ];
}

protected function updateStockQtyById($stockTypeId, $qtyChange): void
{
    $stock = BrickStock::firstOrCreate(
        ['stock_type_id' => $stockTypeId],
        ['quantity' => 0]
    );

    $stock->quantity += $qtyChange;
    $stock->save();
}

protected function logStockChange($employeeId, $stockTypeId, $action, $qty, $date, $transportId = null): void
{
    BrickStockLog::create([
        'employee_id'   => $employeeId,
        'stock_type_id' => $stockTypeId,
        'action'        => $action,
        'quantity'      => $qty,
        'stock_date'    => $date,
        'reference'     => $transportId,
    ]);
}

    /**
     * Process a single transport record with full stock handling
     */
    protected function processTransportRecord($employeeId, $transportDate, $recordInput)
    {
        $category = TransportCategory::findOrFail($recordInput['transport_category_id']);
        $stockType = StockType::findOrFail($recordInput['brick_status']);
        $quantity = $recordInput['quantity'];

        // Validate against production
        $this->validateAgainstProduction($employeeId, $transportDate, $quantity);

        // Resolve stock transitions
        $transition = $this->resolveStockTransition($stockType);
        Log::channel('stock')->debug('Stock transition resolved', $transition);

        // Validate source stock availability
        if ($transition['from_id']) {
            $this->validateStockAvailability($transition['from_id'], $quantity);
        }

        // Create transport record
        $transport = TransportRecord::create([
            'employee_id' => $employeeId,
            'transport_category_id' => $category->id,
            'transport_date' => $transportDate,
            'quantity' => $quantity,
            'unit_price' => $category->unit_price,
            'total_price' => $quantity * $category->unit_price,
            'destination' => $recordInput['destination'] ?? null,
            'stock_type_id' => $stockType->id,
        ]);

        // Process stock movements
        $stockMovement = $this->processStockMovement(
            $employeeId,
            $stockType,
            $quantity,
            $transportDate,
            $transport->id,
            $transition
        );

        return [
            'transport_id' => $transport->id,
            'quantity' => $quantity,
            'category' => $category->name,
            'stock_type' => $stockType->name,
            'stock_movement' => $stockMovement
        ];
    }

    /**
     * Resolve stock transitions with enhanced logic
     */
    protected function resolveStockTransition(StockType $stockType): array
    {
        $fromStockType = StockType::where('flow_stage', '<', $stockType->flow_stage)
            ->where('decrease_from', true)
            ->orderByDesc('flow_stage')
            ->first();

        return [
            'from_id' => $fromStockType?->id,
            'from_name' => $fromStockType?->name,
            'to_id' => $stockType->increase_to ? $stockType->id : null,
            'to_name' => $stockType->name,
            'flow_stage' => $stockType->flow_stage
        ];
    }

    /**
     * Validate stock availability with detailed reporting
     */
    protected function validateStockAvailability($stockTypeId, $requiredQuantity): bool
    {
        $stock = BrickStock::firstOrCreate(
            ['stock_type_id' => $stockTypeId],
            ['quantity' => 0]
        );

        if ($stock->quantity < $requiredQuantity) {
            $stockTypeName = $stock->stockType->name ?? 'Unknown';
            throw new \Exception("Insufficient stock in {$stockTypeName}. Available: {$stock->quantity}, Required: {$requiredQuantity}");
        }

        return true;
    }

    

    /**
     * Check stock availability (for validation)
     */
    protected function checkStockAvailability($stockTypeId, $requiredQuantity): array
    {
        try {
            $stock = BrickStock::firstOrCreate(
                ['stock_type_id' => $stockTypeId],
                ['quantity' => 0]
            );

            if ($stock->quantity < $requiredQuantity) {
                $stockTypeName = $stock->stockType->name ?? 'Unknown';
                return [
                    'success' => false,
                    'message' => "Only {$stock->quantity} units available in {$stockTypeName} stock"
                ];
            }

            return ['success' => true];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate against production records
     */
   /**
 * Check if there’s enough bricks in global production stock
 * @throws \Exception
 */
protected function validateAgainstProduction($quantity)
{
    $stock = \App\Models\ProductionStock::first();

    if (!$stock || $stock->remaining_quantity < $quantity) {
        $available = $stock->remaining_quantity ?? 0;
        throw new \Exception("Only {$available} bricks available in production stock. You requested: {$quantity}.");
    }
}

/**
 * Get remaining quantity from centralized production stock
 */
protected function getRemainingGlobalProduction()
{
    $stock = \App\Models\ProductionStock::first();

    return $stock ? $stock->remaining_quantity : 0;
}
/**
 * Deduct bricks from centralized production stock
 * @throws \Exception if not enough stock
 */
protected function deductFromProduction($quantity)
{
    $stock = \App\Models\ProductionStock::first();

    if (!$stock || $stock->remaining_quantity < $quantity) {
        $available = $stock->remaining_quantity ?? 0;
        throw new \Exception("Insufficient stock to deduct production. Requested: {$quantity}, Available: {$available}");
    }

    $stock->remaining_quantity -= $quantity;
    $stock->save();
}
    /**
     * Process stock movement with comprehensive logging
     */
    protected function processStockMovement($employeeId, $stockType, $quantity, $date, $transportId, $transition)
{
    $result = [
        'from_stock' => null,
        'to_stock' => null,
        'errors' => []
    ];

    try {
        // Process stock decrease (from stock) with enhanced validation
        if (!empty($transition['from_id'])) {
            // Validate the from stock type exists
            $fromStockType = StockType::find($transition['from_id']);
            
            if (!$fromStockType) {
                throw new \Exception("Source stock type {$transition['from_id']} not found");
            }

            // Get current stock level with null safety
            $currentFromStock = BrickStock::firstOrCreate(
                ['stock_type_id' => $transition['from_id']],
                ['quantity' => 0]
            );

            // Validate sufficient stock
            if ($currentFromStock->quantity < $quantity) {
                throw new \Exception(sprintf(
                    "Insufficient stock in %s. Available: %d, Required: %d",
                    $transition['from_name'] ?? 'Unknown Source',
                    $currentFromStock->quantity,
                    $quantity
                ));
            }

            // Process the stock decrease
            $fromStock = $this->updateStock(
                $transition['from_id'],
                -$quantity,
                $employeeId,
                $date,
                $transportId,
                'decrease'
            );

            $result['from_stock'] = [
                'type' => $transition['from_name'] ?? 'Unknown Source',
                'quantity' => -$quantity,
                'before' => $fromStock->quantity + $quantity,
                'after' => $fromStock->quantity,
                'stock_type_id' => $transition['from_id']
            ];
        }

        // Process stock increase (to stock) - existing logic remains
        if (!empty($transition['to_id'])) {
            $toStock = $this->updateStock(
                $transition['to_id'],
                $quantity,
                $employeeId,
                $date,
                $transportId,
                'increase'
            );

            $result['to_stock'] = [
                'type' => $transition['to_name'] ?? 'Unknown Destination',
                'quantity' => $quantity,
                'before' => $toStock->quantity - $quantity,
                'after' => $toStock->quantity,
                'stock_type_id' => $transition['to_id']
            ];
        }

        Log::channel('stock')->info('Stock movement completed', [
            'result' => $result,
            'metadata' => [
                'employee_id' => $employeeId,
                'transport_id' => $transportId,
                'date' => $date,
                'stock_type' => $stockType->name
            ]
        ]);

        return $result;

    } catch (\Exception $e) {
        Log::channel('stock')->error('Stock movement failed', [
            'error' => $e->getMessage(),
            'context' => [
                'employee_id' => $employeeId,
                'transport_id' => $transportId,
                'stock_type' => optional($stockType)->name ?? 'Unknown',
                'quantity' => $quantity,
                'transition' => $transition,
                'stack_trace' => $e->getTraceAsString()
            ]
        ]);
        
        $result['errors'][] = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'type' => get_class($e)
        ];
        
        throw new \Exception("Stock movement processing failed: " . $e->getMessage(), 0, $e);
    }
}

    /**
     * Update stock with comprehensive validation and logging
     */
    protected function updateStock($stockTypeId, $qtyChange, $employeeId, $date, $referenceId, $action)
    {
        $stock = BrickStock::firstOrCreate(
            ['stock_type_id' => $stockTypeId],
            ['quantity' => 0]
        );

        $beforeQty = $stock->quantity;
        $newQty = $beforeQty + $qtyChange;

        if ($newQty < 0) {
            $stockTypeName = optional($stock->stockType)->name ?? 'Unknown';
            throw new \Exception("Negative stock not allowed for {$stockTypeName}. Available: {$beforeQty}, Change: {$qtyChange}");
        }

        $stock->quantity = $newQty;
        $stock->save();

        $this->createStockLog(
            $employeeId,
            $stockTypeId,
            $action,
            abs($qtyChange),
            $date,
            $referenceId,
            $beforeQty,
            $newQty
        );

        return $stock;
    }
    /**
     * Create detailed stock log entry
     */
/**
 * Creates a stock log entry with full validation and error handling
 */
/**
 * Updates stock quantity AND creates log entry atomically
 */
protected function updateStockWithLog($stockTypeId, $qtyChange, $employeeId, $date, $referenceId, $action)
{
    DB::beginTransaction();
    
    try {
        // 1. Update brick_stocks table
        $stock = BrickStock::firstOrCreate(
            ['stock_type_id' => $stockTypeId],
            ['quantity' => 0]
        );

        $beforeQty = $stock->quantity;
        $newQty = $beforeQty + $qtyChange;

        if ($newQty < 0) {
            throw new \Exception("Negative stock not allowed. Current: $beforeQty, Change: $qtyChange");
        }

        $stock->quantity = $newQty;
        $stock->save();

        // 2. Create brick_stock_logs entry
        $log = $this->createStockLog(
            $employeeId,
            $stockTypeId,
            $action,
            abs($qtyChange),
            $date,
            $referenceId,
            $beforeQty,
            $newQty
        );

        DB::commit();

        return [
            'stock' => $stock,
            'log' => $log
        ];

    } catch (\Exception $e) {
        DB::rollBack();
        Log::channel('stock')->error('Stock update failed', [
            'error' => $e->getMessage(),
            'data' => compact('stockTypeId', 'qtyChange', 'employeeId')
        ]);
        throw $e;
    }
}
/**
 * Creates log entry with validation
 */
protected function createStockLog($employeeId, $stockTypeId, $action, $quantity, $date, $referenceId, $beforeQty, $afterQty)
{
    // Validate inputs
    if (!Employee::find($employeeId)) {
        throw new \Exception("Invalid employee ID: $employeeId");
    }

    if (!StockType::find($stockTypeId)) {
        throw new \Exception("Invalid stock type ID: $stockTypeId");
    }

    $logData = [
        'employee_id' => $employeeId,
        'stock_type_id' => $stockTypeId,
        'action' => $action,
        'quantity' => $quantity,
        'stock_date' => $date,
        'reference' => $referenceId,
        'remarks' => 'System generated',
        'created_at' => now(),
        'updated_at' => now()
    ];

    try {
        $log = BrickStockLog::create($logData);
        
        if (!$log->exists) {
            throw new \Exception("Log entry not created");
        }

        return $log;

    } catch (\Exception $e) {
        Log::channel('stock')->error('Log creation failed', [
            'error' => $e->getMessage(),
            'data' => $logData
        ]);
        throw new \Exception("Failed to create log: " . $e->getMessage());
    }
}

/**
 * Validates stock log parameters
 */
/**
 * Updates stock quantity AND creates log entry atomically
 */
/**
 * Updates stock quantity AND creates log entry atomically
 */


protected function validateLogParameters($employeeId, $stockTypeId, $action, $quantity, $date)
{
    $errors = [];
    
    // Validate employee exists
    if (!Employee::where('id', $employeeId)->exists()) {
        $errors[] = "Invalid employee ID: {$employeeId}";
    }

    // Validate stock type exists
    if (!StockType::where('id', $stockTypeId)->exists()) {
        $errors[] = "Invalid stock type ID: {$stockTypeId}";
    }

    // Validate action
    if (!in_array($action, ['increase', 'decrease'])) {
        $errors[] = "Invalid action: {$action}. Must be 'increase' or 'decrease'";
    }

    // Validate quantity
    if (!is_numeric($quantity) || $quantity <= 0) {
        $errors[] = "Invalid quantity: {$quantity}. Must be positive number";
    }

    // Validate date
    if (!strtotime($date)) {
        $errors[] = "Invalid date format: {$date}";
    }

    if (!empty($errors)) {
        throw new \InvalidArgumentException(implode(', ', $errors));
    }
}

    /**
     * Show single employee's transport data
     */
public function show($employeeId, Request $request)
{
    try {
        $employee = Employee::findOrFail($employeeId);
        $categories = TransportCategory::orderBy('name')->get();
        $stockTypes = StockType::all()->keyBy('id');

        $categoriesWithStock = $categories->filter(function ($cat) {
            return str_contains(strtolower($cat->name), 'amatafari');
        })->pluck('id')->toArray();

        // Get all filter parameters
        $filter = $request->input('filter');
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $date = $request->input('date', now()->toDateString());

        // Initialize variables
        $startDate = $date;
        $endDate = $date;
        $dateLabel = '';

        // Handle custom date range first
        if ($start && $end) {
            $startDate = $start;
            $endDate = $end;
            $dateLabel = "Kuva $start kugeza $end";
        } 
        // Handle predefined filters
        elseif ($filter) {
            switch ($filter) {
                case 'today':
                    $startDate = $endDate = now()->toDateString();
                    $dateLabel = 'Uyu munsi - ' . now()->format('Y-m-d');
                    break;
                    
                case 'week':
                    $startDate = now()->startOfWeek()->toDateString();
                    $endDate = now()->endOfWeek()->toDateString();
                    $dateLabel = 'Iyi semana (' . now()->startOfWeek()->format('M d') . ' - ' . now()->endOfWeek()->format('M d, Y') . ')';
                    break;
                    
                case 'month':
                    $startDate = now()->startOfMonth()->toDateString();
                    $endDate = now()->endOfMonth()->toDateString();
                    $dateLabel = 'Uku kwezi - ' . now()->format('F Y');
                    break;
                    
                case 'year':
                    $startDate = now()->startOfYear()->toDateString();
                    $endDate = now()->endOfYear()->toDateString();
                    $dateLabel = 'Uyu mwaka - ' . now()->format('Y');
                    break;
                    
                default:
                    $startDate = $endDate = $date;
                    $dateLabel = 'Itariki: ' . $date;
                    break;
            }
        }
        // Default to today if no filter specified
        else {
            $startDate = $endDate = now()->toDateString();
            $dateLabel = 'Uyu munsi - ' . now()->format('Y-m-d');
        }

        $reportTitle = "Reba ibyakozwe na {$employee->name}";

        // Fetch records
        $records = TransportRecord::with(['category', 'stockType', 'production'])
            ->where('employee_id', $employeeId)
            ->whereBetween('transport_date', [$startDate, $endDate])
            ->get()
            ->map(function ($record) use ($categoriesWithStock) {
                $record->requires_stock_type = in_array($record->transport_category_id, $categoriesWithStock);
                return $record;
            });

        $total = $records->sum('total_price');

        $summary = $records->groupBy('category.name')->mapWithKeys(function ($group, $catName) {
            $qty = $group->sum('quantity');
            $unit = $group->first()->unit_price ?? 0;
            return [
                $catName => [
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'subtotal' => $qty * $unit
                ]
            ];
        });

        return view('transport_records.show', compact(
            'records',
            'employee',
            'categories',
            'stockTypes',
            'total',
            'dateLabel',
            'startDate',
            'endDate',
            'summary',
            'reportTitle'
        ));

    } catch (\Throwable $e) {
        return back()->with('error', 'Ntibikunze kubona ibikorwa: ' . $e->getMessage());
    }
}




    /**
     * Display edit form for a record
     */
    public function edit($id)
    {
        try {
            $record = TransportRecord::with(['employee', 'category', 'stockType', 'production'])->findOrFail($id);
            $employees = Employee::orderBy('name')->get();
            $categories = TransportCategory::orderBy('name')->get();
            $stockTypes = StockType::orderBy('flow_stage')->get();

            return view('transport_records.edit', compact(
                'record', 
                'employees', 
                'categories',
                'stockTypes'
            ));

        } catch (\Exception $e) {
            Log::channel('stock')->error('Edit transport record error', [
                'record_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error loading record: '.$e->getMessage());
        }
    }

    /**
     * Update existing record with comprehensive stock handling
     */
public function update(Request $request, $id)
{
    $record = TransportRecord::with(['stockType', 'production'])->findOrFail($id);

    $validated = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'transport_category_id' => 'required|exists:transport_categories,id',
        'transport_date' => 'required|date',
        'quantity' => [
            'required',
            'integer',
            'min:1',
            new PositiveStock($request->input('brick_status'), $record->quantity)
        ],
        'destination' => 'nullable|string|max:255',
        'brick_status' => 'required|exists:stock_types,id',
    ]);

    $category = TransportCategory::findOrFail($validated['transport_category_id']);
    $quantity = $validated['quantity'];
    $newStockType = StockType::findOrFail($validated['brick_status']);

    DB::beginTransaction();

    try {
        Log::channel('stock')->info('Transport update requested', [
            'record_id' => $id,
            'old_data' => $record->toArray(),
            'new_data' => $validated
        ]);

        // ✅ STEP 1: Use flow_stage instead of hardcoding names
        $currentFlowStage = optional($record->stockType)->flow_stage;
        $newFlowStage = $newStockType->flow_stage;

        $wasFirstStage = $currentFlowStage === 1;
        $isNowFirstStage = $newFlowStage === 1;

        // ✅ STEP 2: Refund old deduction (if was stage 1)
        if ($wasFirstStage) {
            $prodStock = \App\Models\ProductionStock::first();
            $prodStock->remaining_quantity += $record->quantity;
            $prodStock->save();
        }

        // ✅ STEP 3: Validate and deduct production if new is stage 1
        if ($isNowFirstStage) {
            $prodStock = \App\Models\ProductionStock::first();
            $adjustedAvailable = ($prodStock->remaining_quantity ?? 0) + ($wasFirstStage ? $record->quantity : 0);

            if ($adjustedAvailable < $quantity) {
                throw new \Exception("Production stock is insufficient. Available (after refund): {$adjustedAvailable}, Requested: {$quantity}.");
            }

            $prodStock->remaining_quantity -= $quantity;
            $prodStock->save();
        }

        // ✅ STEP 4: Resolve flow relationships dynamically
        $transition = $this->resolveStockTransition($newStockType);

        // STEP 5: Validate from stock if required
        if (!empty($transition['from_id'])) {
            $fromStock = BrickStock::firstOrCreate(
                ['stock_type_id' => $transition['from_id']],
                ['quantity' => 0]
            );

            if (
                $record->stock_type_id != $validated['brick_status'] ||
                $record->quantity != $quantity
            ) {
                if ($fromStock->quantity < $quantity) {
                    throw new \Exception("Stock idahagije muri '{$transition['from_name']}'. Ihari: {$fromStock->quantity}, Ushaka: {$quantity}");
                }
            }
        }

        // STEP 6: Reverse old movement if needed
        if (
            $record->stock_type_id != $validated['brick_status'] ||
            $record->quantity != $quantity
        ) {
            $this->reverseStockMovement($record);
        }

        // STEP 7: Update record
        $record->update([
            'employee_id' => $validated['employee_id'],
            'transport_category_id' => $validated['transport_category_id'],
            'transport_date' => $validated['transport_date'],
            'quantity' => $quantity,
            'unit_price' => $category->unit_price,
            'total_price' => $category->unit_price * $quantity,
            'destination' => $validated['destination'],
            'stock_type_id' => $validated['brick_status'],
        ]);

        // STEP 8: Apply new flow movement
        $this->processStockMovement(
            $validated['employee_id'],
            $newStockType,
            $quantity,
            $validated['transport_date'],
            $record->id,
            $transition
        );

        DB::commit();

        return redirect()->route('transport-records.index')
            ->with('success', 'Transport record updated successfully!');

    } catch (\Exception $e) {
        DB::rollBack();

        Log::channel('stock')->error('Transport update failed', [
            'record_id' => $id,
            'message' => $e->getMessage(),
        ]);

        return redirect()->back()
            ->withInput()
            ->with('error', 'Update failed: ' . $e->getMessage());
    }
}

    /**
     * Reverse stock movement for a record
     */
    protected function reverseStockMovement($record)
    {
        if (!$record->stock_type_id) return;

        $stockType = $record->stockType;
        $quantity = $record->quantity;

        Log::channel('stock')->info('Reversing stock movement', [
            'record_id' => $record->id,
            'stock_type' => $stockType->name,
            'quantity' => $quantity
        ]);

        // Reverse STOCK OUT (return to source)
        if ($stockType->flow_stage > 1) {
            $fromStockType = StockType::where('flow_stage', '<', $stockType->flow_stage)
                ->where('decrease_from', true)
                ->orderByDesc('flow_stage')
                ->first();

            if ($fromStockType) {
                $this->updateStock(
                    $fromStockType->id,
                    $quantity,
                    $record->employee_id,
                    $record->transport_date,
                    $record->id,
                    'increase'
                );
            }
        }

        // Reverse STOCK IN (remove from destination)
        if ($stockType->increase_to) {
            $this->updateStock(
                $stockType->id,
                -$quantity,
                $record->employee_id,
                $record->transport_date,
                $record->id,
                'decrease'
            );
        }
    }

    /**
     * Delete transport record with stock reversal
     */
    public function destroy($id)
    {
        $record = TransportRecord::with('stockType')->findOrFail($id);

        DB::beginTransaction();

        try {
            Log::channel('stock')->info('Deleting transport record', [
                'record_id' => $id,
                'record_data' => $record->toArray()
            ]);

            // Reverse stock movement before deletion
            if ($record->stock_type_id) {
                $this->reverseStockMovement($record);
            }

            $record->delete();

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Record deleted successfully'
                ]);
            }

            return redirect()->back()->with('success', 'Record deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stock')->error('Delete transport record failed', [
                'record_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error deleting record: ' . $e->getMessage());
        }
    }

    /**
     * Stock summary report with enhanced filtering and logging
     */
public function stockSummary(Request $request)
{
    try {
        // ✅ 1. Validate input filter & optional stock_type_id
        $request->validate([
            'filter' => 'sometimes|in:day,week,month,year,custom,between',
            'start_date' => 'required_if:filter,custom,between|date',
            'end_date' => 'required_if:filter,custom,between|date|after_or_equal:start_date',
            'stock_type_id' => 'nullable|exists:stock_types,id',
        ]);

        // ✅ 2. Determine date range - Changed default filter to 'month'
        $filter = $request->input('filter', 'month');
        $stockTypeId = $request->input('stock_type_id');

        // Handle different filter types
        switch ($filter) {
            case 'custom':
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
                break;
                
            case 'between':
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
                break;
                
            default: // day, week, month, year
                $start = now()->startOf($filter);
                $end = now()->endOf($filter);
        }

        Log::channel('stock')->debug('Generating stock summary', compact('filter', 'start', 'end', 'stockTypeId'));

        // ✅ 3. Build main logs query
        $query = BrickStockLog::with(['stockType', 'employee'])
            ->whereBetween('stock_date', [$start, $end]);

        if ($stockTypeId) {
            $query->where('stock_type_id', $stockTypeId);
        }

        // ✅ 4. Aggregate stock actions
        $logs = $query->select(
                'stock_type_id',
                DB::raw("SUM(CASE WHEN action = 'increase' THEN quantity ELSE 0 END) AS total_in"),
                DB::raw("SUM(CASE WHEN action = 'decrease' THEN quantity ELSE 0 END) AS total_out"),
                DB::raw("SUM(CASE WHEN action IN ('correction', 'reverse_sale', 'adjustment') THEN quantity ELSE 0 END) AS total_corrections"),
                DB::raw("MAX(stock_date) AS last_activity")
            )
            ->groupBy('stock_type_id')
            ->get()
            ->map(function ($item) {
                return [
                    'stock_type'     => $item->stockType->name ?? 'Unknown',
                    'type_id'        => $item->stock_type_id,
                    'in'             => (int) $item->total_in,
                    'out'            => (int) $item->total_out,
                    'correction'     => (int) $item->total_corrections,
                    'net'            => (int) $item->total_in - (int) $item->total_out + (int) $item->total_corrections,
                    'last_activity'  => $item->last_activity,
                ];
            });

        // ✅ 5. Recent correction logs
        $corrections = BrickStockLog::with('stockType', 'employee')
            ->whereBetween('stock_date', [$start, $end])
            ->where('action', 'correction');

        if ($stockTypeId) {
            $corrections->where('stock_type_id', $stockTypeId);
        }

        $correctionLogs = $corrections->latest('stock_date')->take(10)->get();

        // ✅ 6. Fetch remaining production from production_stocks table
        $stock = ProductionStock::first();
        $remainingProduction = $stock->remaining_quantity ?? 0;

        // ✅ 7. Return view with all data
        return view('reports.stock-summary', [
            'logs'                => $logs,
            'corrections'         => $correctionLogs,
            'filter'              => $filter,
            'stockTypes'          => StockType::orderBy('name')->get(),
            'selectedType'        => $stockTypeId,
            'start_date'          => $start->toDateString(),
            'end_date'            => $end->toDateString(),
            'remainingProduction' => $remainingProduction,
        ]);

    } catch (\Exception $e) {
        Log::channel('stock')->error('Stock summary error', [
            'error'    => $e->getMessage(),
            'trace'    => $e->getTraceAsString(),
            'request'  => $request->all()
        ]);

        return back()->with('error', 'Error generating report: ' . $e->getMessage());
    }
}

   
/**
     * Export stock summary to CSV
     */
    public function exportStockSummaryCsv(Request $request)
    {
        try {
            $request->validate([
                'filter' => 'sometimes|in:day,week,month,year,custom',
                'start_date' => 'required_if:filter,custom|date',
                'end_date' => 'required_if:filter,custom|date|after_or_equal:start_date'
            ]);

            $filter = $request->input('filter', 'day');

            if ($filter === 'custom') {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
            } else {
                $start = now()->startOf($filter);
                $end = now()->endOf($filter);
            }

            $logs = BrickStockLog::with('stockType')
                ->whereBetween('stock_date', [$start, $end])
                ->select(
                    'stock_type_id',
                    DB::raw("SUM(CASE WHEN action = 'increase' THEN quantity ELSE 0 END) AS total_in"),
                    DB::raw("SUM(CASE WHEN action = 'decrease' THEN quantity ELSE 0 END) AS total_out"),
                    DB::raw("MAX(stock_date) as last_activity")
                )
                ->groupBy('stock_type_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'Stock Type' => $item->stockType->name ?? 'Unknown',
                        'In' => $item->total_in,
                        'Out' => $item->total_out,
                        'Net' => $item->total_in - $item->total_out,
                        'Last Activity' => $item->last_activity,
                    ];
                });

            $filename = 'stock_summary_'.$filter.'_'.now()->format('Ymd_His').'.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=$filename",
            ];

            return response()->stream(function() use ($logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Stock Type', 'In', 'Out', 'Net', 'Last Activity']);

                foreach ($logs as $log) {
                    fputcsv($handle, $log);
                }

                fclose($handle);
            }, 200, $headers);

        } catch (\Exception $e) {
            Log::channel('stock')->error('CSV export failed', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error exporting data: '.$e->getMessage());
        }
    }

    /**
     * Get current stock levels with enhanced data
     */
    public function currentStock()
    {
        try {
            $stocks = StockType::with(['brickStock', 'latestLog' => function($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('flow_stage')
            ->get()
            ->map(function ($type) {
                $latestLog = $type->latestLog->first();
                
                return [
                    'type' => $type->name,
                    'quantity' => $type->brickStock->quantity ?? 0,
                    'stage' => $type->flow_stage,
                    'last_updated' => $type->brickStock->updated_at ?? null,
                    'last_activity' => $latestLog ? [
                        'date' => $latestLog->stock_date,
                        'action' => $latestLog->action,
                        'quantity' => $latestLog->quantity,
                        'employee' => $latestLog->employee->name ?? 'Unknown'
                    ] : null
                ];
            });

            return view('reports.current-stock', compact('stocks'));

        } catch (\Exception $e) {
            Log::channel('stock')->error('Current stock error', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error loading current stock: '.$e->getMessage());
        }
    }

    /**
     * Get available productions for AJAX select
     */
    public function availableProductions(Request $request)
    {
        try {
            $date = $request->input('date', now()->toDateString());
            $employeeId = $request->input('employee_id');

            return Production::whereDate('production_date', $date)
                ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
                ->whereRaw('quantity > COALESCE((SELECT SUM(quantity) FROM transport_records WHERE production_reference = production_records.id), 0)')
                ->with('employee')
                ->get()
                ->map(fn($prod) => [
                    'id' => $prod->id,
                    'text' => "{$prod->reference_number} - {$prod->employee->name} ({$prod->available_quantity}/{$prod->quantity} {$prod->product_type})"
                ]);

        } catch (\Exception $e) {
            Log::channel('stock')->error('Available productions error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function summary(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $employeeId = $request->input('employee_id');

        $employees = Employee::all();
        $categories = TransportCategory::all();

        $query = DB::table('transport_records')
            ->join('employees', 'transport_records.employee_id', '=', 'employees.id')
            ->join('transport_categories', 'transport_records.transport_category_id', '=', 'transport_categories.id')
            ->select(
                'employees.id as employee_id',
                'employees.name as employee_name',
                'transport_categories.id as transport_category_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(quantity * transport_categories.unit_price) as total_price')
            )
            ->whereDate('transport_date', $date)
            ->groupBy('employees.id', 'employees.name', 'transport_categories.id');

        if ($employeeId) {
            $query->where('transport_records.employee_id', $employeeId);
        }

        $records = $query->get();

        $summaryPerEmployee = $records->groupBy('employee_name');

        return view('transport-records.summary', [
            'employees' => $employees,
            'categories' => $categories,
            'summaryPerEmployee' => $summaryPerEmployee,
            'date' => $date
        ]);
    }
}