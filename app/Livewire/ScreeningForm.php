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
    public string $email = '';
    public array $answers = [];
    public ?Collection $questions = null;
    public ?string $result = null;
    public ?int $score = null;

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'email' => 'required|email|max:255',
        'answers.*' => 'required|integer|min:1|max:5',
    ];

    protected $messages = [
        'name.required' => 'Nama harus diisi.',
        'email.required' => 'Email harus diisi.',
        'email.email' => 'Email tidak valid.',
        'answers.*.required' => 'Semua pertanyaan harus dijawab.',
        'answers.*.integer' => 'Jawaban harus berupa angka.',
        'answers.*.min' => 'Pilih salah satu jawaban.',
        'answers.*.max' => 'Pilih salah satu jawaban.',
    ];

    public function mount(): void
    {
        $this->questions = ScreeningQuestion::all();
        
        // Initialize answers array only if questions exist
        if ($this->questions && $this->questions->isNotEmpty()) {
            foreach ($this->questions as $question) {
                $this->answers[$question->id] = null;
            }
        }
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

            // Create or find user
            $user = User::firstOrCreate(
                ['email' => $this->email],
                [
                    'name' => $this->name,
                    'password' => bcrypt('default123'),
                    'role' => 'responden',
                    'email_verified_at' => now(),
                ]
            );

            // Calculate score
            $totalScore = 0;
            foreach ($this->answers as $answer) {
                $totalScore += (int)$answer;
            }

            // Determine result based on score
            $totalQuestions = $this->questions->count();
            $maxScore = $totalQuestions * 5;
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

            session()->flash('success', "Screening berhasil disimpan! Hasil: {$resultText} (Skor: {$totalScore}/{$maxScore})");

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
