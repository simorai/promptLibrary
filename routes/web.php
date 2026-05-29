<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\ConversationShareController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::inertia('conversations', 'Conversations')->name('conversations.index');
    Route::inertia('conversations/{id}', 'Conversations/Show')->name('conversations.show');
    Route::inertia('prompts', 'Prompts/Index')->name('prompts.index');
});

Route::get('share/{token}', [ConversationShareController::class, 'show'])->name('share.show');

require __DIR__.'/settings.php';
