<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\NoteController;
use App\Http\Controllers\TagController;

Route::get('/', [NoteController::class, 'index']);
Route::get('/notes/{id}', [NoteController::class, 'show'])->name('notes.show');
// タグ候補API
Route::get('/tags', [TagController::class, 'index']);