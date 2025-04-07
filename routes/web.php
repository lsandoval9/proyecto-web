<?php

use Illuminate\Support\Facades\Route;

// Ruta principal (Dashboard)
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/students', function () {
    return view('students.index');
})->name('students.index');

Route::get('/subjects', function () {
    return view('subjects.index');
})->name('subjects.index');

Route::get('/enrollments', function () {
    return view('enrollments.index');
})->name('enrollments.index');

Route::get('/grades', function () {
    return view('grades.index');
})->name('grades.index');