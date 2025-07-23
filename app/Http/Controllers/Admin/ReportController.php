<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VinylMaster;
use App\Models\VinylSec;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\Wantlist;
use App\Models\ProductView;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        return Inertia::render('Admin/Reports/Dashboard', [
            'stats' => $this->getDashboardStats()
        ]);
    }

    /**
     * Vinyl/Records report with sales data and inventory
     */
    public function vinyls(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        // Get vinyl sales data from both online orders and POS sales
        $vinylSalesData = $this->getVinylSalesData($dateRange);

        // Get inventory data
        $inventoryData = $this->getVinylInventoryData();

        // Get top selling vinyls
        $topSellingVinyls = $this->getTopSellingVinyls($dateRange);

        // Get sales trends
        $salesTrends = $this->getVinylSalesTrends($dateRange);

        return Inertia::render('Admin/Reports/Vinyls', [
            'salesData' => $vinylSalesData,
            'inventoryData' => $inventoryData,
            'topSellingVinyls' => $topSellingVinyls,
            'salesTrends' => $salesTrends,
            'dateRange' => $dateRange,
            'filters' => $request->only(['start_date', 'end_date', 'category', 'artist'])
        ]);
    }

    /**
     * Cart items report showing abandoned cart analysis
     */
    public function cartItems(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        // Get abandoned carts data
        $abandonedCarts = Cart::with(['user', 'items.product'])
            ->abandoned()
            ->whereBetween('updated_at', $dateRange)
            ->paginate(20);

        // Get cart abandonment statistics
        $cartStats = $this->getCartAbandonmentStats($dateRange);

        // Get most abandoned products
        $mostAbandonedProducts = $this->getMostAbandonedProducts($dateRange);

        // Get cart recovery potential
        $recoveryPotential = $this->getCartRecoveryPotential($dateRange);

        return Inertia::render('Admin/Reports/CartItems', [
            'abandonedCarts' => $abandonedCarts,
            'cartStats' => $cartStats,
            'mostAbandonedProducts' => $mostAbandonedProducts,
            'recoveryPotential' => $recoveryPotential,
            'dateRange' => $dateRange,
            'filters' => $request->only(['start_date', 'end_date'])
        ]);
    }

    /**
     * Wishlist report with customer preferences tracking
     */
    public function wishlist(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        // Get wishlist statistics
        $wishlistStats = $this->getWishlistStats($dateRange);

        // Get most wishlisted products
        $mostWishlistedProducts = $this->getMostWishlistedProducts($dateRange);

        // Get wishlist trends
        $wishlistTrends = $this->getWishlistTrends($dateRange);

        // Get customer wishlist behavior
        $customerBehavior = $this->getWishlistCustomerBehavior($dateRange);

        return Inertia::render('Admin/Reports/Wishlist', [
            'wishlistStats' => $wishlistStats,
            'mostWishlistedProducts' => $mostWishlistedProducts,
            'wishlistTrends' => $wishlistTrends,
            'customerBehavior' => $customerBehavior,
            'dateRange' => $dateRange,
            'filters' => $request->only(['start_date', 'end_date'])
        ]);
    }

    /**
     * Wantlist report for customer demand analysis
     */
    public function wantlist(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        // Get wantlist statistics
        $wantlistStats = $this->getWantlistStats($dateRange);

        // Get most wanted products
        $mostWantedProducts = $this->getMostWantedProducts($dateRange);

        // Get demand trends
        $demandTrends = $this->getWantlistTrends($dateRange);

        // Get customer demand behavior
        $demandBehavior = $this->getWantlistCustomerBehavior($dateRange);

        return Inertia::render('Admin/Reports/Wantlist', [
            'wantlistStats' => $wantlistStats,
            'mostWantedProducts' => $mostWantedProducts,
            'demandTrends' => $demandTrends,
            'demandBehavior' => $demandBehavior,
            'dateRange' => $dateRange,
            'filters' => $request->only(['start_date', 'end_date'])
        ]);
    }

    /**
     * Product views/clicks tracking report
     */
    public function productViews(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        // Get product views statistics
        $viewsStats = $this->getProductViewsStats($dateRange);

        // Get most viewed products
        $mostViewedProducts = $this->getMostViewedProducts($dateRange);

        // Get views trends
        $viewsTrends = $this->getProductViewsTrends($dateRange);

        // Get conversion rates (views to purchases)
        $conversionRates = $this->getViewsToSalesConversion($dateRange);

        return Inertia::render('Admin/Reports/ProductViews', [
            'viewsStats' => $viewsStats,
            'mostViewedProducts' => $mostViewedProducts,
            'viewsTrends' => $viewsTrends,
            'conversionRates' => $conversionRates,
            'dateRange' => $dateRange,
            'filters' => $request->only(['start_date', 'end_date'])
        ]);
    }

    /**
     * Supplier inventory report showing purchase cost, selling price, and supplier information
     */
    public function supplierInventory(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $supplierId = $request->input('supplier_id');

        // Get all suppliers for the filter dropdown
        $suppliers = DB::table('suppliers')->select('id', 'name')->orderBy('name')->get();

        // Get inventory items with supplier information
        $query = DB::table('vinyl_secs')
            ->join('vinyl_masters', 'vinyl_secs.vinyl_master_id', '=', 'vinyl_masters.id')
            ->leftJoin('suppliers', 'vinyl_secs.supplier_id', '=', 'suppliers.id')
            ->leftJoin(DB::raw('(SELECT vinyl_sec_id, SUM(quantity) as total_sold FROM pos_sale_items GROUP BY vinyl_sec_id) as pos_sales'),
                'pos_sales.vinyl_sec_id', '=', 'vinyl_secs.id')
            ->leftJoin('products', function($join) {
                $join->on('products.productable_id', '=', 'vinyl_secs.id')
                    ->where('products.productable_type', '=', 'App\\Models\\VinylSec');
            })
            ->leftJoin(DB::raw('(SELECT product_id, SUM(quantity) as total_sold FROM order_items GROUP BY product_id) as online_sales'),
                'online_sales.product_id', '=', 'products.id')
            ->leftJoin('pivot_artist_vinyl_master', 'vinyl_masters.id', '=', 'pivot_artist_vinyl_master.vinyl_master_id')
            ->leftJoin('artists', 'pivot_artist_vinyl_master.artist_id', '=', 'artists.id');

        // Apply supplier filter if provided
        if ($supplierId) {
            $query->where('vinyl_secs.supplier_id', $supplierId);
        }

        // Get paginated inventory items
        $inventoryItems = $query->selectRaw('
                vinyl_secs.id,
                vinyl_masters.title,
                vinyl_masters.cover_image,
                GROUP_CONCAT(DISTINCT artists.name SEPARATOR \', \') as artist,
                suppliers.name as supplier_name,
                vinyl_secs.cost_price as cost,
                vinyl_secs.price,
                vinyl_secs.stock,
                COALESCE(pos_sales.total_sold, 0) + COALESCE(online_sales.total_sold, 0) as total_sold,
                CASE
                    WHEN vinyl_secs.cost_price > 0 AND vinyl_secs.price > vinyl_secs.cost_price
                    THEN ((vinyl_secs.price - vinyl_secs.cost_price) / vinyl_secs.cost_price) * 100
                    ELSE 0
                END as margin_percentage
            ')
            ->groupBy(
                'vinyl_secs.id',
                'vinyl_masters.title',
                'vinyl_masters.cover_image',
                'suppliers.name',
                'vinyl_secs.cost_price',
                'vinyl_secs.price',
                'vinyl_secs.stock',
                'pos_sales.total_sold',
                'online_sales.total_sold'
            )
            ->orderBy('supplier_name')
            ->orderBy('vinyl_masters.title')
            ->paginate(20);

        // Get supplier summary data
        $supplierSummary = DB::table('vinyl_secs')
            ->leftJoin('suppliers', 'vinyl_secs.supplier_id', '=', 'suppliers.id')
            ->selectRaw('
                suppliers.id,
                COALESCE(suppliers.name, "Sem Fornecedor") as name,
                COUNT(vinyl_secs.id) as total_items,
                SUM(vinyl_secs.cost_price * vinyl_secs.stock) as total_cost,
                SUM(vinyl_secs.price * vinyl_secs.stock) as total_price,
                CASE
                    WHEN SUM(vinyl_secs.cost_price * vinyl_secs.stock) > 0
                    THEN ((SUM(vinyl_secs.price * vinyl_secs.stock) - SUM(vinyl_secs.cost_price * vinyl_secs.stock)) / SUM(vinyl_secs.cost_price * vinyl_secs.stock)) * 100
                    ELSE 0
                END as margin_percentage
            ')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderBy('name')
            ->get();

        // Handle export request
        if ($request->has('export')) {
            return $this->exportSupplierInventory($query, $dateRange);
        }

        return Inertia::render('Admin/Reports/SupplierInventory', [
            'inventoryItems' => $inventoryItems,
            'supplierSummary' => $supplierSummary,
            'suppliers' => $suppliers,
            'dateRange' => $dateRange,
            'filters' => $request->only(['start_date', 'end_date', 'supplier_id'])
        ]);
    }

    /**
     * Export supplier inventory data to CSV
     */
    private function exportSupplierInventory($query, $dateRange)
    {
        $filename = 'supplier_inventory_' . Carbon::now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Título',
                'Artista',
                'Fornecedor',
                'Custo',
                'Preço',
                'Estoque',
                'Vendidos',
                'Margem (%)'
            ]);

            // Get all records without pagination
            $records = $query->selectRaw('
                    vinyl_secs.id,
                    vinyl_masters.title,
                    GROUP_CONCAT(DISTINCT artists.name SEPARATOR \', \') as artist,
                    suppliers.name as supplier_name,
                    vinyl_secs.cost_price as cost,
                    vinyl_secs.price,
                    vinyl_secs.stock,
                    COALESCE(pos_sales.total_sold, 0) + COALESCE(online_sales.total_sold, 0) as total_sold,
                    CASE
                        WHEN vinyl_secs.cost_price > 0 AND vinyl_secs.price > vinyl_secs.cost_price
                        THEN ((vinyl_secs.price - vinyl_secs.cost_price) / vinyl_secs.cost_price) * 100
                        ELSE 0
                    END as margin_percentage
                ')
                ->groupBy(
                    'vinyl_secs.id',
                    'vinyl_masters.title',
                    'suppliers.name',
                    'vinyl_secs.cost_price',
                    'vinyl_secs.price',
                    'vinyl_secs.stock',
                    'pos_sales.total_sold',
                    'online_sales.total_sold'
                )
                ->get();

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->id,
                    $record->title,
                    $record->artist,
                    $record->supplier_name ?? 'Sem Fornecedor',
                    number_format($record->cost, 2, ',', '.'),
                    number_format($record->price, 2, ',', '.'),
                    $record->stock,
                    $record->total_sold,
                    number_format($record->margin_percentage, 2, ',', '.')
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'total_vinyls' => VinylSec::count(),
            'total_orders' => Order::count(),
            'total_pos_sales' => PosSale::count(),
            'abandoned_carts' => Cart::abandoned()->count(),
            'wishlist_items' => Wishlist::count(),
            'wantlist_items' => Wantlist::count(),
            'product_views_today' => ProductView::whereDate('viewed_at', $today)->count(),
            'revenue_this_month' => Order::whereBetween('created_at', [$thisMonth, now()])
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'pos_revenue_this_month' => PosSale::whereBetween('created_at', [$thisMonth, now()])
                ->sum('total'),
        ];
    }

    /**
     * Get vinyl sales data combining online orders and POS sales
     */
    private function getVinylSalesData($dateRange)
    {
        // Online sales through orders
        $onlineSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.productable_type', 'App\\Models\\VinylSec')
            ->whereBetween('orders.created_at', $dateRange)
            ->where('orders.payment_status', 'paid')
            ->selectRaw('
                SUM(order_items.quantity) as total_quantity,
                SUM(order_items.item_total) as total_revenue,
                COUNT(DISTINCT orders.id) as total_orders
            ')
            ->first();

        // POS sales
        $posSales = DB::table('pos_sale_items')
            ->join('pos_sales', 'pos_sale_items.pos_sale_id', '=', 'pos_sales.id')
            ->whereBetween('pos_sales.created_at', $dateRange)
            ->selectRaw('
                SUM(pos_sale_items.quantity) as total_quantity,
                SUM(pos_sale_items.item_total) as total_revenue,
                COUNT(DISTINCT pos_sales.id) as total_sales
            ')
            ->first();

        return [
            'online' => [
                'quantity' => $onlineSales->total_quantity ?? 0,
                'revenue' => $onlineSales->total_revenue ?? 0,
                'orders' => $onlineSales->total_orders ?? 0,
            ],
            'pos' => [
                'quantity' => $posSales->total_quantity ?? 0,
                'revenue' => $posSales->total_revenue ?? 0,
                'sales' => $posSales->total_sales ?? 0,
            ],
            'total' => [
                'quantity' => ($onlineSales->total_quantity ?? 0) + ($posSales->total_quantity ?? 0),
                'revenue' => ($onlineSales->total_revenue ?? 0) + ($posSales->total_revenue ?? 0),
            ]
        ];
    }

    /**
     * Get vinyl inventory data
     */
    private function getVinylInventoryData()
    {
        return VinylSec::with(['vinylMaster.artists'])
            ->selectRaw('
                vinyl_secs.*,
                CASE
                    WHEN stock = 0 THEN "out_of_stock"
                    WHEN stock <= 5 THEN "low_stock"
                    ELSE "in_stock"
                END as stock_status
            ')
            ->orderBy('stock', 'asc')
            ->limit(50)
            ->get()
            ->groupBy('stock_status');
    }

    /**
     * Get top selling vinyls
     */
    private function getTopSellingVinyls($dateRange)
    {
        // Combine online and POS sales
        $onlineTopSellers = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('vinyl_secs', 'products.productable_id', '=', 'vinyl_secs.id')
            ->join('vinyl_masters', 'vinyl_secs.vinyl_master_id', '=', 'vinyl_masters.id')
            ->where('products.productable_type', 'App\\Models\\VinylSec')
            ->whereBetween('orders.created_at', $dateRange)
            ->where('orders.payment_status', 'paid')
            ->selectRaw('
                vinyl_masters.title,
                vinyl_masters.cover_image,
                SUM(order_items.quantity) as total_sold,
                SUM(order_items.item_total) as total_revenue,
                "online" as source
            ')
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title', 'vinyl_masters.cover_image')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $posTopSellers = DB::table('pos_sale_items')
            ->join('pos_sales', 'pos_sale_items.pos_sale_id', '=', 'pos_sales.id')
            ->join('vinyl_secs', 'pos_sale_items.vinyl_sec_id', '=', 'vinyl_secs.id')
            ->join('vinyl_masters', 'vinyl_secs.vinyl_master_id', '=', 'vinyl_masters.id')
            ->whereBetween('pos_sales.created_at', $dateRange)
            ->selectRaw('
                vinyl_masters.title,
                vinyl_masters.cover_image,
                SUM(pos_sale_items.quantity) as total_sold,
                SUM(pos_sale_items.item_total) as total_revenue,
                "pos" as source
            ')
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title', 'vinyl_masters.cover_image')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return [
            'online' => $onlineTopSellers,
            'pos' => $posTopSellers
        ];
    }

    /**
     * Get vinyl sales trends over time
     */
    private function getVinylSalesTrends($dateRange)
    {
        $interval = $this->getTrendInterval($dateRange);

        $onlineTrends = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.productable_type', 'App\\Models\\VinylSec')
            ->whereBetween('orders.created_at', $dateRange)
            ->where('orders.payment_status', 'paid')
            ->selectRaw("
                DATE_FORMAT(orders.created_at, '{$interval}') as period,
                SUM(order_items.quantity) as quantity,
                SUM(order_items.item_total) as revenue
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $posTrends = DB::table('pos_sale_items')
            ->join('pos_sales', 'pos_sale_items.pos_sale_id', '=', 'pos_sales.id')
            ->whereBetween('pos_sales.created_at', $dateRange)
            ->selectRaw("
                DATE_FORMAT(pos_sales.created_at, '{$interval}') as period,
                SUM(pos_sale_items.quantity) as quantity,
                SUM(pos_sale_items.item_total) as revenue
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'online' => $onlineTrends,
            'pos' => $posTrends
        ];
    }

    /**
     * Get cart abandonment statistics
     */
    private function getCartAbandonmentStats($dateRange)
    {
        $totalCarts = Cart::whereBetween('updated_at', $dateRange)->count();
        $abandonedCarts = Cart::abandoned()
            ->whereBetween('updated_at', $dateRange)
            ->count();

        $abandonmentRate = $totalCarts > 0 ? ($abandonedCarts / $totalCarts) * 100 : 0;

        $averageCartValue = CartItem::join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->join('vinyl_secs', 'products.productable_id', '=', 'vinyl_secs.id')
            ->where('carts.status', 'active')
            ->whereBetween('carts.updated_at', $dateRange)
            ->selectRaw('AVG(vinyl_secs.price * cart_items.quantity) as avg_value')
            ->value('avg_value') ?? 0;

        return [
            'total_carts' => $totalCarts,
            'abandoned_carts' => $abandonedCarts,
            'abandonment_rate' => round($abandonmentRate, 2),
            'average_cart_value' => round($averageCartValue, 2),
        ];
    }

    /**
     * Get most abandoned products
     */
    private function getMostAbandonedProducts($dateRange)
    {
        return CartItem::join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->join('vinyl_secs', 'products.productable_id', '=', 'vinyl_secs.id')
            ->join('vinyl_masters', 'vinyl_secs.vinyl_master_id', '=', 'vinyl_masters.id')
            ->where('carts.status', 'active')
            ->where('carts.updated_at', '<', now()->subDays(7))
            ->whereBetween('carts.updated_at', $dateRange)
            ->selectRaw('
                vinyl_masters.title,
                vinyl_masters.cover_image,
                COUNT(*) as abandonment_count,
                SUM(cart_items.quantity) as total_quantity,
                AVG(vinyl_secs.price) as avg_price
            ')
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title', 'vinyl_masters.cover_image')
            ->orderByDesc('abandonment_count')
            ->limit(10)
            ->get();
    }

    /**
     * Get cart recovery potential
     */
    private function getCartRecoveryPotential($dateRange)
    {
        return CartItem::join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->join('vinyl_secs', 'products.productable_id', '=', 'vinyl_secs.id')
            ->where('carts.status', 'active')
            ->where('carts.updated_at', '<', now()->subDays(7))
            ->whereBetween('carts.updated_at', $dateRange)
            ->selectRaw('
                SUM(vinyl_secs.price * cart_items.quantity) as potential_revenue,
                COUNT(DISTINCT carts.id) as recoverable_carts,
                SUM(cart_items.quantity) as total_items
            ')
            ->first();
    }

    /**
     * Get wishlist statistics
     */
    private function getWishlistStats($dateRange)
    {
        $totalWishlists = Wishlist::whereBetween('created_at', $dateRange)->count();
        $uniqueCustomers = Wishlist::whereBetween('created_at', $dateRange)
            ->distinct('user_id')
            ->count();

        $averageItemsPerWishlist = $uniqueCustomers > 0 ? $totalWishlists / $uniqueCustomers : 0;

        return [
            'total_wishlist_items' => $totalWishlists,
            'unique_customers' => $uniqueCustomers,
            'average_items_per_customer' => round($averageItemsPerWishlist, 2),
        ];
    }

    /**
     * Get most wishlisted products
     */
    private function getMostWishlistedProducts($dateRange)
    {
        return Wishlist::join('products', 'wishlists.product_id', '=', 'products.id')
            ->join('vinyl_secs', 'products.productable_id', '=', 'vinyl_secs.id')
            ->join('vinyl_masters', 'vinyl_secs.vinyl_master_id', '=', 'vinyl_masters.id')
            ->whereBetween('wishlists.created_at', $dateRange)
            ->selectRaw('
                vinyl_masters.title,
                vinyl_masters.cover_image,
                COUNT(*) as wishlist_count,
                vinyl_secs.price,
                vinyl_secs.stock
            ')
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title', 'vinyl_masters.cover_image', 'vinyl_secs.price', 'vinyl_secs.stock')
            ->orderByDesc('wishlist_count')
            ->limit(10)
            ->get();
    }

    /**
     * Get wishlist trends
     */
    private function getWishlistTrends($dateRange)
    {
        $interval = $this->getTrendInterval($dateRange);

        return Wishlist::whereBetween('created_at', $dateRange)
            ->selectRaw("
                DATE_FORMAT(created_at, '{$interval}') as period,
                COUNT(*) as wishlist_additions
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get wishlist customer behavior
     */
    private function getWishlistCustomerBehavior($dateRange)
    {
        return DB::table('wishlists')
            ->join('users', 'wishlists.user_id', '=', 'users.id')
            ->whereBetween('wishlists.created_at', $dateRange)
            ->selectRaw('
                users.name,
                users.email,
                COUNT(*) as items_wishlisted,
                MAX(wishlists.created_at) as last_wishlist_activity
            ')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('items_wishlisted')
            ->limit(20)
            ->get();
    }

    /**
     * Get wantlist statistics
     */
    private function getWantlistStats($dateRange)
    {
        $totalWantlists = Wantlist::whereBetween('created_at', $dateRange)->count();
        $uniqueCustomers = Wantlist::whereBetween('created_at', $dateRange)
            ->distinct('user_id')
            ->count();

        $averageItemsPerWantlist = $uniqueCustomers > 0 ? $totalWantlists / $uniqueCustomers : 0;

        return [
            'total_wantlist_items' => $totalWantlists,
            'unique_customers' => $uniqueCustomers,
            'average_items_per_customer' => round($averageItemsPerWantlist, 2),
        ];
    }

    /**
     * Get most wanted products
     */
    private function getMostWantedProducts($dateRange)
    {
        return Wantlist::join('products', 'wantlists.product_id', '=', 'products.id')
            ->join('vinyl_secs', 'products.productable_id', '=', 'vinyl_secs.id')
            ->join('vinyl_masters', 'vinyl_secs.vinyl_master_id', '=', 'vinyl_masters.id')
            ->whereBetween('wantlists.created_at', $dateRange)
            ->selectRaw('
                vinyl_masters.title,
                vinyl_masters.cover_image,
                COUNT(*) as demand_count,
                vinyl_secs.price,
                vinyl_secs.stock,
                CASE
                    WHEN vinyl_secs.stock = 0 THEN "out_of_stock"
                    WHEN vinyl_secs.stock <= 5 THEN "low_stock"
                    ELSE "in_stock"
                END as availability_status
            ')
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title', 'vinyl_masters.cover_image', 'vinyl_secs.price', 'vinyl_secs.stock')
            ->orderByDesc('demand_count')
            ->limit(10)
            ->get();
    }

    /**
     * Get wantlist trends
     */
    private function getWantlistTrends($dateRange)
    {
        $interval = $this->getTrendInterval($dateRange);

        return Wantlist::whereBetween('created_at', $dateRange)
            ->selectRaw("
                DATE_FORMAT(created_at, '{$interval}') as period,
                COUNT(*) as wantlist_additions
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get wantlist customer behavior
     */
    private function getWantlistCustomerBehavior($dateRange)
    {
        return DB::table('wantlists')
            ->join('users', 'wantlists.user_id', '=', 'users.id')
            ->whereBetween('wantlists.created_at', $dateRange)
            ->selectRaw('
                users.name,
                users.email,
                COUNT(*) as items_wanted,
                MAX(wantlists.created_at) as last_wantlist_activity
            ')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('items_wanted')
            ->limit(20)
            ->get();
    }

    /**
     * Get product views statistics
     */
    private function getProductViewsStats($dateRange)
    {
        $totalViews = ProductView::whereBetween('viewed_at', $dateRange)->count();
        $uniqueViews = ProductView::whereBetween('viewed_at', $dateRange)
            ->selectRaw('COUNT(DISTINCT COALESCE(user_id, ip_address)) as unique_count')
            ->value('unique_count');

        $averageViewsPerProduct = ProductView::whereBetween('viewed_at', $dateRange)
            ->selectRaw('COUNT(*) / COUNT(DISTINCT product_id) as avg_views')
            ->value('avg_views') ?? 0;

        return [
            'total_views' => $totalViews,
            'unique_views' => $uniqueViews,
            'average_views_per_product' => round($averageViewsPerProduct, 2),
        ];
    }

    /**
     * Get most viewed products
     */
    private function getMostViewedProducts($dateRange)
    {
        return ProductView::join('products', 'product_views.product_id', '=', 'products.id')
            ->join('vinyl_secs', 'products.productable_id', '=', 'vinyl_secs.id')
            ->join('vinyl_masters', 'vinyl_secs.vinyl_master_id', '=', 'vinyl_masters.id')
            ->whereBetween('product_views.viewed_at', $dateRange)
            ->selectRaw('
                vinyl_masters.title,
                vinyl_masters.cover_image,
                COUNT(*) as total_views,
                COUNT(DISTINCT COALESCE(product_views.user_id, product_views.ip_address)) as unique_views,
                vinyl_secs.price,
                vinyl_secs.stock
            ')
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title', 'vinyl_masters.cover_image', 'vinyl_secs.price', 'vinyl_secs.stock')
            ->orderByDesc('total_views')
            ->limit(10)
            ->get();
    }

    /**
     * Get product views trends
     */
    private function getProductViewsTrends($dateRange)
    {
        $interval = $this->getTrendInterval($dateRange);

        return ProductView::whereBetween('viewed_at', $dateRange)
            ->selectRaw("
                DATE_FORMAT(viewed_at, '{$interval}') as period,
                COUNT(*) as total_views,
                COUNT(DISTINCT COALESCE(user_id, ip_address)) as unique_views
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get views to sales conversion rates
     */
    private function getViewsToSalesConversion($dateRange)
    {
        // Get products with views and their sales
        $viewsAndSales = DB::table('product_views')
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->leftJoin('order_items', function($join) use ($dateRange) {
                $join->on('products.id', '=', 'order_items.product_id')
                     ->join('orders', 'order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.created_at', $dateRange)
                     ->where('orders.payment_status', 'paid');
            })
            ->whereBetween('product_views.viewed_at', $dateRange)
            ->selectRaw('
                products.id,
                COUNT(DISTINCT product_views.id) as total_views,
                COUNT(DISTINCT COALESCE(product_views.user_id, product_views.ip_address)) as unique_views,
                COALESCE(SUM(order_items.quantity), 0) as total_sales
            ')
            ->groupBy('products.id')
            ->having('total_views', '>', 0)
            ->get();

        $totalViews = $viewsAndSales->sum('total_views');
        $totalSales = $viewsAndSales->sum('total_sales');
        $conversionRate = $totalViews > 0 ? ($totalSales / $totalViews) * 100 : 0;

        return [
            'overall_conversion_rate' => round($conversionRate, 2),
            'total_views' => $totalViews,
            'total_sales' => $totalSales,
            'products_with_conversions' => $viewsAndSales->where('total_sales', '>', 0)->count(),
        ];
    }

    /**
     * Get date range from request or default to last 30 days
     */
    private function getDateRange(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->startOfDay());
        $endDate = $request->input('end_date', Carbon::now()->endOfDay());

        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate)->startOfDay();
        }
        if (is_string($endDate)) {
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        return [$startDate, $endDate];
    }

    /**
     * Get appropriate interval for trends based on date range
     */
    private function getTrendInterval($dateRange)
    {
        $days = Carbon::parse($dateRange[0])->diffInDays(Carbon::parse($dateRange[1]));

        if ($days <= 7) {
            return '%Y-%m-%d'; // Daily
        } elseif ($days <= 60) {
            return '%Y-%u'; // Weekly
        } else {
            return '%Y-%m'; // Monthly
        }
    }
}
