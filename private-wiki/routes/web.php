<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BugTimelineController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// 認証ルート
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 認証が必要なルート
Route::middleware('auth')->group(function () {
    Route::get('/', [NoteController::class, 'index']);
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{id}', [NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes/{id}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{id}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::get('/notes/{id}/history', [NoteController::class, 'history'])->name('notes.history');
    Route::post('/notes/{id}/restore/{version}', [NoteController::class, 'restore'])->name('notes.restore');
    
    // タグ候補API
    Route::get('/tags', [TagController::class, 'index']);
    
    // バグタイムライン
    Route::get('/bug-timeline', [BugTimelineController::class, 'index'])->name('bug-timeline.index');
    Route::post('/bug-timeline', [BugTimelineController::class, 'store'])->name('bug-timeline.store');
    Route::patch('/bug-timeline/{bugReport}/status', [BugTimelineController::class, 'updateStatus'])->name('bug-timeline.update-status');
    Route::delete('/bug-timeline/{bugReport}', [BugTimelineController::class, 'destroy'])->name('bug-timeline.destroy');
    
    // Markdown変換API
    Route::post('/api/markdown', [NoteController::class, 'convertMarkdown']);
});
