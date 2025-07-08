<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
//  Add your custom routes here

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // add routes for storage links
    Route::get('/storage-link', function () {
        // Step 1: Delete existing 'public/storage' link or folder
        $publicStoragePath = public_path('storage');

        // Step 1: Check if 'public/storage' exists
        if (File::exists($publicStoragePath)) {
            // Step 2: Only delete if it's NOT a symbolic link
            if (!is_link($publicStoragePath)) {
                // If it's a real directory, delete it
                if (File::isDirectory($publicStoragePath)) {
                    File::deleteDirectory($publicStoragePath);
                } elseif (File::isFile($publicStoragePath)) {
                    unlink($publicStoragePath);
                }
            }
        }

        Artisan::call('storage:link');
        return response()->json(['message' => 'Storage link created successfully.']);
    })->name('storage.link');

    // add rotes for clearing cache
    Route::get('/clear-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return response()->json(['message' => 'Cache cleared successfully.']);
    })->name('cache.clear');

});
