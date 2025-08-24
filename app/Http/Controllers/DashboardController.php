<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sales;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:60,1');
    }

    /**
     * Display dashboard with filtered data
     */
    public function index(Request $request)
    {
        // Get filter from query or default to 'today'
        $filter = $request->query('filter', 'today');
        
        $timelineEvents = [
            [
                'title' => 'Team Meeting',
                'description' => 'Quarterly sales review',
                'time' => now()->setTime(9, 0),
                'color' => 'primary',
                'icon' => 'bi-people-fill',
                'user' => null
            ],
            [
                'title' => 'Client Call',
                'description' => 'Discuss new project requirements',
                'time' => now()->setTime(11, 30),
                'color' => 'info',
                'icon' => 'bi-telephone-outbound-fill',
                'user' => ['name' => 'John Doe']
            ],
            [
                'title' => 'Lunch Break',
                'description' => '',
                'time' => now()->setTime(13, 0),
                'color' => 'warning',
                'icon' => 'bi-egg-fried',
                'user' => null
            ],
            [
                'title' => 'Product Demo',
                'description' => 'New feature showcase for investors',
                'time' => now()->setTime(15, 30),
                'color' => 'success',
                'icon' => 'bi-laptop',
                'user' => ['name' => 'Jane Smith']
            ]
        ];

        try {
            // Validate the filter to avoid bad values
            $validator = Validator::make(['filter' => $filter], [
                'filter' => 'required|in:today,week,month,year,all'
            ]);

            if ($validator->fails()) {
                $filter = 'today';
                Log::warning("Invalid dashboard filter provided, defaulting to 'today'");
            }

            $userId = Auth::id();
            $cacheKey = "dashboard_{$filter}_user_{$userId}";

            // Cache per filter & user for 5 minutes
            $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filter) {
                return $this->generateDashboardData($filter);
            });

            return view('dashboard', array_merge($data, [
                'timelineEvents' => $timelineEvents,
            ]));

        } catch (\Exception $e) {
            Log::error("Dashboard error: " . $e->getMessage(), [
                'filter' => $filter,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('dashboard', $this->getErrorResponse($filter, $e->getMessage()));
        }
    }

    /**
     * Generate comprehensive dashboard data
     */
    protected function generateDashboardData(string $filter): array
    {
        $dateRange = $this->getDateRange($filter);
        
        // Get all data in parallel to avoid redundant queries
        $salesData = $this->getSalesData($dateRange);
        $comparisonData = $this->getComparisonData($filter);
        $topProducts = $this->getTopProducts(5, $dateRange);
        $recentSales = $this->getRecentSales(5, $dateRange);
        $salesTrend = $this->getSalesTrendData($dateRange, $filter);

        // Users, Employees, and Customers data
        $usersData = $this->getUsersData($dateRange);
        $employeesData = $this->getEmployeesData($dateRange);
        $customersData = $this->getCustomersData($dateRange);

        return [
            'filter' => $filter,
            'periodLabel' => $this->getPeriodLabel($filter),
            'comparisonText' => $this->getComparisonText($filter),
            'hasComparison' => ($comparisonData['status'] ?? '') === 'success',
            'lastUpdated' => now(),
            'status' => 'ok',
            
            // Users data
            'totalUsers' => $usersData['total'],
            'newUsersCount' => $usersData['new'],
            'activeUsersCount' => $usersData['active'],
            
            // Employees data
            'totalEmployees' => $employeesData['total'],
            'newEmployeesCount' => $employeesData['new'],
            'activeEmployeesCount' => $employeesData['active'],
            
            // Customers data
            'totalCustomers' => $customersData['total'],
            'newCustomersCount' => $customersData['new'],
            'activeCustomersCount' => $customersData['active'],
            
            // Sales data
            'salesCount' => $salesData['count'],
            'totalRevenue' => $salesData['revenue'],
            'averageOrderValue' => $salesData['average'],
            'salesPercentChange' => $comparisonData['sales']['change'] ?? 0,
            'revenuePercentChange' => $comparisonData['revenue']['change'] ?? 0,
            
            // Chart & table datasets
            'salesTrend' => [
                'labels' => array_column($salesTrend, 'period'),
                'data' => array_column($salesTrend, 'revenue'),
                'counts' => array_column($salesTrend, 'sales_count')
            ],
            'topProducts' => $topProducts,
            'recentSales' => $recentSales,
        ];
    }

    /**
     * Get users data with proper error handling
     */
    protected function getUsersData(array $dateRange): array
    {
        try {
            $total = Cache::remember('total_users_count', now()->addHours(1), function() {
                return User::count();
            });

            $new = User::whereBetween('created_at', [
                $dateRange['start']->format('Y-m-d H:i:s'),
                $dateRange['end']->format('Y-m-d H:i:s')
            ])->count();

            // Check if your User model has a last_login_at column
            $active = 0;
            if (DB::getSchemaBuilder()->hasColumn('users', 'last_login_at')) {
                $active = User::whereBetween('last_login_at', [
                    $dateRange['start']->format('Y-m-d H:i:s'),
                    $dateRange['end']->format('Y-m-d H:i:s')
                ])->count();
            } else {
                // Alternative: count users who have logged in recently based on updated_at
                $active = User::whereBetween('updated_at', [
                    $dateRange['start']->format('Y-m-d H:i:s'),
                    $dateRange['end']->format('Y-m-d H:i:s')
                ])->count();
            }

            return [
                'total' => $total,
                'new' => $new,
                'active' => $active
            ];

        } catch (\Exception $e) {
            Log::error("Failed to get users data: " . $e->getMessage());
            return ['total' => 0, 'new' => 0, 'active' => 0];
        }
    }

    /**
     * Get employees data with proper error handling
     */
    protected function getEmployeesData(array $dateRange): array
    {
        try {
            $total = Cache::remember('total_employees_count', now()->addHours(1), function() {
                return Employee::count();
            });

            // Check if Employee model has hire_date or created_at column
            $new = 0;
            if (DB::getSchemaBuilder()->hasColumn('employees', 'hire_date')) {
                $new = Employee::whereBetween('hire_date', [
                    $dateRange['start']->format('Y-m-d'),
                    $dateRange['end']->format('Y-m-d')
                ])->count();
            } elseif (DB::getSchemaBuilder()->hasColumn('employees', 'created_at')) {
                $new = Employee::whereBetween('created_at', [
                    $dateRange['start']->format('Y-m-d H:i:s'),
                    $dateRange['end']->format('Y-m-d H:i:s')
                ])->count();
            }

            // Active employees who made sales in this period
            $active = Employee::whereHas('sales', function($query) use ($dateRange) {
                $query->whereBetween('sale_date', [
                    $dateRange['start']->format('Y-m-d'),
                    $dateRange['end']->format('Y-m-d')
                ]);
            })->count();

            return [
                'total' => $total,
                'new' => $new,
                'active' => $active
            ];

        } catch (\Exception $e) {
            Log::error("Failed to get employees data: " . $e->getMessage());
            return ['total' => 0, 'new' => 0, 'active' => 0];
        }
    }

    /**
     * Get customers data with proper error handling
     */
    protected function getCustomersData(array $dateRange): array
    {
        try {
            $total = Cache::remember('total_customers_count', now()->addHours(1), function() {
                return Customer::count();
            });

            $new = Customer::whereBetween('created_at', [
                $dateRange['start']->format('Y-m-d H:i:s'),
                $dateRange['end']->format('Y-m-d H:i:s')
            ])->count();

            // Active customers who made purchases in this period
            // Check if you have a direct relationship or need to go through sales
            $active = 0;
            
            // Method 1: If customers have direct relationship with sales
            if (method_exists(Customer::class, 'sales')) {
                $active = Customer::whereHas('sales', function($query) use ($dateRange) {
                    $query->whereBetween('sale_date', [
                        $dateRange['start']->format('Y-m-d'),
                        $dateRange['end']->format('Y-m-d')
                    ]);
                })->count();
            } else {
                // Method 2: Count distinct customers from sales table
                $active = Sales::whereBetween('sale_date', [
                    $dateRange['start']->format('Y-m-d'),
                    $dateRange['end']->format('Y-m-d')
                ])->distinct('customer_id')->count('customer_id');
            }

            return [
                'total' => $total,
                'new' => $new,
                'active' => $active
            ];

        } catch (\Exception $e) {
            Log::error("Failed to get customers data: " . $e->getMessage());
            return ['total' => 0, 'new' => 0, 'active' => 0];
        }
    }

    /**
     * Get sales data with proper error handling
     */
    protected function getSalesData(array $dateRange): array
    {
        try {
            $query = Sales::whereBetween('sale_date', [
                $dateRange['start']->format('Y-m-d'), 
                $dateRange['end']->format('Y-m-d')
            ]);

            // Only apply completed scope if it exists
            if (method_exists(Sales::class, 'scopeCompleted')) {
                $query = $query->completed();
            }

            $count = $query->count();
            $revenue = (float) $query->sum('total_price');
            $average = $count > 0 ? round($revenue / $count, 2) : 0;

            return [
                'count' => $count,
                'revenue' => $revenue,
                'average' => $average,
            ];
        } catch (\Exception $e) {
            Log::error("Sales data error: " . $e->getMessage());
            return [
                'count' => 0,
                'revenue' => 0,
                'average' => 0
            ];
        }
    }

    /**
     * Get comparison data between current and previous period
     */
    protected function getComparisonData(string $filter): array
    {
        $dateRange = $this->getDateRange($filter);
        
        if (!$dateRange['previous_start'] || !$dateRange['previous_end']) {
            return [
                'status' => 'no_data',
                'message' => 'No comparison available',
                'sales' => ['current' => 0, 'previous' => 0, 'change' => 0],
                'revenue' => ['current' => 0, 'previous' => 0, 'change' => 0]
            ];
        }

        $currentSales = $this->getSalesData([
            'start' => $dateRange['start'],
            'end' => $dateRange['end']
        ]);
        
        $previousSales = $this->getSalesData([
            'start' => $dateRange['previous_start'],
            'end' => $dateRange['previous_end']
        ]);

        return [
            'status' => 'success',
            'sales' => [
                'current' => $currentSales['count'],
                'previous' => $previousSales['count'],
                'change' => $this->calculatePercentageChange($currentSales['count'], $previousSales['count'])
            ],
            'revenue' => [
                'current' => $currentSales['revenue'],
                'previous' => $previousSales['revenue'],
                'change' => $this->calculatePercentageChange($currentSales['revenue'], $previousSales['revenue'])
            ]
        ];
    }

    /**
     * Get sales trend chart data with proper date handling
     */
    protected function getSalesTrendData(array $dateRange, string $filter): array
    {
        try {
            $groupFormat = match ($filter) {
                'today' => '%H:00',
                'week', 'month' => '%Y-%m-%d',
                'year' => '%Y-%m',
                default => '%Y-%m'
            };

            $query = Sales::select(
                DB::raw("DATE_FORMAT(sale_date, '{$groupFormat}') as period"),
                DB::raw("SUM(total_price) as revenue"),
                DB::raw("COUNT(*) as sales_count")
            )
            ->whereBetween('sale_date', [
                $dateRange['start']->format('Y-m-d'),
                $dateRange['end']->format('Y-m-d')
            ])
            ->groupBy('period')
            ->orderBy('period');

            // Only apply completed scope if it exists
            if (method_exists(Sales::class, 'scopeCompleted')) {
                $query = $query->completed();
            }

            $results = $query->get();

            return $results->map(function ($item) {
                return [
                    'period' => $item->period,
                    'revenue' => (float)$item->revenue,
                    'sales_count' => (int)$item->sales_count
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error("Sales trend error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top selling products
     */
    protected function getTopProducts(int $limit, array $dateRange): array
    {
        try {
            return DB::table('sales')
                ->select(
                    'stock_type_id',
                    'stock_types.name as product_name',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(total_price) as total_revenue')
                )
                ->join('stock_types', 'sales.stock_type_id', '=', 'stock_types.id')
                ->whereBetween('sale_date', [
                    $dateRange['start']->format('Y-m-d'), 
                    $dateRange['end']->format('Y-m-d')
                ])
                ->groupBy('stock_type_id', 'stock_types.name')
                ->orderByDesc('total_revenue')
                ->limit($limit)
                ->get()
                ->map(function($item) {
                    return [
                        'stock_type_id' => $item->stock_type_id,
                        'product_name' => $item->product_name,
                        'total_quantity' => (int)$item->total_quantity,
                        'total_revenue' => (float)$item->total_revenue
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error("Top products error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent sales
     */
    protected function getRecentSales(int $limit, array $dateRange): array
    {
        try {
            return Sales::with(['customer', 'employee', 'stockType'])
                ->whereBetween('sale_date', [
                    $dateRange['start']->format('Y-m-d'), 
                    $dateRange['end']->format('Y-m-d')
                ])
                ->orderByDesc('sale_date')
                ->orderByDesc('created_at') // Secondary sort for same-day sales
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error("Recent sales error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get date range for filtering
     */
    protected function getDateRange(string $filter): array
    {
        $now = now();
        
        switch ($filter) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'previous_start' => $now->copy()->subDay()->startOfDay(),
                    'previous_end' => $now->copy()->subDay()->endOfDay()
                ];
                
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                    'previous_start' => $now->copy()->subWeek()->startOfWeek(),
                    'previous_end' => $now->copy()->subWeek()->endOfWeek()
                ];
                
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'previous_start' => $now->copy()->subMonth()->startOfMonth(),
                    'previous_end' => $now->copy()->subMonth()->endOfMonth()
                ];
                
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                    'previous_start' => $now->copy()->subYear()->startOfYear(),
                    'previous_end' => $now->copy()->subYear()->endOfYear()
                ];
                
            case 'all':
            default:
                return [
                    'start' => Carbon::create(2000, 1, 1)->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'previous_start' => null,
                    'previous_end' => null
                ];
        }
    }

    // ==================== HELPER METHODS ====================

    /**
     * Calculate percentage change between two values
     */
    protected function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Get human readable period label
     */
    protected function getPeriodLabel(string $filter): string
    {
        return match($filter) {
            'today' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            'year' => 'This Year',
            'all' => 'All Time',
            default => 'Current Period'
        };
    }

    /**
     * Get comparison text for the period
     */
    protected function getComparisonText(string $filter): string
    {
        return match($filter) {
            'today' => 'vs Yesterday',
            'week' => 'vs Last Week',
            'month' => 'vs Last Month',
            'year' => 'vs Last Year',
            default => 'vs Previous Period'
        };
    }

    /**
     * Generate error response when dashboard fails
     */
    protected function getErrorResponse(string $filter, string $message): array
    {
        return [
            'filter' => $filter,
            'periodLabel' => $this->getPeriodLabel($filter),
            'comparisonText' => 'No comparison available',
            'hasComparison' => false,
            'lastUpdated' => now(),
            'status' => 'error',
            'errorMessage' => $message,

            // Zero out all counts
            'totalUsers' => 0,
            'newUsersCount' => 0,
            'activeUsersCount' => 0,
            'totalEmployees' => 0,
            'newEmployeesCount' => 0,
            'activeEmployeesCount' => 0,
            'totalCustomers' => 0,
            'newCustomersCount' => 0,
            'activeCustomersCount' => 0,
            'salesCount' => 0,
            'totalRevenue' => 0,
            'averageOrderValue' => 0,
            'salesPercentChange' => 0,
            'revenuePercentChange' => 0,

            // Empty chart data
            'salesTrend' => [
                'labels' => [],
                'data' => [],
                'counts' => []
            ],
            'topProducts' => [],
            'recentSales' => [],
        ];
    }

    /**
     * Clear dashboard cache for specific filter
     */
    public function clearCache(Request $request)
    {
        try {
            $filter = $request->input('filter', 'all');
            $userId = Auth::id();
            
            if ($filter === 'all') {
                // Clear all dashboard caches for this user
                $filters = ['today', 'week', 'month', 'year', 'all'];
                foreach ($filters as $f) {
                    Cache::forget("dashboard_{$f}_user_{$userId}");
                }
                
                // Clear count caches
                Cache::forget('total_users_count');
                Cache::forget('total_employees_count');
                Cache::forget('total_customers_count');
                
            } else {
                Cache::forget("dashboard_{$filter}_user_{$userId}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Dashboard cache cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Cache clear error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache'
            ], 500);
        }
    }
}