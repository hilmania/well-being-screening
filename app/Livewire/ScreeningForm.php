<?php

namespace App\Livewire;

use App\Models\ScreeningQuestion;
use App\Models\WellBeingScreening;
use App\Models\ScreeningAnswer;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.guest')]

class ScreeningForm extends Component
{
    public string $name = '';
    public string $gender = '';
    public string $birth_date = '';
    public string $address = '';
    public string $phone = '';
    public array $answers = [];
    public bool $consent = false;
    public ?Collection $questions = null;
    public ?string $result = null;
    public ?int $score = null;

    protected $messages = [
        'name.required' => 'Nama lengkap harus diisi.',
        'name.min' => 'Nama lengkap minimal 2 karakter.',
        'gender.required' => 'Jenis kelamin harus dipilih.',
        'gender.in' => 'Jenis kelamin tidak valid.',
        'birth_date.required' => 'Tanggal lahir harus diisi.',
        'birth_date.date' => 'Format tanggal lahir tidak valid.',
        'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
        'address.required' => 'Alamat tinggal harus diisi.',
        'address.min' => 'Alamat tinggal minimal 10 karakter.',
        'phone.required' => 'Nomor telepon harus diisi.',
        'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format Indonesia (contoh: 081234567890 atau +6281234567890).',
        'phone.min' => 'Nomor telepon minimal 10 digit.',
        'phone.max' => 'Nomor telepon maksimal 15 digit.',
        'answers.*.required' => 'Semua pertanyaan harus dijawab.',
        'consent.accepted' => 'Anda harus menyetujui penggunaan data pribadi untuk melanjutkan.',
    ];

    public function mount(): void
    {
        $this->questions = ScreeningQuestion::activeOrdered()->get();

        // Initialize answers array only if questions exist
        if ($this->questions && $this->questions->isNotEmpty()) {
            foreach ($this->questions as $question) {
                $this->answers[$question->id] = null;
            }
        }
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|min:2|max:255',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date|before:today',
            'address' => 'required|min:10|max:500',
            'phone' => ['required', 'regex:/^(\+?62|0)[0-9]{8,13}$/', 'min:10', 'max:15'],
            'consent' => 'accepted',
        ];

        // Dynamic rules based on question type
        if ($this->questions && $this->questions->isNotEmpty()) {
            foreach ($this->questions as $question) {
                if ($question->question_type === 'likert') {
                    $rules['answers.' . $question->id] = 'required|integer|min:1|max:5';
                } else {
                    $rules['answers.' . $question->id] = 'required|string|min:1|max:1000';
                }
            }
        }

        return $rules;
    }

    public function submit()
    {
        $this->validate();

        try {
            // Check if questions exist
            if (!$this->questions || $this->questions->isEmpty()) {
                session()->flash('error', 'Tidak ada pertanyaan tersedia saat ini. Silakan hubungi administrator.');
                return;
            }

            // Debug: Log the answers array before processing
            Log::info('Answers array before processing', [
                'answers' => $this->answers,
                'questions_count' => $this->questions->count()
            ]);

            // Create new user for each screening submission
            // Generate unique email to avoid conflicts
            $uniqueEmail = $this->phone . '_' . time() . '@screening.local';

            $user = User::create([
                'name' => $this->name,
                'email' => $uniqueEmail,
                'gender' => $this->gender,
                'birth_date' => $this->birth_date,
                'address' => $this->address,
                'phone' => $this->phone,
                'password' => bcrypt('default123'),
                'role' => 'responden',
                'email_verified_at' => now(),
            ]);

            // Debug: Log user data to verify new user creation
            Log::info('New user created for screening', [
                'user_id' => $user->id,
                'input_data' => [
                    'name' => $this->name,
                    'gender' => $this->gender,
                    'birth_date' => $this->birth_date,
                    'address' => $this->address,
                    'phone' => $this->phone,
                ],
                'saved_data' => [
                    'name' => $user->name,
                    'gender' => $user->gender,
                    'birth_date' => $user->birth_date,
                    'address' => $user->address,
                    'phone' => $user->phone,
                ]
            ]);

            // Calculate score (only from Likert questions)
            $totalScore = 0;
            $likertQuestions = 0;
            foreach ($this->questions as $question) {
                if ($question->question_type === 'likert' && isset($this->answers[$question->id])) {
                    $totalScore += (int)$this->answers[$question->id];
                    $likertQuestions++;
                }
            }

            // Determine result based on score (only if there are Likert questions)
            if ($likertQuestions > 0) {
                $maxScore = $likertQuestions * 5;
                $percentage = ($totalScore / $maxScore) * 100;

                if ($percentage >= 80) {
                    $resultText = 'Sangat Baik';
                } elseif ($percentage >= 60) {
                    $resultText = 'Baik';
                } elseif ($percentage >= 40) {
                    $resultText = 'Cukup';
                } elseif ($percentage >= 20) {
                    $resultText = 'Kurang';
                } else {
                    $resultText = 'Sangat Kurang';
                }
            } else {
                // If no Likert questions, set a default result
                $resultText = 'Screening Completed';
                $maxScore = 0;
            }

            // Create screening record
            $screening = WellBeingScreening::create([
                'user_id' => $user->id,
                'screening_date' => now(),
                'score' => $totalScore,
                'result' => $resultText,
            ]);

            // Save answers - Store the answers array before reset
            $answersToSave = $this->answers;
            $savedAnswersCount = 0;

            foreach ($answersToSave as $questionId => $answer) {
                if ($answer !== null && $answer !== '') {
                    ScreeningAnswer::create([
                        'screening_id' => $screening->id,
                        'question_id' => $questionId,
                        'answer' => $answer,
                    ]);
                    $savedAnswersCount++;
                }
            }

            // Debug: Log how many answers were saved
            Log::info('Answers saved', [
                'screening_id' => $screening->id,
                'saved_answers_count' => $savedAnswersCount,
                'total_questions' => $this->questions->count()
            ]);

            // Set result for display
            $this->result = $resultText;
            $this->score = $totalScore;

            // Dispatch SweetAlert for success notification
            $this->dispatch('showSweetAlert', [
                'icon' => 'success',
                'title' => 'Screening Berhasil!',
                'text' => "Hasil: {$resultText}" . ($likertQuestions > 0 ? " (Skor: {$totalScore}/{$maxScore})" : "") . ". Jawaban tersimpan: {$savedAnswersCount}",
                'confirmButtonText' => 'Tutup',
                'timer' => 6000
            ]);

            // Keep session flash for compatibility
            session()->flash('success', "Screening berhasil disimpan! Hasil: {$resultText}" . ($likertQuestions > 0 ? " (Skor: {$totalScore}/{$maxScore})" : "") . " (Jawaban tersimpan: {$savedAnswersCount})");

            // Reset form AFTER successful save
            $this->name = '';
            $this->gender = '';
            $this->birth_date = '';
            $this->address = '';
            $this->phone = '';
            $this->consent = false;            // Reset answers array
            if ($this->questions && $this->questions->isNotEmpty()) {
                foreach ($this->questions as $question) {
                    $this->answers[$question->id] = null;
                }
            }

        } catch (\Exception $e) {
            // Log the actual error for debugging
            Log::error('Screening form submission error: ' . $e->getMessage(), [
                'user_data' => [
                    'name' => $this->name,
                    'phone' => $this->phone,
                ],
                'answers' => $this->answers,
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi. Error: ' . $e->getMessage());

            // Dispatch SweetAlert for error notification
            $this->dispatch('showSweetAlert', [
                'icon' => 'error',
                'title' => 'Terjadi Kesalahan',
                'text' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi atau hubungi administrator jika masalah berlanjut.',
                'confirmButtonText' => 'Tutup'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.screening-form');
    }
}
