<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\VinylMaster;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch vinyl masters with complete status (those that have VinylSec records)
        $completeVinyls = VinylMaster::with([
            'vinylSec' => function ($query) {
                $query->select('id', 'vinyl_master_id', 'price', 'promotional_price', 'is_promotional', 'promo_starts_at', 'promo_ends_at', 'in_stock', 'stock');
            },
            'artists' => function ($query) {
                $query->select('artists.id', 'artists.name');
            }
        ])
        ->whereHas('vinylSec') // Only get vinyl masters that have a vinyl sec record
        ->select('id', 'title', 'slug', 'cover_image', 'release_year')
        ->orderBy('created_at', 'desc')
        ->limit(12) // Show 12 vinyls on home page
        ->get()
        ->map(function ($vinyl) {
            return [
                'id' => $vinyl->id,
                'title' => $vinyl->title,
                'slug' => $vinyl->slug,
                'cover_image' => $vinyl->optimized_cover_image,
                'release_year' => $vinyl->release_year,
                'artists' => $vinyl->artists->pluck('name')->join(', '),
                'current_price' => $vinyl->current_price,
                'status_text' => $vinyl->status_text,
                'is_complete' => true, // All fetched vinyls are complete
            ];
        });

        return inertia('Site/Home', [
            'vinyls' => $completeVinyls
        ]);
    }
}
