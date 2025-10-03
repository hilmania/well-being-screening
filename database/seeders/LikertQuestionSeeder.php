<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ScreeningQuestion;

class LikertQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $likertQuestions = [
            [
                'question_text' => 'Seberapa sering Anda merasa cemas atau khawatir dalam 2 minggu terakhir?',
                'question_type' => 'likert',
                'placeholder' => null,
            ],
            [
                'question_text' => 'Seberapa puas Anda dengan kualitas tidur Anda saat ini?',
                'question_type' => 'likert',
                'placeholder' => null,
            ],
            [
                'question_text' => 'Seberapa sering Anda merasa sedih atau murung dalam seminggu terakhir?',
                'question_type' => 'likert',
                'placeholder' => null,
            ],
            [
                'question_text' => 'Seberapa mampu Anda mengatasi stres dalam kehidupan sehari-hari?',
                'question_type' => 'likert',
                'placeholder' => null,
            ],
            [
                'question_text' => 'Seberapa puas Anda dengan dukungan sosial yang Anda terima dari orang-orang terdekat?',
                'question_type' => 'likert',
                'placeholder' => null,
            ],
        ];

        foreach ($likertQuestions as $question) {
            ScreeningQuestion::create($question);
        }
    }
}
