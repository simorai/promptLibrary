<?php

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\ConversationShareController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ModelController;
use App\Http\Controllers\Api\PromptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    Route::get('models', [ModelController::class, 'index'])->name('api.models.index');

    Route::get('conversations', [ConversationController::class, 'index'])->name('api.conversations.index');
    Route::post('conversations', [ConversationController::class, 'store'])->name('api.conversations.store');
    Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('api.conversations.show');
    Route::patch('conversations/{conversation}', [ConversationController::class, 'update'])->name('api.conversations.update');
    Route::delete('conversations/{conversation}', [ConversationController::class, 'destroy'])->name('api.conversations.destroy');
    Route::post('conversations/{conversation}/pin', [ConversationController::class, 'pin'])->name('api.conversations.pin');
    Route::post('conversations/{conversation}/export', [ConversationController::class, 'export'])->name('api.conversations.export');
    Route::post('conversations/{conversation}/share', [ConversationShareController::class, 'generate'])->name('api.conversations.share.generate');
    Route::delete('conversations/{conversation}/share', [ConversationShareController::class, 'revoke'])->name('api.conversations.share.revoke');

    Route::post('messages', [MessageController::class, 'store'])->name('api.messages.store');
    Route::post('conversations/{conversation}/messages/stream', [MessageController::class, 'stream'])->name('api.messages.stream');

    Route::post('comments', [CommentController::class, 'store'])->name('api.comments.store');
    Route::patch('comments/{comment}', [CommentController::class, 'update'])->name('api.comments.update');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('api.comments.destroy');

    Route::get('prompts', [PromptController::class, 'index'])->name('api.prompts.index');
    Route::post('prompts', [PromptController::class, 'store'])->name('api.prompts.store');
    Route::patch('prompts/{prompt}', [PromptController::class, 'update'])->name('api.prompts.update');
    Route::delete('prompts/{prompt}', [PromptController::class, 'destroy'])->name('api.prompts.destroy');
});
