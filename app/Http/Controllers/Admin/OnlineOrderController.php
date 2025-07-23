<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OnlineOrderController extends Controller
{
    /**
     * Display a listing of online orders.
     */
    public function index(Request $request): Response
    {
        $query = Order::with(['user', 'items.product'])
            ->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('payment_status')) {
            $query->byPaymentStatus($request->payment_status);
        }

        if ($request->filled('customer_id')) {
            $query->byCustomer($request->customer_id);
        }

        // Handle date range filtering
        if ($request->filled('date_range')) {
            $query->byDateRange($this->getDateRangeValues($request->date_range));
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Get filter options
        $statusOptions = [
            ['value' => '', 'label' => 'Todos os Status'],
            ['value' => 'pending', 'label' => 'Pendente'],
            ['value' => 'processing', 'label' => 'Processando'],
            ['value' => 'shipped', 'label' => 'Enviado'],
            ['value' => 'delivered', 'label' => 'Entregue'],
            ['value' => 'cancelled', 'label' => 'Cancelado'],
        ];

        $paymentStatusOptions = [
            ['value' => '', 'label' => 'Todos os Status de Pagamento'],
            ['value' => 'pending', 'label' => 'Pendente'],
            ['value' => 'paid', 'label' => 'Pago'],
            ['value' => 'failed', 'label' => 'Falhou'],
            ['value' => 'refunded', 'label' => 'Reembolsado'],
        ];

        // Get customers for filter (only those with orders)
        $customers = User::whereHas('orders')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->name . ' (' . $user->email . ')'
                ];
            })
            ->prepend(['value' => '', 'label' => 'Todos os Clientes']);

        return Inertia::render('Admin/Orders/Online/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'payment_status', 'customer_id', 'date_range', 'start_date', 'end_date', 'search']),
            'statusOptions' => $statusOptions,
            'paymentStatusOptions' => $paymentStatusOptions,
            'customers' => $customers,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        $order->load([
            'user',
            'items.product'
        ]);

        // Load status history if the table exists
        try {
            $order->load('statusHistory.changedBy');
        } catch (\Exception $e) {
            // Status history table doesn't exist yet, skip loading
        }

        return Inertia::render('Admin/Orders/Online/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Export orders to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Order::with(['user', 'items.product']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('payment_status')) {
            $query->byPaymentStatus($request->payment_status);
        }

        if ($request->filled('customer_id')) {
            $query->byCustomer($request->customer_id);
        }

        // Handle date range filtering
        if ($request->filled('date_range')) {
            $query->byDateRange($this->getDateRangeValues($request->date_range));
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $orders = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pedidos_online_' . date('Y-m-d_H-i-s') . '.csv"',
        ];

        return response()->stream(function () use ($orders) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'Número do Pedido',
                'Cliente',
                'Email',
                'Status',
                'Status Pagamento',
                'Subtotal',
                'Taxa',
                'Frete',
                'Desconto',
                'Total',
                'Método Pagamento',
                'Data Criação',
                'Data Processamento',
                'Data Envio',
                'Data Entrega',
                'Itens',
            ]);

            // Add order data
            foreach ($orders as $order) {
                $items = $order->items->map(function ($item) {
                    return $item->product_name . ' (Qtd: ' . $item->quantity . ', Preço: R$ ' . number_format($item->price, 2, ',', '.') . ')';
                })->implode('; ');

                fputcsv($handle, [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_email,
                    $order->status_label,
                    $order->payment_status_label,
                    'R$ ' . number_format($order->subtotal, 2, ',', '.'),
                    'R$ ' . number_format($order->tax_amount, 2, ',', '.'),
                    'R$ ' . number_format($order->shipping_amount, 2, ',', '.'),
                    'R$ ' . number_format($order->discount_amount, 2, ',', '.'),
                    'R$ ' . number_format($order->total_amount, 2, ',', '.'),
                    $order->payment_method ?? 'N/A',
                    $order->created_at->format('d/m/Y H:i:s'),
                    $order->processed_at?->format('d/m/Y H:i:s') ?? 'N/A',
                    $order->shipped_at?->format('d/m/Y H:i:s') ?? 'N/A',
                    $order->delivered_at?->format('d/m/Y H:i:s') ?? 'N/A',
                    $items,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Get order statistics for dashboard.
     */
    public function stats(): array
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        $totalRevenue = Order::whereIn('status', ['delivered', 'shipped', 'processing'])
            ->sum('total_amount');

        $todayOrders = Order::whereDate('created_at', today())->count();
        $thisMonthOrders = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'processing_orders' => $processingOrders,
            'shipped_orders' => $shippedOrders,
            'delivered_orders' => $deliveredOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_revenue' => $totalRevenue,
            'today_orders' => $todayOrders,
            'this_month_orders' => $thisMonthOrders,
        ];
    }

    /**
     * Get date range values for predefined ranges.
     */
    private function getDateRangeValues(string $range): array
    {
        $now = now();

        return match($range) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'yesterday' => [$now->subDay()->startOfDay(), $now->subDay()->endOfDay()],
            'this_week' => [$now->startOfWeek(), $now->endOfWeek()],
            'last_week' => [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()],
            'this_month' => [$now->startOfMonth(), $now->endOfMonth()],
            'last_month' => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
            default => [null, null]
        };
    }
}
