<?php

use Illuminate\Support\Facades\Route;

// landing page
Route::get('/', function () {
    return view('home');
})->name('home');

// login
Route::get('/login', function () {
    return view('login');
})->name('login');

//register
Route::get('/register', function () {
    return view('register');
})->name('register');