<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YouTubeService
{
    protected $apiKey;
    protected $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
    }

    public function searchVideos(string $query, int $maxResults = 10): array
    {
        if (empty($this->apiKey)) {
            // Retornar erro ou array vazio se a chave não estiver configurada
            return ['error' => 'YouTube API key is not configured.'];
        }

        $response = Http::get("$this->baseUrl/search", [
            'part' => 'snippet',
            'q' => $query,
            'key' => $this->apiKey,
            'maxResults' => $maxResults,
            'type' => 'video',
        ]);

        if ($response->failed()) {
            return ['error' => 'Failed to fetch data from YouTube API.'];
        }

        $results = $response->json();

        return array_map(function ($item) {
            return [
                'videoId' => $item['id']['videoId'],
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'thumbnail' => $item['snippet']['thumbnails']['default']['url'],
                'url' => 'https://www.youtube.com/watch?v=' . $item['id']['videoId'],
            ];
        }, $results['items'] ?? []);
    }
}
