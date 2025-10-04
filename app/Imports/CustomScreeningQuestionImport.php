<?php

namespace App\Imports;

use App\Models\ScreeningQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomScreeningQuestionImport implements ToCollection, WithHeadingRow
{
    protected $model;
    protected $fieldsToImport;
    protected $additionalData;

    public function __construct($model = null, $attributes = [], $additionalData = [])
    {
        $this->model = $model ?: ScreeningQuestion::class;
        $this->fieldsToImport = $attributes['fieldsToImport'] ?? [];
        $this->additionalData = $additionalData;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $data = [];

            // Map the fields from Excel to model attributes
            foreach ($this->fieldsToImport as $field => $config) {
                $value = $row[$field] ?? null;

                // Handle boolean conversion for is_active
                if ($field === 'is_active' && $value !== null) {
                    $value = $this->parseBooleanValue($value);
                }

                // Set default values if specified
                if ($value === null && isset($config['default'])) {
                    $value = $config['default'];
                }

                $data[$field] = $value;
            }

            // Create the model instance
            $this->model::create($data);
        }
    }

    private function parseBooleanValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value));

        return in_array($value, ['1', 'true', 'yes', 'ya', 'on', 'aktif'], true);
    }

    public static function importFromFile($filePath, $model = null, $attributes = [], $additionalData = [])
    {
        // Handle Livewire temporary file path resolution
        $resolvedPath = static::resolveFilePath($filePath);

        // Create import instance
        $import = new static($model, $attributes, $additionalData);

        // Import the file
        \Maatwebsite\Excel\Facades\Excel::import($import, $resolvedPath);

        return $import;
    }

    protected static function resolveFilePath($filePath)
    {
        // If file exists at the given path, use it
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->path($filePath);
        }

        // Try to find the file in private/livewire-tmp
        $privatePath = 'private/' . $filePath;
        if (Storage::disk('local')->exists($privatePath)) {
            return Storage::disk('local')->path($privatePath);
        }

        // Try without the livewire-tmp prefix
        $cleanPath = str_replace('livewire-tmp/', '', $filePath);
        $privateCleanPath = 'private/livewire-tmp/' . $cleanPath;
        if (Storage::disk('local')->exists($privateCleanPath)) {
            return Storage::disk('local')->path($privateCleanPath);
        }

        // If all else fails, return the original path
        return Storage::disk('local')->path($filePath);
    }
}
