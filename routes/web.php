<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Events\UserTyping;
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});



Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::post('/chats/{chat}/typing', [ChatController::class, 'typing'])->middleware('auth');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'delete'])->name('profile.destroy');

});

Route::middleware(['auth'])->group(function () {
    Route::get('/group-chats/{chat}/messages', [GroupChatController::class, 'fetchMessages'])
        ->name('group-chats.messages');
    Route::get('/group-chats/{chat}', [GroupChatController::class, 'show'])
        ->name('group-chats.show');
});



Route::post('/typing', function (Request $request) {
    event(new UserTyping(auth()->user(), $request->chat_id));
    return response()->json(['status' => 'ok']);
});

Route::get('/debug-user', function() {
    return response()->json([
        'user_id' => Auth::id(),
        'user_name' => Auth::user()->name,
        'chat_memberships' => Auth::user()->chats->pluck('id')
    ]);
})->middleware('auth');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/chats', function () {
        // Fire online event
        event(new \App\Events\UserStatusUpdated(auth()->user(), true));

        // Return chats page
        return view('chats.index');
    });

    $router->get('/chats/{chat}', function ($chatId) {
        event(new \App\Events\UserStatusUpdated(auth()->user(), true));

        $chat = \App\Models\Chat::findOrFail($chatId);
        return view('chats.show', compact('chat'));
    });
});

$router->post('/logout', function () use ($router) {
    if (auth()->check()) {
        event(new \App\Events\UserStatusUpdated(auth()->user(), false));
        auth()->logout();
    }

    return redirect('/');
});

// Include chat routes with auth middleware
require __DIR__.'/chat-routes.php';

require __DIR__.'/auth.php';
