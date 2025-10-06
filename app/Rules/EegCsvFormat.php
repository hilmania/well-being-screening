<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class EegCsvFormat implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(?string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('File upload tidak valid.');
            return;
        }

        // Check if file is CSV
        $extension = strtolower($value->getClientOriginalExtension());
        $mimeType = $value->getMimeType();

        if (!in_array($extension, ['csv']) && !in_array($mimeType, ['text/csv', 'application/csv', 'text/plain'])) {
            $fail('File harus berformat CSV.');
            return;
        }

        // Read the CSV file content
        $path = $value->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            $fail('File tidak dapat dibaca.');
            return;
        }

        $lineCount = 0;
        $validLines = 0;
        $maxLinesToCheck = 100; // Check first 100 lines for performance

        while (($line = fgets($handle)) !== false && $lineCount < $maxLinesToCheck) {
            $lineCount++;
            $line = trim($line);

            if (empty($line)) {
                continue; // Skip empty lines
            }

            // Split by semicolon (as per the sample file)
            $columns = explode(';', $line);

            // Check if there are exactly 4 columns
            if (count($columns) !== 4) {
                fclose($handle);
                $fail("Baris {$lineCount}: File harus memiliki tepat 4 kolom yang dipisahkan dengan semicolon (;). Ditemukan " . count($columns) . " kolom.");
                return;
            }

            // Check if all columns contain numeric values
            foreach ($columns as $index => $column) {
                $column = trim($column);

                // Replace comma with dot for decimal numbers (European format)
                $column = str_replace(',', '.', $column);

                if (!is_numeric($column)) {
                    fclose($handle);
                    $fail("Baris {$lineCount}, kolom " . ($index + 1) . ": Nilai harus berupa angka. Ditemukan: '{$column}'");
                    return;
                }
            }

            $validLines++;
        }

        fclose($handle);

        // Check if file has any valid data
        if ($validLines === 0) {
            $fail('File tidak mengandung data yang valid.');
            return;
        }

        // Additional validation: check minimum number of lines
        if ($validLines < 10) {
            $fail('File harus mengandung minimal 10 baris data EEG yang valid.');
            return;
        }
    }
}
