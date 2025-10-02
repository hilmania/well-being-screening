<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\ScreeningForm;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\WellBeingScreening;
use App\Models\VolunteersResponse;
use App\Models\PsychologistResponse;
use Carbon\Carbon;

Route::get('/', function () {
    $chatbotSettings = [
        'chatbot_url' => Setting::get('chatbot_url', 'https://cdn.botpress.cloud/webchat/v3.2/shareable.html?configUrl=https://files.bpcontent.cloud/2025/09/22/15/20250922155518-LPZFIPF0.json'),
        'chatbot_enabled' => Setting::get('chatbot_enabled', '1') === '1',
        'chatbot_title' => Setting::get('chatbot_title', 'Assistant Kesehatan Mental'),
        'chatbot_auto_open_delay' => Setting::get('chatbot_auto_open_delay', '5'),
    ];

    // Data untuk chart statistics
    $totalResponden = WellBeingScreening::distinct('user_id')->count();
    $respondenDitanganiRelawan = WellBeingScreening::whereHas('volunteerResponses')->distinct('user_id')->count();
    $respondenDitanganiPsikolog = WellBeingScreening::whereHas('psychologistResponses')->distinct('user_id')->count();
    $totalScreening = WellBeingScreening::count();

    // Data untuk donut chart distribusi status
    $handledByVolunteer = WellBeingScreening::whereHas('volunteerResponses')->count();
    $handledByPsychologist = WellBeingScreening::whereHas('psychologistResponses')->count();
    $notHandled = $totalScreening - $handledByVolunteer;

    // Data untuk trend chart - 12 bulan terakhir
    $months = [];
    $screeningData = [];
    $volunteerData = [];
    $psychologistData = [];

    for ($i = 11; $i >= 0; $i--) {
        $month = Carbon::now()->subMonths($i);
        $months[] = $month->format('M Y');

        // Count screening per bulan
        $screeningCount = WellBeingScreening::whereMonth('created_at', $month->month)
            ->whereYear('created_at', $month->year)
            ->count();
        $screeningData[] = $screeningCount;

        // Count volunteer responses per bulan
        $volunteerCount = VolunteersResponse::whereMonth('created_at', $month->month)
            ->whereYear('created_at', $month->year)
            ->count();
        $volunteerData[] = $volunteerCount;

        // Count psychologist responses per bulan
        $psychologistCount = PsychologistResponse::whereMonth('created_at', $month->month)
            ->whereYear('created_at', $month->year)
            ->count();
        $psychologistData[] = $psychologistCount;
    }

    $chartData = [
        'stats' => [
            'total_responden' => $totalResponden,
            'ditangani_relawan' => $respondenDitanganiRelawan,
            'ditangani_psikolog' => $respondenDitanganiPsikolog,
            'total_screening' => $totalScreening,
        ],
        'distribution' => [
            'data' => [$handledByPsychologist, $handledByVolunteer - $handledByPsychologist, $notHandled],
            'labels' => ['Ditangani Psikolog', 'Ditangani Relawan', 'Belum Ditangani'],
            'colors' => ['#f59e0b', '#10b981', '#ef4444'],
        ],
        'trends' => [
            'datasets' => [
                [
                    'label' => 'Screening Dilakukan',
                    'data' => $screeningData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Ditangani Relawan',
                    'data' => $volunteerData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Ditangani Psikolog',
                    'data' => $psychologistData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ]
    ];

    return view('landing', compact('chatbotSettings', 'chartData'));
})->name('home');

Route::get('/screening', ScreeningForm::class)->name('screening');

// Route untuk download attachment files
Route::get('/download/attachment/{filename}', function (Request $request, $filename) {
    // Pastikan user sudah login dan memiliki akses
    if (!Auth::check()) {
        abort(401, 'Unauthorized');
    }

    // Clean filename untuk keamanan
    $filename = basename($filename);
    $path = 'volunteer-attachments/' . $filename;

    // Pastikan file ada
    if (!Storage::disk('public')->exists($path)) {
        // Log untuk debugging
        Log::info("File not found: " . $path);
        Log::info("Looking in: " . storage_path('app/public/' . $path));
        abort(404, 'File not found');
    }

    $fullPath = storage_path('app/public/' . $path);

    // Log untuk debugging
    Log::info("Downloading file: " . $fullPath);

    // Download file dengan nama asli
    return response()->download($fullPath, $filename);
})->name('download.attachment')->middleware('auth');

// Health check endpoint for production monitoring
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
    ]);
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
