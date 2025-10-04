<?php

namespace App\Imports;

use App\Models\ScreeningQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class ScreeningQuestionImport implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    private array $errors = [];
    private int $successCount = 0;
    private int $errorCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Validate each row
                $this->validateRow($row->toArray(), $index + 2); // +2 because of header and 0-based index

                // Create the question
                ScreeningQuestion::create([
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'placeholder' => $row['placeholder'] ?? null,
                    'group_name' => $row['group_name'],
                    'order' => $row['order'] ?? 0,
                    'is_active' => $this->parseBooleanValue($row['is_active'] ?? true),
                ]);

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    public function rules(): array
    {
        return [
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|in:likert,text',
            'placeholder' => 'nullable|string|max:255',
            'group_name' => 'required|in:basic_assessment,mood_emotion,anxiety_stress,sleep_energy,social_support,coping_strategy,life_quality,trauma_history,future_goals,custom',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'question_text.required' => 'Kolom question_text harus diisi',
            'question_text.max' => 'Kolom question_text maksimal 1000 karakter',
            'question_type.required' => 'Kolom question_type harus diisi',
            'question_type.in' => 'Kolom question_type harus berisi "likert" atau "text"',
            'placeholder.max' => 'Kolom placeholder maksimal 255 karakter',
            'group_name.required' => 'Kolom group_name harus diisi',
            'group_name.in' => 'Kolom group_name harus berisi salah satu nilai yang valid',
            'order.integer' => 'Kolom order harus berupa angka',
            'order.min' => 'Kolom order tidak boleh negatif',
            'is_active.boolean' => 'Kolom is_active harus berisi true/false atau 1/0',
        ];
    }

    private function validateRow(array $row, int $rowNumber): void
    {
        $validator = Validator::make($row, $this->rules(), $this->customValidationMessages());

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            throw new \Exception(implode(', ', $errors));
        }

        // Additional business logic validation
        if ($row['question_type'] === 'text' && empty($row['placeholder'])) {
            // Warning but not error - placeholder is optional for text type
        }
    }

    private function parseBooleanValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['true', '1', 'yes', 'ya', 'aktif', 'active']);
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return true; // Default to active
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getSummary(): array
    {
        return [
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'total_processed' => $this->successCount + $this->errorCount,
            'errors' => $this->errors,
        ];
    }
}
