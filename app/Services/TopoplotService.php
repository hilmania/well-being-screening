<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TopoplotService
{
    /**
     * Generate topoplot image from CSV file with caching
     */
    public static function generateTopoplot(string $csvFilePath, array $options = [], bool $forceRegenerate = false): ?string
    {
        try {
            // Create cache key based on file path and modification time
            $fileHash = md5($csvFilePath . filemtime($csvFilePath));
            $cacheKey = "topoplot_" . $fileHash;

            // Check if image already cached (unless force regenerate)
            if (!$forceRegenerate && Cache::has($cacheKey)) {
                Log::info('Topoplot cache hit', ['cache_key' => $cacheKey]);
                return Cache::get($cacheKey);
            }

            if ($forceRegenerate) {
                Log::info('Force regenerating topoplot', ['cache_key' => $cacheKey]);
                Cache::forget($cacheKey);
            }

            // If not cached, call API
            if (!file_exists($csvFilePath)) {
                Log::error('CSV file not found', ['path' => $csvFilePath]);
                return null;
            }

            $filename = basename($csvFilePath);
            $apiBaseUrl = config('app.topoplot_api_url', 'http://127.0.0.1:8000');
            // $apiUrl = rtrim($apiBaseUrl, '/') . '/topoplot_csv_label';
            $apiUrl = rtrim($apiBaseUrl, '/') . '/topoplot_17';


            $defaultOptions = [
                'return' => 'base64'
            ];

            $requestOptions = array_merge($defaultOptions, $options);

            Log::info('Calling topoplot API', [
                'url' => $apiUrl,
                'file' => $filename,
                'options' => $requestOptions
            ]);

            $response = Http::timeout(30)
                ->attach('file', file_get_contents($csvFilePath), $filename)
                ->post($apiUrl, $requestOptions);

            Log::info('Topoplot API response received', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'success' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $imageBase64 = $data['image'] ?? null; // Changed from 'image_base64' to 'image'

                if ($imageBase64) {
                    // Cache the image for 24 hours
                    Cache::put($cacheKey, $imageBase64, 60 * 24);
                    Log::info('Topoplot generated and cached', [
                        'cache_key' => $cacheKey,
                        'image_size' => strlen($imageBase64)
                    ]);

                    return $imageBase64;
                } else {
                    Log::error('Topoplot API response missing image data', [
                        'response_data' => $data,
                        'available_keys' => array_keys($data)
                    ]);
                }
            } else {
                Log::error('Topoplot API failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Topoplot generation error', [
                'error' => $e->getMessage(),
                'file' => $csvFilePath
            ]);
        }

        return null;
    }

    /**
     * Get HTML img tag for topoplot
     */
    public static function getTopoplotHtml(string $csvFilePath, string $cssClass = 'max-w-32 max-h-32 object-contain rounded-lg border'): string
    {
        $imageBase64 = self::generateTopoplot($csvFilePath);

        if ($imageBase64) {
            return "<img src='data:image/png;base64,{$imageBase64}' alt='Topoplot' class='{$cssClass}' />";
        }

        return '<span class="text-gray-400 text-xs">Topoplot tidak tersedia</span>';
    }

    /**
     * Clear topoplot cache for specific file
     */
    public static function clearCache(string $csvFilePath): void
    {
        $fileHash = md5($csvFilePath . filemtime($csvFilePath));
        $cacheKey = "topoplot_" . $fileHash;
        Cache::forget($cacheKey);
    }

    /**
     * Clear all topoplot cache
     */
    public static function clearAllCache(): void
    {
        // For database cache, we need to clear differently
        try {
            $keys = Cache::store()->many([]);
            foreach ($keys as $key => $value) {
                if (str_starts_with($key, 'topoplot_')) {
                    Cache::forget($key);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not clear all topoplot cache', ['error' => $e->getMessage()]);
        }
    }
}
