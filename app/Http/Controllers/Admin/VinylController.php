<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DiscogsService;
use App\Models\VinylMaster;
use App\Models\Artist;
use App\Models\RecordLabel;
use App\Models\Track;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\VinylSec;
use App\Models\Weight;
use App\Models\Dimension;
use App\Models\MidiaStatus;
use App\Models\CoverStatus;
use App\Models\Supplier;
use App\Models\CatStyleShop;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class VinylController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        public function searchDiscogs(Request $request, DiscogsService $discogsService)
    {
        $request->validate(['query' => 'required|string|min:3']);

        $results = $discogsService->search($request->input('query'));

        return response()->json($results);
    }

    /**
     * Buscar detalhes completos de um release do Discogs
     */
    public function getDiscogsDetails($discogs_id, DiscogsService $discogsService)
    {
        try {
            $details = $discogsService->getReleaseDetails($discogs_id);

            // Buscar imagens dos artistas
            if (!empty($details['artists'])) {
                foreach ($details['artists'] as &$artist) { // Usar referência para modificar o array original
                    try {
                        $artistDetails = $discogsService->getArtistDetails($artist['id']);
                        $artist['images'] = $artistDetails['images'] ?? [];
                    } catch (\Exception $e) {
                        Log::warning("Erro ao buscar detalhes do artista {$artist['id']}: " . $e->getMessage());
                        $artist['images'] = []; // Garante que a chave 'images' exista
                    }
                }
            }

            // Buscar imagens das gravadoras
            if (!empty($details['record_labels'])) {
                foreach ($details['record_labels'] as &$label) { // Usar referência
                    try {
                        $labelDetails = $discogsService->getLabelDetails($label['id']);
                        $label['images'] = $labelDetails['images'] ?? [];
                    } catch (\Exception $e) {
                        Log::warning("Erro ao buscar detalhes da gravadora {$label['id']}: " . $e->getMessage());
                        $label['images'] = [];
                    }
                }
            }

            return response()->json($details);
        } catch (\Exception $e) {
            Log::error('Erro em getDiscogsDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Não foi possível obter os detalhes do lançamento.'], 500);
        }
    }

    public function index(Request $request)
    {
        $query = VinylMaster::with([
            'artists',
            'vinylSec.midiaStatus',
            'vinylSec.coverStatus',
            'vinylSec.supplier',
            'categories',
            'recordLabels'
        ])
        ->select('vinyl_masters.*', 'vinyl_secs.price', 'vinyl_secs.in_stock', 'vinyl_secs.stock', 'vinyl_secs.promotional_price', 'vinyl_secs.is_promotional')
        ->leftJoin('vinyl_secs', 'vinyl_masters.id', '=', 'vinyl_secs.vinyl_master_id');

        // Advanced search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('vinyl_masters.title', 'like', "%{$search}%")
                    ->orWhere('vinyl_masters.description', 'like', "%{$search}%")
                    ->orWhere('vinyl_masters.discogs_id', 'like', "%{$search}%")
                    ->orWhereHas('artists', function ($q_artist) use ($search) {
                        $q_artist->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('recordLabels', function ($q_label) use ($search) {
                        $q_label->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by artist
        if ($request->filled('artist')) {
            $artist = $request->input('artist');
            $query->whereHas('artists', function ($q_artist) use ($artist) {
                $q_artist->where('name', 'like', "%{$artist}%");
            });
        }

        // Filter by year range
        if ($request->filled('year_from')) {
            $query->where('vinyl_masters.release_year', '>=', $request->input('year_from'));
        }
        if ($request->filled('year_to')) {
            $query->where('vinyl_masters.release_year', '<=', $request->input('year_to'));
        }

        // Filter by specific year
        if ($request->filled('year')) {
            $query->where('vinyl_masters.release_year', $request->input('year'));
        }

        // Filter by category (genre)
        if ($request->filled('category')) {
            $category_id = $request->input('category');
            $query->whereHas('categories', function ($q_cat) use ($category_id) {
                $q_cat->where('cat_style_shop.id', $category_id);
            });
        }

        // Filter by media status
        if ($request->filled('media_status')) {
            $query->whereHas('vinylSec', function ($q) use ($request) {
                $q->where('midia_status_id', $request->input('media_status'));
            });
        }

        // Filter by cover status
        if ($request->filled('cover_status')) {
            $query->whereHas('vinylSec', function ($q) use ($request) {
                $q->where('cover_status_id', $request->input('cover_status'));
            });
        }

        // Filter by availability status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'available') {
                $query->where('vinyl_secs.in_stock', true)
                      ->where('vinyl_secs.stock', '>', 0);
            } elseif ($status === 'unavailable') {
                $query->where(function ($q) {
                    $q->where('vinyl_secs.in_stock', false)
                      ->orWhere('vinyl_secs.stock', '<=', 0)
                      ->orWhereNull('vinyl_secs.id');
                });
            } elseif ($status === 'incomplete') {
                $query->whereNull('vinyl_secs.id');
            } elseif ($status === 'promotional') {
                $query->where('vinyl_secs.is_promotional', true);
            }
        }

        // Filter by price range
        if ($request->filled('price_from')) {
            $query->where('vinyl_secs.price', '>=', $request->input('price_from'));
        }
        if ($request->filled('price_to')) {
            $query->where('vinyl_secs.price', '<=', $request->input('price_to'));
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('vinyl_masters.country', 'like', '%' . $request->input('country') . '%');
        }

        // Sorting functionality
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['title', 'release_year', 'created_at', 'price', 'stock'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        if ($sortBy === 'price' || $sortBy === 'stock') {
            $query->orderBy('vinyl_secs.' . $sortBy, $sortDirection);
        } else {
            $query->orderBy('vinyl_masters.' . $sortBy, $sortDirection);
        }

        // Enhanced pagination with more options
        $perPage = $request->input('per_page', 15);
        $allowedPerPage = [10, 15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        $vinyls = $query->paginate($perPage)->withQueryString();

        // Get filter options for dropdowns
        $filterOptions = [
            'categories' => CatStyleShop::orderBy('name')->get(),
            'mediaStatuses' => MidiaStatus::orderBy('title')->get(),
            'coverStatuses' => CoverStatus::orderBy('title')->get(),
            'years' => VinylMaster::selectRaw('DISTINCT release_year')
                ->whereNotNull('release_year')
                ->orderBy('release_year', 'desc')
                ->pluck('release_year'),
            'countries' => VinylMaster::selectRaw('DISTINCT country')
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->orderBy('country')
                ->pluck('country'),
        ];

        return Inertia::render('Admin/Vinyl/Index', [
            'vinyls' => $vinyls,
            'filters' => $request->only([
                'search', 'artist', 'year', 'year_from', 'year_to',
                'category', 'media_status', 'cover_status', 'status',
                'price_from', 'price_to', 'country', 'sort_by',
                'sort_direction', 'per_page'
            ]),
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Vinyl/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'required|integer',
            'country' => 'nullable|string|max:100',
            'discogs_id' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discogs_url' => 'nullable|string|max:255',
            'cover_image' => 'nullable|string|url',
            'artists' => 'required|array|min:1',
            'artists.*.name' => 'required|string|max:255',
            'artists.*.id' => 'required|integer', // Discogs artist ID
            'artists.*.discogs_url' => 'nullable|string|url',
            'artists.*.profile' => 'nullable|string',
            'artists.*.image' => 'nullable|string|url', // Selected artist image
            'record_labels' => 'required|array|min:1',
            'record_labels.*.name' => 'required|string|max:255',
            'record_labels.*.id' => 'required|integer', // Discogs label ID
            'record_labels.*.discogs_url' => 'nullable|string|url',
            'record_labels.*.profile' => 'nullable|string',
            'record_labels.*.logo' => 'nullable|string|url', // Selected label logo
            'tracks' => 'nullable|array',
            'tracks.*.name' => 'required|string|max:255',
            'tracks.*.position' => 'required|string', // Position can be 'A1', 'B2', etc.
            'tracks.*.duration' => 'nullable|string|max:20',
            'tracks.*.youtube_url' => 'nullable|string|url',
        ]);

        try {
            // Check if vinyl already exists
            if (VinylMaster::where('discogs_id', $validatedData['discogs_id'])->exists()) {
                return back()->withErrors(['error' => 'Este disco já está cadastrado no sistema.'])->withInput();
            }

            \DB::beginTransaction();

            // 1. Create unique slug
            $slug = Str::slug($validatedData['title'] . '-' . $validatedData['discogs_id']);

            // 2. Download and save cover image
            $coverImagePath = null;
            if (!empty($validatedData['cover_image'])) {
                try {
                    $coverImageContents = file_get_contents($validatedData['cover_image']);
                    $coverImageName = 'vinyl_covers/' . $slug . '.jpg';
                    Storage::disk('public')->put($coverImageName, $coverImageContents);
                    $coverImagePath = 'storage/' . $coverImageName;
                } catch (\Exception $e) {
                    Log::warning('Erro ao baixar imagem da capa: ' . $e->getMessage());
                }
            }

            // 3. Create VinylMaster
            $vinylMaster = VinylMaster::create([
                'title' => $validatedData['title'],
                'slug' => $slug,
                'discogs_id' => $validatedData['discogs_id'],
                'description' => $validatedData['description'],
                'cover_image' => $coverImagePath,
                'discogs_url' => $validatedData['discogs_url'],
                'release_year' => $validatedData['year'],
                'country' => $validatedData['country'],
            ]);

            // 4. Process and associate artists
            $artistIds = [];
            foreach ($validatedData['artists'] as $artistData) {
                $artistImagePath = null;
                if (!empty($artistData['image'])) {
                    try {
                        $artistImageContents = file_get_contents($artistData['image']);
                        $artistImageName = 'artist_images/' . Str::slug($artistData['name']) . '-' . $artistData['id'] . '.jpg';
                        Storage::disk('public')->put($artistImageName, $artistImageContents);
                        $artistImagePath = 'storage/' . $artistImageName;
                    } catch (\Exception $e) {
                        Log::warning("Erro ao baixar imagem do artista {$artistData['name']}: " . $e->getMessage());
                    }
                }

                $artist = Artist::updateOrCreate(
                    ['discogs_id' => $artistData['id']],
                    [
                        'name' => $artistData['name'],
                        'slug' => Str::slug($artistData['name']),
                        'profile' => $artistData['profile'] ?? '',
                        'images' => $artistImagePath ? json_encode([$artistImagePath]) : null,
                        'discogs_url' => $artistData['discogs_url'],
                    ]
                );
                $artistIds[] = $artist->id;
            }
            $vinylMaster->artists()->sync($artistIds);

            // 5. Process and associate record labels
            $labelIds = [];
            foreach ($validatedData['record_labels'] as $labelData) {
                $labelLogoPath = null;
                if (!empty($labelData['logo'])) {
                    try {
                        $labelLogoContents = file_get_contents($labelData['logo']);
                        $labelLogoName = 'label_logos/' . Str::slug($labelData['name']) . '-' . $labelData['id'] . '.jpg';
                        Storage::disk('public')->put($labelLogoName, $labelLogoContents);
                        $labelLogoPath = 'storage/' . $labelLogoName;
                    } catch (\Exception $e) {
                        Log::warning("Erro ao baixar logo da gravadora {$labelData['name']}: " . $e->getMessage());
                    }
                }

                $label = RecordLabel::updateOrCreate(
                    ['discogs_id' => $labelData['id']],
                    [
                        'name' => $labelData['name'],
                        'slug' => Str::slug($labelData['name']),
                        'discogs_url' => $labelData['discogs_url'],
                        'description' => $labelData['profile'] ?? '', // Discogs uses 'profile' for label description
                        'logo' => $labelLogoPath,
                    ]
                );
                $labelIds[] = $label->id;
            }
            $vinylMaster->recordLabels()->sync($labelIds);

            // 6. Create tracks
            if (!empty($validatedData['tracks'])) {
                foreach ($validatedData['tracks'] as $trackData) {
                    $vinylMaster->tracks()->create([
                        'name' => $trackData['name'],
                        'position' => $trackData['position'],
                        'duration' => $trackData['duration'],
                        'youtube_url' => $trackData['youtube_url'] ?? null,
                    ]);
                }
            }

            // 7. Create product automatically
            $this->createProduct($vinylMaster);

            \DB::commit();

            return redirect()->route('admin.vinyls.complete', $vinylMaster->id)->with('success', 'Disco adicionado com sucesso! Agora complete as informações de estoque e preço.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollback();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \DB::rollback();
            Log::error('Erro ao salvar vinyl: ' . $e->getMessage() . ' na linha ' . $e->getLine() . ' do arquivo ' . $e->getFile() . '. Exceção completa: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Ocorreu um erro inesperado ao salvar o disco. Por favor, tente novamente.'])->withInput();
        }
    }

    /**
     * Criar produto automaticamente para o vinyl master
     */
    private function createProduct(VinylMaster $vinylMaster)
    {
        // Buscar o tipo de produto "vinyl"
        $productType = ProductType::where('slug', 'vinyl')->first();

        if (!$productType) {
            // Se o tipo de produto não existir, lança uma exceção.
            // Isso indica que os dados base (seeders) não foram executados.
            throw new \Exception("ProductType 'vinyl' não encontrado. Execute os seeders.");
        }

        // Criar o produto
        $product = new Product([
            'name' => $vinylMaster->title,
            'slug' => $vinylMaster->slug,
            'description' => $vinylMaster->description,
            'product_type_id' => $productType->id,
        ]);

        // Associar o produto ao vinil
        $vinylMaster->products()->save($product);
    }

    /**
     * Exibir a página de finalização do cadastro
     */
    public function complete($id)
    {
        $vinylMaster = VinylMaster::with(['artists', 'recordLabels', 'tracks'])->findOrFail($id);

        // Buscar dados necessários para o formulário
        $weights = Weight::all();
        $dimensions = Dimension::all();
        $midiaStatuses = MidiaStatus::all();
        $coverStatuses = CoverStatus::all();
        $suppliers = Supplier::all();
        $categories = CatStyleShop::all();

        return inertia('Admin/Vinyl/Complete', [
            'vinylMaster' => $vinylMaster,
            'weights' => $weights,
            'dimensions' => $dimensions,
            'midiaStatuses' => $midiaStatuses,
            'coverStatuses' => $coverStatuses,
            'suppliers' => $suppliers,
            'categories' => $categories,
        ]);
    }

    /**
     * Excluir disco
     */
    public function destroy(VinylMaster $vinyl)
    {
        // Futuramente, adicionar lógica para deletar arquivos de imagem associados do storage.
        $vinyl->delete();

        return redirect()->route('admin.vinyls.index')->with('success', 'Disco excluído com sucesso.');
    }

    /**
     * Salvar os dados de finalização do cadastro
     */
    public function completeStore(Request $request, $vinylMasterId)
    {
        $vinylMaster = VinylMaster::findOrFail($vinylMasterId);

        $validatedData = $request->validate([
            'weight_id' => 'nullable|exists:weights,id',
            'dimension_id' => 'nullable|exists:dimensions,id',
            'midia_status_id' => 'nullable|exists:midia_status,id',
            'cover_status_id' => 'nullable|exists:cover_status,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:cat_style_shop,id',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'buy_price' => 'nullable|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0',
            'is_new' => 'boolean',
            'in_stock' => 'boolean',
            'is_promotional' => 'boolean',
            'is_presale' => 'boolean',
            'presale_arrival_date' => 'nullable|date|after:today',
        ]);

        try {
            \DB::beginTransaction();

            // Gerar código interno único
            $internalCode = VinylSec::generateInternalCode();

            // Criar VinylSec
            $vinylSec = VinylSec::create([
                'vinyl_master_id' => $vinylMaster->id,
                'internal_code' => $internalCode,
                'weight_id' => $validatedData['weight_id'] ?? null,
                'dimension_id' => $validatedData['dimension_id'] ?? null,
                'midia_status_id' => $validatedData['midia_status_id'] ?? null,
                'cover_status_id' => $validatedData['cover_status_id'] ?? null,
                'supplier_id' => $validatedData['supplier_id'] ?? null,
                'stock' => $validatedData['stock'],
                'price' => $validatedData['price'],
                'buy_price' => $validatedData['buy_price'] ?? null,
                'promotional_price' => $validatedData['promotional_price'] ?? null,
                'is_new' => $validatedData['is_new'] ?? false,
                'in_stock' => $validatedData['in_stock'] ?? true,
                'is_promotional' => $validatedData['is_promotional'] ?? false,
                'is_presale' => $validatedData['is_presale'] ?? false,
                'presale_arrival_date' => $validatedData['presale_arrival_date'] ?? null,
            ]);

            // Associar categorias
            if (!empty($validatedData['categories'])) {
                $vinylMaster->categories()->sync($validatedData['categories']);
            }

            \DB::commit();

            return redirect()->route('admin.vinyls.index')
                ->with('success', 'Cadastro do disco finalizado com sucesso! Código interno: ' . $internalCode);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Erro ao finalizar cadastro do vinyl: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Ocorreu um erro ao finalizar o cadastro. Tente novamente.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vinyl = VinylMaster::with([
            'artists',
            'recordLabels',
            'tracks',
            'vinylSec.midiaStatus',
            'vinylSec.coverStatus',
            'vinylSec.supplier',
            'categories'
        ])->findOrFail($id);

        return Inertia::render('Admin/Vinyl/Show', [
            'vinyl' => $vinyl,
        ]);
    }

    /**
     * Get optimized image URL for vinyl cover
     */
    public function getOptimizedImage(VinylMaster $vinyl, $size = 'medium')
    {
        if (!$vinyl->cover_image) {
            return asset('images/vinyl-placeholder.png');
        }

        // If it's already a full URL (from Discogs), return as is
        if (filter_var($vinyl->cover_image, FILTER_VALIDATE_URL)) {
            return $vinyl->cover_image;
        }

        // For local images, ensure proper path
        $imagePath = $vinyl->cover_image;
        if (!str_starts_with($imagePath, 'storage/')) {
            $imagePath = 'storage/' . ltrim($imagePath, '/');
        }

        return asset($imagePath);
    }

    /**
     * Update vinyl image
     */
    public function updateImage(Request $request, VinylMaster $vinyl)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Delete old image if it exists and is local
            if ($vinyl->cover_image && !filter_var($vinyl->cover_image, FILTER_VALIDATE_URL)) {
                $oldImagePath = str_replace('storage/', '', $vinyl->cover_image);
                Storage::disk('public')->delete($oldImagePath);
            }

            // Store new image
            $imagePath = $request->file('image')->store('vinyl_covers', 'public');

            // Update vinyl record
            $vinyl->update([
                'cover_image' => 'storage/' . $imagePath
            ]);

            return back()->with('success', 'Imagem atualizada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar imagem do vinyl: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao atualizar a imagem.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $vinyl = VinylMaster::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'release_year' => 'required|integer|min:1900|max:' . (date('Y') + 5),
            'country' => 'nullable|string|max:100',
        ]);

        try {
            $vinyl->update($validatedData);

            return back()->with('success', 'Disco atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar vinyl: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao atualizar o disco.']);
        }
    }

    /**
     * Handle bulk actions on multiple vinyls
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,update_status,update_category',
            'vinyl_ids' => 'required|array|min:1',
            'vinyl_ids.*' => 'exists:vinyl_masters,id',
            'status' => 'nullable|boolean',
            'category_id' => 'nullable|exists:cat_style_shop,id',
        ]);

        $vinylIds = $request->input('vinyl_ids');
        $action = $request->input('action');

        try {
            DB::beginTransaction();

            switch ($action) {
                case 'delete':
                    VinylMaster::whereIn('id', $vinylIds)->delete();
                    $message = 'Discos excluídos com sucesso!';
                    break;

                case 'update_status':
                    VinylSec::whereIn('vinyl_master_id', $vinylIds)
                        ->update(['in_stock' => $request->input('status')]);
                    $message = 'Status dos discos atualizado com sucesso!';
                    break;

                case 'update_category':
                    $categoryId = $request->input('category_id');
                    foreach ($vinylIds as $vinylId) {
                        $vinyl = VinylMaster::find($vinylId);
                        if ($vinyl) {
                            $vinyl->categories()->sync([$categoryId]);
                        }
                    }
                    $message = 'Categoria dos discos atualizada com sucesso!';
                    break;
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro na ação em lote: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao executar a ação em lote.']);
        }
    }

    /**
     * Export vinyls data
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');

        $query = VinylMaster::with(['artists', 'vinylSec', 'categories'])
            ->select('vinyl_masters.*', 'vinyl_secs.price', 'vinyl_secs.in_stock', 'vinyl_secs.stock')
            ->leftJoin('vinyl_secs', 'vinyl_masters.id', '=', 'vinyl_secs.vinyl_master_id');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('vinyl_masters.title', 'like', "%{$search}%")
                    ->orWhereHas('artists', function ($q_artist) use ($search) {
                        $q_artist->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $vinyls = $query->get();

        if ($format === 'csv') {
            return $this->exportToCsv($vinyls);
        }

        return back()->withErrors(['error' => 'Formato de exportação não suportado.']);
    }

    /**
     * Export to CSV format
     */
    private function exportToCsv($vinyls)
    {
        $filename = 'vinyls_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($vinyls) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID', 'Título', 'Artistas', 'Ano', 'País', 'Preço',
                'Estoque', 'Status', 'Categorias', 'Discogs ID'
            ]);

            foreach ($vinyls as $vinyl) {
                fputcsv($file, [
                    $vinyl->id,
                    $vinyl->title,
                    $vinyl->artists->pluck('name')->join(', '),
                    $vinyl->release_year,
                    $vinyl->country,
                    $vinyl->price ? 'R$ ' . number_format($vinyl->price, 2, ',', '.') : '-',
                    $vinyl->stock ?? 0,
                    $vinyl->in_stock ? 'Em Estoque' : 'Fora de Estoque',
                    $vinyl->categories->pluck('name')->join(', '),
                    $vinyl->discogs_id,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
