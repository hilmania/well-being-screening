<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WellBeingScreening;
use App\Models\ScreeningAnswer;
use App\Models\VolunteersResponse;
use App\Models\PsychologistResponse;
use App\Models\ScreeningQuestion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat beberapa responden tambahan
        $responden1 = User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => bcrypt('password'),
            'role' => 'responden',
        ]);

        $responden2 = User::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
            'role' => 'responden',
        ]);

        $responden3 = User::create([
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'password' => bcrypt('password'),
            'role' => 'responden',
        ]);

        // Ambil user yang sudah ada
        $existingResponden = User::where('role', 'responden')->first();
        $relawan = User::where('role', 'relawan')->first();
        $psikolog = User::where('role', 'psikolog')->first();

        // Buat data screening untuk berbagai waktu
        $screenings = [];
        
        // Screening bulan ini
        $screening1 = WellBeingScreening::create([
            'user_id' => $responden1->id,
            'screening_date' => Carbon::now()->subDays(5),
            'score' => 75,
            'result' => 'Kondisi mental cukup baik, namun perlu perhatian pada tingkat stress',
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $screening2 = WellBeingScreening::create([
            'user_id' => $responden2->id,
            'screening_date' => Carbon::now()->subDays(3),
            'score' => 85,
            'result' => 'Kondisi mental baik, tidak ada indikasi masalah serius',
            'created_at' => Carbon::now()->subDays(3),
        ]);

        $screening3 = WellBeingScreening::create([
            'user_id' => $responden3->id,
            'screening_date' => Carbon::now()->subDays(1),
            'score' => 60,
            'result' => 'Perlu perhatian lebih, terdapat indikasi stress dan kecemasan',
            'created_at' => Carbon::now()->subDays(1),
        ]);

        // Screening bulan lalu
        $screening4 = WellBeingScreening::create([
            'user_id' => $existingResponden->id,
            'screening_date' => Carbon::now()->subMonth()->subDays(10),
            'score' => 70,
            'result' => 'Kondisi mental cukup stabil',
            'created_at' => Carbon::now()->subMonth()->subDays(10),
        ]);

        // Buat jawaban untuk screening
        $questions = ScreeningQuestion::all();
        foreach ([$screening1, $screening2, $screening3, $screening4] as $screening) {
            foreach ($questions->take(5) as $question) {
                ScreeningAnswer::create([
                    'screening_id' => $screening->id,
                    'question_id' => $question->id,
                    'answer' => fake()->randomElement([
                        'Sangat setuju',
                        'Setuju', 
                        'Netral',
                        'Tidak setuju',
                        'Sangat tidak setuju'
                    ]),
                ]);
            }
        }

        // Buat respons relawan
        if ($relawan) {
            VolunteersResponse::create([
                'screening_id' => $screening1->id,
                'volunteer_id' => $relawan->id,
                'notes' => 'Responden menunjukkan gejala stress ringan. Disarankan untuk melakukan konseling lebih lanjut.',
                'created_at' => Carbon::now()->subDays(4),
            ]);

            VolunteersResponse::create([
                'screening_id' => $screening2->id,
                'volunteer_id' => $relawan->id,
                'notes' => 'Kondisi responden baik. Tidak ada tanda-tanda masalah mental yang serius.',
                'created_at' => Carbon::now()->subDays(2),
            ]);

            VolunteersResponse::create([
                'screening_id' => $screening4->id,
                'volunteer_id' => $relawan->id,
                'notes' => 'Responden dalam kondisi stabil, namun perlu monitoring berkala.',
                'created_at' => Carbon::now()->subMonth()->subDays(8),
            ]);
        }

        // Buat respons psikolog
        if ($psikolog) {
            PsychologistResponse::create([
                'screening_id' => $screening1->id,
                'psychologist_id' => $psikolog->id,
                'diagnosis' => 'Gangguan kecemasan ringan',
                'recommendation' => 'Terapi kognitif behavioral 6 sesi, latihan relaksasi, dan monitoring rutin.',
                'created_at' => Carbon::now()->subDays(3),
            ]);

            PsychologistResponse::create([
                'screening_id' => $screening4->id,
                'psychologist_id' => $psikolog->id,
                'diagnosis' => 'Stress adaptasi',
                'recommendation' => 'Konseling suportif dan teknik manajemen stress.',
                'created_at' => Carbon::now()->subMonth()->subDays(7),
            ]);
        }
    }
}
