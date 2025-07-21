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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

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

    public function index()
    {
        return Inertia::render('Admin/Vinyl/Index', [
            'vinyls' => VinylMaster::with(['artists', 'recordLabels'])
                ->latest()
                ->paginate(10),
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

            return redirect()->route('admin.vinyls.index')->with('success', 'Disco adicionado com sucesso!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollback();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \DB::rollback();
            Log::error('Erro ao salvar vinyl: ' . $e->getMessage() . ' na linha ' . $e->getLine() . ' do arquivo ' . $e->getFile());
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
