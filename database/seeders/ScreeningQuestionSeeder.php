<?php

namespace Database\Seeders;

use App\Models\ScreeningQuestion;
use Illuminate\Database\Seeder;

class ScreeningQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            'Bagaimana perasaan Anda secara umum dalam 2 minggu terakhir?',
            'Seberapa sering Anda merasa sedih atau tertekan dalam seminggu terakhir?',
            'Apakah Anda mengalami kesulitan tidur atau tidur berlebihan?',
            'Bagaimana tingkat energi Anda dalam melakukan aktivitas sehari-hari?',
            'Seberapa sering Anda merasa cemas atau khawatir berlebihan?',
            'Apakah Anda merasa kehilangan minat pada hal-hal yang biasanya Anda nikmati?',
            'Bagaimana hubungan Anda dengan keluarga dan teman-teman?',
            'Seberapa sering Anda merasa sulit berkonsentrasi?',
            'Apakah Anda pernah memiliki pikiran untuk menyakiti diri sendiri?',
            'Bagaimana Anda menilai kualitas hidup Anda saat ini?'
        ];

        foreach ($questions as $question) {
            ScreeningQuestion::create([
                'question_text' => $question
            ]);
        }
    }
}
