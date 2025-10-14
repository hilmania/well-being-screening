<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

// Add this route to handle file downloads
Route::get('/download/volunteer-attachment/{filename}', function ($filename) {
    $filePath = 'volunteer-attachments/' . $filename;

    if (!Storage::disk('public')->exists($filePath)) {
        abort(404, 'File not found');
    }

    $fullPath = Storage::disk('public')->path($filePath);

    return response()->download($fullPath, $filename, [
        'Content-Type' => 'text/csv',
    ]);
})->name('download.volunteer-attachment');
