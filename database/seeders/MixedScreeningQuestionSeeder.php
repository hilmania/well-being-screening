<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ScreeningQuestion;

class MixedScreeningQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus semua pertanyaan yang ada
        ScreeningQuestion::truncate();

        $questions = [
            // Grup: Assessment Dasar
            [
                'question_text' => 'Secara umum, bagaimana perasaan Anda dalam 2 minggu terakhir?',
                'question_type' => 'likert',
                'placeholder' => null,
                'group_name' => 'basic_assessment',
                'is_active' => true,
                'order' => 1,
            ],

            [
                'question_text' => 'Ceritakan lebih detail tentang perasaan atau emosi yang paling dominan yang Anda rasakan belakangan ini.',
                'question_type' => 'text',
                'placeholder' => 'Contoh: Saya merasa cemas karena pekerjaan yang menumpuk, merasa senang karena ada kabar baik, atau merasa sedih karena masalah keluarga...',
                'group_name' => 'mood_emotion',
                'is_active' => true,
                'order' => 2,
            ],

            // Grup: Kecemasan & Stress
            [
                'question_text' => 'Seberapa sering Anda merasa cemas atau khawatir dalam seminggu terakhir?',
                'question_type' => 'likert',
                'placeholder' => null,
                'group_name' => 'anxiety_stress',
                'is_active' => true,
                'order' => 3,
            ],

            [
                'question_text' => 'Apa hal yang paling membuat Anda merasa tertekan atau stres saat ini? Jelaskan secara detail.',
                'question_type' => 'text',
                'placeholder' => 'Jelaskan situasi, orang, atau kondisi yang membuat Anda merasa tertekan. Sertakan juga bagaimana dampaknya terhadap kehidupan sehari-hari Anda...',
                'group_name' => 'anxiety_stress',
                'is_active' => true,
                'order' => 4,
            ],

            // Grup: Tidur & Energi
            [
                'question_text' => 'Seberapa puas Anda dengan kualitas tidur Anda saat ini?',
                'question_type' => 'likert',
                'placeholder' => null,
                'group_name' => 'sleep_energy',
                'is_active' => true,
                'order' => 5,
            ],

            [
                'question_text' => 'Apakah ada perubahan dalam pola tidur, makan, atau aktivitas harian Anda belakangan ini? Jelaskan perubahan tersebut.',
                'question_type' => 'text',
                'placeholder' => 'Contoh: Saya sulit tidur karena banyak pikiran, nafsu makan berkurang, sering bangun malam, atau jadi malas beraktivitas...',
                'group_name' => 'sleep_energy',
                'is_active' => true,
                'order' => 6,
            ],

            // Grup: Dukungan Sosial
            [
                'question_text' => 'Seberapa puas Anda dengan dukungan sosial yang Anda terima dari orang-orang terdekat?',
                'question_type' => 'likert',
                'placeholder' => null,
                'group_name' => 'social_support',
                'is_active' => true,
                'order' => 7,
            ],

            [
                'question_text' => 'Ceritakan tentang dukungan sosial yang Anda miliki. Siapa saja orang-orang yang dapat Anda andalkan saat menghadapi masalah?',
                'question_type' => 'text',
                'placeholder' => 'Sebutkan keluarga, teman, partner, atau support system lainnya dan jelaskan bagaimana mereka membantu Anda...',
                'group_name' => 'social_support',
                'is_active' => true,
                'order' => 8,
            ],

            // Grup: Strategi Coping
            [
                'question_text' => 'Seberapa mampu Anda mengatasi stres dalam kehidupan sehari-hari?',
                'question_type' => 'likert',
                'placeholder' => null,
                'group_name' => 'coping_strategy',
                'is_active' => true,
                'order' => 9,
            ],

            [
                'question_text' => 'Bagaimana cara Anda biasanya mengatasi rasa cemas atau khawatir? Strategi apa yang paling efektif untuk Anda?',
                'question_type' => 'text',
                'placeholder' => 'Contoh: Saya biasanya mendengarkan musik, berbicara dengan teman, olahraga, meditasi, membaca, atau cara lainnya...',
                'group_name' => 'coping_strategy',
                'is_active' => true,
                'order' => 10,
            ],

            // Grup: Tujuan & Harapan
            [
                'question_text' => 'Apa harapan atau tujuan yang ingin Anda capai untuk meningkatkan kesehatan mental Anda ke depannya?',
                'question_type' => 'text',
                'placeholder' => 'Contoh: Saya ingin lebih tenang menghadapi masalah, ingin tidur lebih nyenyak, lebih percaya diri, atau memiliki lebih banyak waktu untuk diri sendiri...',
                'group_name' => 'future_goals',
                'is_active' => true,
                'order' => 11,
            ],

            // Contoh pertanyaan yang non-aktif (untuk demo)
            [
                'question_text' => 'Apakah Anda pernah mengalami trauma yang signifikan dalam hidup Anda?',
                'question_type' => 'text',
                'placeholder' => 'Anda tidak wajib menjawab pertanyaan ini. Jika berkenan, ceritakan secara singkat...',
                'group_name' => 'trauma_history',
                'is_active' => false, // Non-aktif untuk demo
                'order' => 12,
            ],
        ];

        foreach ($questions as $question) {
            ScreeningQuestion::create($question);
        }
    }
}
