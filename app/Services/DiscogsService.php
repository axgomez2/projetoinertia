<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DiscogsService
{
    protected $client;
    protected $token;

    public function __construct()
    {
        $this->token = config('services.discogs.token');
        
        $this->client = Http::withHeaders([
            'Authorization' => 'Discogs token=' . $this->token,
            'User-Agent' => config('app.name'),
        ])->baseUrl('https://api.discogs.com');
    }

    /**
     * Search for a release on Discogs.
     *
     * @param string $query
     * @return array
     */
    public function search(string $query): array
    {
        $response = $this->client->get('/database/search', [
            'q' => $query,
            'type' => 'release', // Search for releases (albums, EPs, singles)
            'per_page' => 20, // Limit results
        ]);

        if ($response->failed()) {
            // Handle error appropriately, maybe log it
            return [];
        }

        return $response->json('results', []);
    }

    /**
     * Get detailed information about a specific release.
     *
     * @param string $releaseId
     * @return array
     */
    public function getReleaseDetails($releaseId)
    {
        $data = $this->performRequest("/releases/{$releaseId}");

        return [
            'title' => $data['title'] ?? 'N/A',
            'artists' => array_map(function ($artist) {
                return [
                    'id' => $artist['id'],
                    'name' => $artist['name'],
                    'discogs_url' => $artist['resource_url'] ?? null,
                ];
            }, $data['artists'] ?? []),
            'record_labels' => array_map(function ($label) {
                return [
                    'id' => $label['id'],
                    'name' => $label['name'],
                    'discogs_url' => $label['resource_url'] ?? null,
                ];
            }, $data['labels'] ?? []),
            'year' => $data['year'] ?? null,
            'country' => $data['country'] ?? null,
            'discogs_id' => (string)($data['id'] ?? null),
            'discogs_url' => $data['uri'] ?? null,
            'cover_image' => $data['images'][0]['uri'] ?? null, // Pega a primeira imagem como padrão
            'images' => $data['images'] ?? [],
            'tracks' => array_map(function ($track) {
                return [
                    'position' => $track['position'],
                    'name' => $track['title'],
                    'duration' => $track['duration'],
                ];
            }, $data['tracklist'] ?? []),
            'description' => $data['notes'] ?? null,
        ];
    }

    public function getArtistDetails($artistId)
    {
        return $this->performRequest("/artists/{$artistId}");
    }

    public function getLabelDetails($labelId)
    {
        return $this->performRequest("/labels/{$labelId}");
    }

    private function performRequest($uri)
    {
        $response = $this->client->get($uri);

        if ($response->failed()) {
            // Handle error appropriately, maybe log it
            return [];
        }

        return $response->json();
    }
}
