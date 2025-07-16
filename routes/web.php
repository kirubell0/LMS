<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ListController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;

Route::get('/', function () {
    return Inertia::render('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('lists', ListController::class);
    Route::resource('tasks', TaskController::class);
    Route::get('letters/{letter}/pdf', [LetterController::class, 'generatePDF'])->name('letters.pdf');
    Route::get('/tasks/{task}/print-dialog', [TaskController::class, 'printDialog'])->name('tasks.printDialog');
    Route::get('tasks/{letter}/show', [TaskController::class, 'preview'])->name('tasks.show');
    Route::get('/tasks/{task}/print', [TaskController::class, 'printPDF'])->name('tasks.printPDF');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';    
