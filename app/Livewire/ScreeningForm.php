<?php

namespace App\Livewire;

use App\Models\ScreeningQuestion;
use App\Models\WellBeingScreening;
use App\Models\ScreeningAnswer;
use App\Models\User;
use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;

class ScreeningForm extends Component
{
    public string $name = '';
    public string $gender = '';
    public string $birth_date = '';
    public string $address = '';
    public string $phone = '';
    public array $answers = [];
    public ?Collection $questions = null;
    public ?string $result = null;
    public ?int $score = null;

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'gender' => 'required|in:male,female',
        'birth_date' => 'required|date|before:today',
        'address' => 'required|min:10|max:500',
        'phone' => 'required|regex:/^([0-9\s\-\+\(\)]+)$/|min:10|max:15',
        'answers.*' => 'required',
    ];

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
    }    protected function rules()
    {
        $rules = [
            'name' => 'required|min:2|max:255',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date|before:today',
            'address' => 'required|min:10|max:500',
            'phone' => ['required', 'regex:/^(\+?62|0)[0-9]{8,13}$/', 'min:10', 'max:15'],
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
                session()->flash('error', 'Tidak ada pertanyaan tersedia saat ini.');
                return;
            }

            // Create or find user with extended information
            $user = User::firstOrCreate(
                ['phone' => $this->phone], // Using phone as unique identifier
                [
                    'name' => $this->name,
                    'email' => $this->name . '@screening.local', // Generate email for compatibility
                    'gender' => $this->gender,
                    'birth_date' => $this->birth_date,
                    'address' => $this->address,
                    'phone' => $this->phone,
                    'password' => bcrypt('default123'),
                    'role' => 'responden',
                    'email_verified_at' => now(),
                ]
            );

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

            // Save answers
            foreach ($this->answers as $questionId => $answer) {
                ScreeningAnswer::create([
                    'screening_id' => $screening->id,
                    'question_id' => $questionId,
                    'answer' => $answer,
                ]);
            }

            // Set result for display
            $this->result = $resultText;
            $this->score = $totalScore;

            session()->flash('success', "Screening berhasil disimpan! Hasil: {$resultText}" . ($likertQuestions > 0 ? " (Skor: {$totalScore}/{$maxScore})" : ""));

            // Reset form
            $this->reset(['name', 'email', 'answers']);
            if ($this->questions && $this->questions->isNotEmpty()) {
                foreach ($this->questions as $question) {
                    $this->answers[$question->id] = null;
                }
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function render()
    {
        return view('livewire.screening-form')
            ->layout('layouts.guest');
    }
}
