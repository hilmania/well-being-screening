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
            $apiUrl = rtrim($apiBaseUrl, '/') . '/topoplot_csv_label';

            $defaultOptions = [
                'return' => 'base64',
                'dpi' => 100,
                'row' => 2
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

            if ($response->successful()) {
                $data = $response->json();
                $imageBase64 = $data['image_base64'] ?? null;

                if ($imageBase64) {
                    // Cache the image for 24 hours
                    Cache::put($cacheKey, $imageBase64, 60 * 24);
                    Log::info('Topoplot generated and cached', [
                        'cache_key' => $cacheKey,
                        'image_size' => strlen($imageBase64)
                    ]);

                    return $imageBase64;
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
        $keys = Cache::getRedis()->keys('*topoplot_*');
        foreach ($keys as $key) {
            Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
        }
    }
}
