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

Route::get('/', function () {
    return view('landing');
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
