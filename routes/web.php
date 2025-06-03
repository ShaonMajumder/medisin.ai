<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('patient_analyses/{filename}', function ($filename) {
    $path = storage_path('app/private/patient_analyses/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path); // or ->download($path) for downloads
})->name('analysis.image.view');

Route::get('/medicine/add', [MedicineController::class, 'showAddForm']);

// ->middleware('auth');