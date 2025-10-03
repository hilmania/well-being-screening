<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ScreeningQuestion;

class OpenQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $openQuestions = [
            [
                'question_text' => 'Ceritakan tentang perasaan atau emosi yang paling dominan yang Anda rasakan dalam seminggu terakhir.',
                'question_type' => 'text',
                'placeholder' => 'Contoh: Saya merasa cemas karena pekerjaan yang menumpuk, atau saya merasa senang karena mendapat kabar baik...',
            ],
            [
                'question_text' => 'Apa hal yang paling membuat Anda merasa tertekan atau stres saat ini? Jelaskan secara detail.',
                'question_type' => 'text',
                'placeholder' => 'Jelaskan situasi, orang, atau kondisi yang membuat Anda merasa tertekan. Sertakan juga bagaimana perasaan Anda menghadapinya...',
            ],
            [
                'question_text' => 'Bagaimana cara Anda biasanya mengatasi rasa cemas atau khawatir? Strategi apa yang paling efektif untuk Anda?',
                'question_type' => 'text',
                'placeholder' => 'Contoh: Saya biasanya mendengarkan musik, berbicara dengan teman, olahraga, meditasi, atau cara lainnya...',
            ],
            [
                'question_text' => 'Ceritakan tentang dukungan sosial yang Anda miliki. Siapa saja orang-orang yang dapat Anda andalkan saat menghadapi masalah?',
                'question_type' => 'text',
                'placeholder' => 'Sebutkan keluarga, teman, partner, atau support system lainnya dan jelaskan bagaimana mereka membantu Anda...',
            ],
            [
                'question_text' => 'Apakah ada perubahan dalam pola tidur, makan, atau aktivitas harian Anda belakangan ini? Jelaskan perubahan tersebut.',
                'question_type' => 'text',
                'placeholder' => 'Contoh: Saya sulit tidur karena banyak pikiran, nafsu makan berkurang, atau saya jadi malas beraktivitas...',
            ],
            [
                'question_text' => 'Apa harapan atau tujuan yang ingin Anda capai untuk meningkatkan kesehatan mental Anda ke depannya?',
                'question_type' => 'text',
                'placeholder' => 'Contoh: Saya ingin lebih tenang menghadapi masalah, ingin tidur lebih nyenyak, atau ingin lebih percaya diri...',
            ],
        ];

        foreach ($openQuestions as $question) {
            ScreeningQuestion::create($question);
        }
    }
}
