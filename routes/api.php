<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/medicine/add', [MedicineController::class, 'addMedicine'])->name('medicine.add');
Route::post('/patient/analyze-symptoms', [PatientController::class, 'analyzeImage'])->name('medicine.analyze.image');