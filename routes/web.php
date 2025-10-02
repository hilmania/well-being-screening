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

Route::get('/', function () {
    $chatbotSettings = [
        'chatbot_url' => Setting::get('chatbot_url', 'https://cdn.botpress.cloud/webchat/v3.2/shareable.html?configUrl=https://files.bpcontent.cloud/2025/09/22/15/20250922155518-LPZFIPF0.json'),
        'chatbot_enabled' => Setting::get('chatbot_enabled', '1') === '1',
        'chatbot_title' => Setting::get('chatbot_title', 'Assistant Kesehatan Mental'),
        'chatbot_auto_open_delay' => Setting::get('chatbot_auto_open_delay', '5'),
    ];

    return view('landing', compact('chatbotSettings'));
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
