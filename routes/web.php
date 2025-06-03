<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/medicine/add', function () {
    return view('medicine.add');
});
// ->middleware('auth');