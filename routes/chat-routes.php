<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GroupChatController;

// Remove any existing /users route
Route::get('/users', function () {
    abort(404);
})->name('users.remove');

// Then define the proper route
Route::middleware(['auth'])->group(function () {
    Route::get('/users', [ChatController::class, 'users'])->name('users.index');
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/{user}', [ChatController::class, 'createOrGetPrivateChat'])->name('chats.create.private');
    Route::get('/chats/show/{chat}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/chats/{chat}/messages', [ChatController::class, 'storeMessage'])->name('messages.store');
    Route::post('/messages/{message}/read', [ChatController::class, 'markAsRead'])->name('messages.mark-read');



// Group chats
    Route::get('/group-chats/create', [GroupChatController::class, 'create'])->name('group-chats.create');
    Route::post('/group-chats', [GroupChatController::class, 'store'])->name('group-chats.store');

    Route::post('/group-chats/{chat}/members', [GroupChatController::class, 'addMembers'])->name('group-chats.add-members');
     Route::put('/group-chats/{chat}', [GroupChatController::class, 'update'])->name('group-chats.update');
    Route::post('/group-chats/{chat}/leave', [GroupChatController::class, 'leave'])->name('group-chats.leave');

    Route::post('/group-chats/{chat}/messages', [GroupChatController::class, 'storeMessage'])->name('group-messages.store');

    Route::get('/group-chats/{chat}/messages', [GroupChatController::class, 'getMessages'])->name('group-messages.index');

});
Route::post('/group-chats/{chat}/typing', [GroupChatController::class, 'userTyping'])->name('group-typing.update');
Route::post('/chats/{chat}/typing', [ChatController::class, 'userTyping'])->name('typing.update');
Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('chats.show');
Route::get('/group-chats/{chat}', [GroupChatController::class, 'show'])->name('group-chats.show');

