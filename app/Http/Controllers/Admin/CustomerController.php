<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Wantlist;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * Display a listing of role 20 customers.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 20)
            ->withCount(['orders', 'addresses'])
            ->with(['orders' => function ($query) {
                $query->select('user_id', DB::raw('SUM(total_amount) as total_spent'))
                      ->groupBy('user_id');
            }]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('registration_date_from')) {
            $query->where('created_at', '>=', $request->registration_date_from);
        }

        if ($request->filled('registration_date_to')) {
            $query->where('created_at', '<=', $request->registration_date_to);
        }

        if ($request->filled('min_orders')) {
            $query->having('orders_count', '>=', $request->min_orders);
        }

        if ($request->filled('max_orders')) {
            $query->having('orders_count', '<=', $request->max_orders);
        }

        // Add total spent calculation
        $customers = $query->get()->map(function ($customer) {
            $totalSpent = $customer->orders()->sum('total_amount');
            $lastOrderAt = $customer->orders()->latest()->first()?->created_at;
            $wishlistCount = Wishlist::where('user_id', $customer->id)->count();
            $wantlistCount = Wantlist::where('user_id', $customer->id)->count();
            $cartItemsCount = CartItem::where('user_id', $customer->id)->count();

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'created_at' => $customer->created_at,
                'email_verified_at' => $customer->email_verified_at,
                'orders_count' => $customer->orders_count,
                'addresses_count' => $customer->addresses_count,
                'total_spent' => $totalSpent,
                'last_order_at' => $lastOrderAt,
                'status' => $customer->email_verified_at ? 'verified' : 'unverified',
                'wishlist_count' => $wishlistCount,
                'wantlist_count' => $wantlistCount,
                'cart_items_count' => $cartItemsCount,
                'last_activity' => $customer->last_login_at ?? $customer->updated_at,
            ];
        });

        // Apply total spent filters
        if ($request->filled('min_spent')) {
            $customers = $customers->filter(function ($customer) use ($request) {
                return $customer['total_spent'] >= $request->min_spent;
            });
        }

        if ($request->filled('max_spent')) {
            $customers = $customers->filter(function ($customer) use ($request) {
                return $customer['total_spent'] <= $request->max_spent;
            });
        }

        // Sort customers
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $customers = $customers->sortBy($sortBy, SORT_REGULAR, $sortOrder === 'desc');

        // Paginate manually
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $total = $customers->count();
        $customers = $customers->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only([
                'search', 'registration_date_from', 'registration_date_to',
                'min_orders', 'max_orders', 'min_spent', 'max_spent',
                'sort_by', 'sort_order'
            ]),
            'pagination' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
            'statistics' => $this->getCustomerStatistics(),
        ]);
    }

    /**
     * Display the specified customer.
     */
    public function show(User $customer)
    {
        // Ensure we're only showing role 20 customers
        if ($customer->role !== 20) {
            abort(404);
        }

        $customer->load([
            'orders' => function ($query) {
                $query->with('items.product')
                      ->orderBy('created_at', 'desc');
            },
            'addresses'
        ]);

        // Get customer wishlists, wantlists, and cart items
        $wishlists = Wishlist::where('user_id', $customer->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        $wantlists = Wantlist::where('user_id', $customer->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        $cartItems = CartItem::where('user_id', $customer->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate customer statistics
        $statistics = [
            'total_orders' => $customer->orders->count(),
            'total_spent' => $customer->orders->sum('total_amount'),
            'average_order_value' => $customer->orders->count() > 0
                ? $customer->orders->sum('total_amount') / $customer->orders->count()
                : 0,
            'first_order_date' => $customer->orders->min('created_at'),
            'last_order_date' => $customer->orders->max('created_at'),
            'orders_by_status' => $customer->orders->groupBy('status')->map->count(),
            'orders_by_month' => $customer->orders
                ->groupBy(function ($order) {
                    return $order->created_at->format('Y-m');
                })
                ->map->count()
                ->sortKeys(),
            'wishlist_count' => $wishlists->count(),
            'wantlist_count' => $wantlists->count(),
            'cart_items_count' => $cartItems->count(),
            'abandoned_cart_value' => $cartItems->sum(function ($item) {
                return $item->quantity * ($item->product->price ?? 0);
            }),
        ];

        // Get customer notes
        $notes = $this->getCustomerNotes($customer->id);

        // Get customer communication history
        $communications = $this->getCustomerCommunications($customer->id);

        return Inertia::render('Admin/Customers/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'created_at' => $customer->created_at,
                'email_verified_at' => $customer->email_verified_at,
                'last_login_at' => $customer->last_login_at,
                'orders' => $customer->orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'status_label' => $order->status_label,
                        'payment_status' => $order->payment_status,
                        'payment_status_label' => $order->payment_status_label,
                        'total_amount' => $order->total_amount,
                        'created_at' => $order->created_at,
                        'items_count' => $order->items->count(),
                        'items' => $order->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'total' => $item->total,
                            ];
                        }),
                    ];
                }),
                'addresses' => $customer->addresses,
                'wishlists' => $wishlists->map(function ($wishlist) {
                    return [
                        'id' => $wishlist->id,
                        'product_id' => $wishlist->product_id,
                        'product_name' => $wishlist->product->name ?? 'Produto não encontrado',
                        'created_at' => $wishlist->created_at,
                    ];
                }),
                'wantlists' => $wantlists->map(function ($wantlist) {
                    return [
                        'id' => $wantlist->id,
                        'product_id' => $wantlist->product_id,
                        'product_name' => $wantlist->product->name ?? 'Produto não encontrado',
                        'created_at' => $wantlist->created_at,
                    ];
                }),
                'cart_items' => $cartItems->map(function ($cartItem) {
                    return [
                        'id' => $cartItem->id,
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product->name ?? 'Produto não encontrado',
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->price ?? 0,
                        'total' => $cartItem->quantity * ($cartItem->product->price ?? 0),
                        'created_at' => $cartItem->created_at,
                        'updated_at' => $cartItem->updated_at,
                    ];
                }),
            ],
            'statistics' => $statistics,
            'notes' => $notes,
            'communications' => $communications,
        ]);
    }

    /**
     * Store a new customer note.
     */
    public function storeNote(Request $request, User $customer)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        // Store note in a simple JSON format in user table or create a notes table
        $this->addCustomerNote($customer->id, $request->note, auth()->id());

        return back()->with('success', 'Nota adicionada com sucesso.');
    }

    /**
     * Store a new customer communication record.
     */
    public function storeCommunication(Request $request, User $customer)
    {
        $request->validate([
            'type' => 'required|string|in:email,phone,whatsapp,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Store communication in a simple JSON format in user table
        $this->addCustomerCommunication(
            $customer->id,
            $request->type,
            $request->subject,
            $request->message,
            auth()->id()
        );

        return back()->with('success', 'Comunicação registrada com sucesso.');
    }

    /**
     * Update customer status.
     */
    public function updateStatus(Request $request, User $customer)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,blocked',
        ]);

        $customer->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status do cliente atualizado com sucesso.');
    }

    /**
     * Export customers data.
     */
    public function export(Request $request)
    {
        $customers = User::where('role', 20)
            ->withCount('orders')
            ->with(['orders' => function ($query) {
                $query->select('user_id', DB::raw('SUM(total_amount) as total_spent'))
                      ->groupBy('user_id');
            }])
            ->get()
            ->map(function ($customer) {
                $totalSpent = $customer->orders()->sum('total_amount');
                $lastOrderAt = $customer->orders()->latest()->first()?->created_at;

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
                    'email_verified_at' => $customer->email_verified_at ? $customer->email_verified_at->format('Y-m-d H:i:s') : null,
                    'orders_count' => $customer->orders_count,
                    'total_spent' => $totalSpent,
                    'last_order_at' => $lastOrderAt ? $lastOrderAt->format('Y-m-d H:i:s') : null,
                    'status' => $customer->email_verified_at ? 'verified' : 'unverified',
                ];
            });

        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers.csv"',
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, [
                'ID', 'Nome', 'Email', 'Data de Registro', 'Email Verificado',
                'Total de Pedidos', 'Total Gasto', 'Último Pedido', 'Status'
            ]);

            // Add rows
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer['id'],
                    $customer['name'],
                    $customer['email'],
                    $customer['created_at'],
                    $customer['email_verified_at'],
                    $customer['orders_count'],
                    $customer['total_spent'],
                    $customer['last_order_at'],
                    $customer['status'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get customer statistics for the index page.
     */
    private function getCustomerStatistics()
    {
        $totalCustomers = User::where('role', 20)->count();
        $verifiedCustomers = User::where('role', 20)->whereNotNull('email_verified_at')->count();
        $customersWithOrders = User::where('role', 20)->has('orders')->count();

        $totalRevenue = Order::whereHas('user', function ($query) {
            $query->where('role', 20);
        })->sum('total_amount');

        $averageOrderValue = Order::whereHas('user', function ($query) {
            $query->where('role', 20);
        })->avg('total_amount');

        $newCustomersThisMonth = User::where('role', 20)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        $newCustomersLastMonth = User::where('role', 20)
            ->whereBetween('created_at', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ])
            ->count();

        $customerGrowthRate = $newCustomersLastMonth > 0
            ? (($newCustomersThisMonth - $newCustomersLastMonth) / $newCustomersLastMonth) * 100
            : 0;

        $activeCustomers = User::where('role', 20)
            ->whereHas('orders', function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subMonths(3));
            })
            ->count();

        return [
            'total_customers' => $totalCustomers,
            'verified_customers' => $verifiedCustomers,
            'customers_with_orders' => $customersWithOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'new_customers_this_month' => $newCustomersThisMonth,
            'new_customers_last_month' => $newCustomersLastMonth,
            'customer_growth_rate' => $customerGrowthRate,
            'active_customers' => $activeCustomers,
            'active_customers_rate' => $totalCustomers > 0 ? ($activeCustomers / $totalCustomers) * 100 : 0,
            'verification_rate' => $totalCustomers > 0 ? ($verifiedCustomers / $totalCustomers) * 100 : 0,
        ];
    }

    /**
     * Get customer notes.
     */
    private function getCustomerNotes($customerId)
    {
        // For now, we'll store notes in a simple way
        // In a production system, you'd want a proper notes table
        $customer = User::find($customerId);
        $notes = json_decode($customer->notes ?? '[]', true);

        return collect($notes)->map(function ($note) {
            return [
                'id' => $note['id'] ?? uniqid(),
                'note' => $note['note'],
                'created_by' => $note['created_by'],
                'created_by_name' => User::find($note['created_by'])?->name ?? 'Unknown',
                'created_at' => $note['created_at'],
            ];
        })->sortByDesc('created_at')->values();
    }

    /**
     * Add a customer note.
     */
    private function addCustomerNote($customerId, $note, $createdBy)
    {
        $customer = User::find($customerId);
        $notes = json_decode($customer->notes ?? '[]', true);

        $notes[] = [
            'id' => uniqid(),
            'note' => $note,
            'created_by' => $createdBy,
            'created_at' => now()->toISOString(),
        ];

        $customer->update(['notes' => json_encode($notes)]);
    }

    /**
     * Get customer communications.
     */
    private function getCustomerCommunications($customerId)
    {
        $customer = User::find($customerId);
        $communications = json_decode($customer->communications ?? '[]', true);

        return collect($communications)->map(function ($comm) {
            return [
                'id' => $comm['id'] ?? uniqid(),
                'type' => $comm['type'],
                'subject' => $comm['subject'],
                'message' => $comm['message'],
                'created_by' => $comm['created_by'],
                'created_by_name' => User::find($comm['created_by'])?->name ?? 'Unknown',
                'created_at' => $comm['created_at'],
            ];
        })->sortByDesc('created_at')->values();
    }

    /**
     * Add a customer communication record.
     */
    private function addCustomerCommunication($customerId, $type, $subject, $message, $createdBy)
    {
        $customer = User::find($customerId);
        $communications = json_decode($customer->communications ?? '[]', true);

        $communications[] = [
            'id' => uniqid(),
            'type' => $type,
            'subject' => $subject,
            'message' => $message,
            'created_by' => $createdBy,
            'created_at' => now()->toISOString(),
        ];

        $customer->update(['communications' => json_encode($communications)]);
    }
}
