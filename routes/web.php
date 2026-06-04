<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Chat\TypingController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/chat', fn () => Inertia::render('Chat/Index'))->name('chat');

    Route::get('/chat/conversations', [ConversationController::class, 'index'])->name('chat.conversations.index');
    Route::post('/chat/conversations/direct', [ConversationController::class, 'direct'])->name('chat.conversations.direct');
    Route::post('/chat/conversations/group', [ConversationController::class, 'group'])->name('chat.conversations.group');
    Route::delete('/chat/conversations/{conversation}/leave', [ConversationController::class, 'leave'])->name('chat.conversations.leave');

    Route::post('/chat/conversations/{conversation}/members', [ConversationController::class, 'addMembers'])->name('chat.conversations.members.add');
    Route::delete('/chat/conversations/{conversation}/members/{user}', [ConversationController::class, 'removeMember'])->name('chat.conversations.members.remove');
    Route::patch('/chat/conversations/{conversation}/members/{user}/role', [ConversationController::class, 'updateMemberRole'])->name('chat.conversations.members.role');

    Route::get('/chat/conversations/{conversation}/messages', [MessageController::class, 'index'])->name('chat.messages.index');
    Route::post('/chat/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('chat.messages.store');
    Route::patch('/chat/messages/{message}', [MessageController::class, 'update'])->name('chat.messages.update');
    Route::delete('/chat/messages/{message}', [MessageController::class, 'destroy'])->name('chat.messages.destroy');
    Route::post('/chat/messages/{message}/read', [MessageController::class, 'read'])->name('chat.messages.read');

    Route::post('/chat/typing', [TypingController::class, 'store'])->name('chat.typing');
    Route::get('/chat/users', [ConversationController::class, 'users'])->name('chat.users');
});