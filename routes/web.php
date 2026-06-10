<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeminiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ArticleController::class, 'index'])->name('home');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/gemini/analyze-log', [GeminiController::class, 'analyzeLog'])->name('gemini.analyze-log');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/articles/create', [AdminController::class, 'create'])->name('articles.create');
    Route::post('/articles', [AdminController::class, 'store'])->name('articles.store');
    Route::get('/articles/{article}/edit', [AdminController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [AdminController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [AdminController::class, 'destroy'])->name('articles.destroy');
    Route::post('/gemini/generate-draft', [GeminiController::class, 'generateDraft'])->name('gemini.generate-draft');
});
