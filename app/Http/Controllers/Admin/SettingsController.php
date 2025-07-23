<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatStyleShop;
use App\Models\MidiaStatus;
use App\Models\CoverStatus;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard
     */
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'categoriesCount' => CatStyleShop::count(),
            'mediaStatusCount' => MidiaStatus::count(),
            'coverStatusCount' => CoverStatus::count(),
            'suppliersCount' => Supplier::count(),
        ]);
    }

    // ===== CATEGORIES (CatStyleShop) MANAGEMENT =====

    /**
     * Display categories listing
     */
    public function categories(Request $request)
    {
        $query = CatStyleShop::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name')->get();

        return Inertia::render('Admin/Settings/Categories', [
            'categories' => $categories,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a new category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cat_style_shop,name',
        ]);

        CatStyleShop::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('success', 'Categoria criada com sucesso!');
    }

    /**
     * Update a category
     */
    public function updateCategory(Request $request, CatStyleShop $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cat_style_shop', 'name')->ignore($category->id),
            ],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Delete a category
     */
    public function destroyCategory(CatStyleShop $category)
    {
        // Check if category is being used
        if ($category->vinylMasters()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir esta categoria pois ela está sendo utilizada por discos.']);
        }

        $category->delete();

        return back()->with('success', 'Categoria excluída com sucesso!');
    }

    // ===== MEDIA STATUS MANAGEMENT =====

    /**
     * Display media status listing
     */
    public function mediaStatus(Request $request)
    {
        $query = MidiaStatus::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $mediaStatuses = $query->orderBy('title')->get();

        return Inertia::render('Admin/Settings/MediaStatus', [
            'mediaStatuses' => $mediaStatuses,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a new media status
     */
    public function storeMediaStatus(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:midia_status,title',
            'description' => 'nullable|string',
        ]);

        MidiaStatus::create($validated);

        return back()->with('success', 'Status de mídia criado com sucesso!');
    }

    /**
     * Update a media status
     */
    public function updateMediaStatus(Request $request, MidiaStatus $mediaStatus)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('midia_status', 'title')->ignore($mediaStatus->id),
            ],
            'description' => 'nullable|string',
        ]);

        $mediaStatus->update($validated);

        return back()->with('success', 'Status de mídia atualizado com sucesso!');
    }

    /**
     * Delete a media status
     */
    public function destroyMediaStatus(MidiaStatus $mediaStatus)
    {
        // Check if media status is being used
        if ($mediaStatus->vinylSecs()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir este status pois ele está sendo utilizado por discos.']);
        }

        $mediaStatus->delete();

        return back()->with('success', 'Status de mídia excluído com sucesso!');
    }

    // ===== COVER STATUS MANAGEMENT =====

    /**
     * Display cover status listing
     */
    public function coverStatus(Request $request)
    {
        $query = CoverStatus::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $coverStatuses = $query->orderBy('title')->get();

        return Inertia::render('Admin/Settings/CoverStatus', [
            'coverStatuses' => $coverStatuses,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a new cover status
     */
    public function storeCoverStatus(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:cover_status,title',
            'description' => 'nullable|string',
        ]);

        CoverStatus::create($validated);

        return back()->with('success', 'Status de capa criado com sucesso!');
    }

    /**
     * Update a cover status
     */
    public function updateCoverStatus(Request $request, CoverStatus $coverStatus)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cover_status', 'title')->ignore($coverStatus->id),
            ],
            'description' => 'nullable|string',
        ]);

        $coverStatus->update($validated);

        return back()->with('success', 'Status de capa atualizado com sucesso!');
    }

    /**
     * Delete a cover status
     */
    public function destroyCoverStatus(CoverStatus $coverStatus)
    {
        // Check if cover status is being used
        if ($coverStatus->vinylSecs()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir este status pois ele está sendo utilizado por discos.']);
        }

        $coverStatus->delete();

        return back()->with('success', 'Status de capa excluído com sucesso!');
    }

    // ===== SUPPLIERS MANAGEMENT =====

    /**
     * Display suppliers listing
     */
    public function suppliers(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->input('city')}%");
        }

        if ($request->filled('state')) {
            $query->where('state', $request->input('state'));
        }

        $suppliers = $query->orderBy('name')->get();

        return Inertia::render('Admin/Settings/Suppliers', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'city', 'state']),
        ]);
    }

    /**
     * Store a new supplier
     */
    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'document' => 'nullable|string|max:20',
            'document_type' => 'nullable|in:cnpj,cpf',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zipcode' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'last_purchase_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        Supplier::create($validated);

        return back()->with('success', 'Fornecedor criado com sucesso!');
    }

    /**
     * Update a supplier
     */
    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'document' => 'nullable|string|max:20',
            'document_type' => 'nullable|in:cnpj,cpf',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zipcode' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'last_purchase_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return back()->with('success', 'Fornecedor atualizado com sucesso!');
    }

    /**
     * Delete a supplier
     */
    public function destroySupplier(Supplier $supplier)
    {
        // Check if supplier is being used
        if ($supplier->vinylSecs()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir este fornecedor pois ele está sendo utilizado por discos.']);
        }

        $supplier->delete();

        return back()->with('success', 'Fornecedor excluído com sucesso!');
    }
}
