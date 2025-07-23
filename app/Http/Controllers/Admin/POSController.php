<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\VinylSec;
use App\Models\VinylMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class POSController extends Controller
{
    public function index(Request $request): Response
    {
        $query = PosSale::with(['user', 'items.vinylSec.vinylMaster'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('payment_method')) {
            $query->byPaymentMethod($request->payment_method);
        }

        if ($request->filled('date_range')) {
            $dateRange = $this->parseDateRange($request->date_range);
            $query->byDateRange($dateRange);
        }

        $sales = $query->paginate(15)->withQueryString();

        // Calculate statistics
        $stats = $this->calculateStats($request);

        return Inertia::render('Admin/POS/History', [
            'sales' => $sales,
            'stats' => $stats,
            'filters' => $request->only(['search', 'payment_method', 'date_range']),
            'paymentMethods' => $this->getPaymentMethods(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/POS/DirectSale', [
            'paymentMethods' => $this->getPaymentMethods(),
        ]);
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('search', '');

        $products = VinylSec::with(['vinylMaster.artists', 'midiaStatus', 'coverStatus'])
            ->whereHas('vinylMaster', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhereHas('artists', function ($artistQuery) use ($search) {
                          $artistQuery->where('name', 'like', "%{$search}%");
                      });
            })
            ->orWhere('catalog_number', 'like', "%{$search}%")
            ->orWhere('internal_code', 'like', "%{$search}%")
            ->where('in_stock', true)
            ->where('stock', '>', 0)
            ->limit(20)
            ->get()
            ->map(function ($vinylSec) {
                return [
                    'id' => $vinylSec->id,
                    'title' => $vinylSec->vinylMaster->title ?? 'Título não informado',
                    'artist' => $vinylSec->vinylMaster->artists->pluck('name')->join(', ') ?: 'Artista não informado',
                    'catalog_number' => $vinylSec->catalog_number,
                    'internal_code' => $vinylSec->internal_code,
                    'price' => $vinylSec->getCurrentPrice(),
                    'stock' => $vinylSec->stock,
                    'format' => $vinylSec->format,
                    'midia_status' => $vinylSec->midiaStatus->name ?? 'N/A',
                    'cover_status' => $vinylSec->coverStatus->name ?? 'N/A',
                ];
            });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'payment_method' => 'required|string|in:money,credit_card,debit_card,pix,bank_transfer',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.vinyl_sec_id' => 'required|exists:vinyl_secs,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.item_discount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = 0;
            $totalDiscount = 0;

            foreach ($request->items as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $itemDiscount = $item['item_discount'] ?? 0;
                $subtotal += $itemTotal;
                $totalDiscount += $itemDiscount;
            }

            $total = $subtotal - $totalDiscount;

            // Create POS sale
            $posSale = PosSale::create([
                'user_id' => $request->user_id,
                'customer_name' => $request->customer_name,
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'shipping' => 0, // POS sales don't have shipping
                'total' => $total,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'invoice_number' => PosSale::generateInvoiceNumber(),
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                $vinylSec = VinylSec::findOrFail($item['vinyl_sec_id']);

                // Check stock availability
                if ($vinylSec->stock < $item['quantity']) {
                    throw new \Exception("Estoque insuficiente para o produto: {$vinylSec->vinylMaster->title}");
                }

                $itemTotal = ($item['price'] * $item['quantity']) - ($item['item_discount'] ?? 0);

                PosSaleItem::create([
                    'pos_sale_id' => $posSale->id,
                    'vinyl_sec_id' => $item['vinyl_sec_id'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'item_discount' => $item['item_discount'] ?? 0,
                    'item_total' => $itemTotal,
                ]);

                // Update stock
                $vinylSec->decrement('stock', $item['quantity']);

                // Update in_stock status if stock reaches zero
                if ($vinylSec->stock <= 0) {
                    $vinylSec->update(['in_stock' => false]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venda realizada com sucesso!',
                'sale' => $posSale->load(['items.vinylSec.vinylMaster']),
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar venda: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function show(PosSale $posSale): Response
    {
        $posSale->load(['user', 'items.vinylSec.vinylMaster.artists']);

        return Inertia::render('Admin/POS/Show', [
            'sale' => $posSale,
        ]);
    }

    public function receipt(PosSale $posSale)
    {
        $posSale->load(['user', 'items.vinylSec.vinylMaster.artists']);

        return response()->json([
            'sale' => $posSale,
            'receipt_data' => [
                'store_name' => config('app.name', 'RDV Loja'),
                'store_address' => 'Endereço da loja aqui',
                'store_phone' => 'Telefone da loja aqui',
                'generated_at' => now()->format('d/m/Y H:i:s'),
            ],
        ]);
    }

    private function getPaymentMethods(): array
    {
        return [
            ['value' => 'money', 'label' => 'Dinheiro'],
            ['value' => 'credit_card', 'label' => 'Cartão de Crédito'],
            ['value' => 'debit_card', 'label' => 'Cartão de Débito'],
            ['value' => 'pix', 'label' => 'PIX'],
            ['value' => 'bank_transfer', 'label' => 'Transferência Bancária'],
        ];
    }

    private function parseDateRange($dateRange): array
    {
        $ranges = [
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
        ];

        return $ranges[$dateRange] ?? [null, null];
    }

    private function calculateStats(Request $request): array
    {
        $query = PosSale::query();

        // Apply same filters as main query
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('payment_method')) {
            $query->byPaymentMethod($request->payment_method);
        }

        if ($request->filled('date_range')) {
            $dateRange = $this->parseDateRange($request->date_range);
            $query->byDateRange($dateRange);
        }

        return [
            'total_sales' => $query->count(),
            'total_revenue' => $query->sum('total'),
            'average_sale' => $query->avg('total') ?? 0,
            'total_items_sold' => PosSaleItem::whereIn('pos_sale_id', $query->pluck('id'))->sum('quantity'),
        ];
    }
}
