<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Group chat messages with infinite scrolling
    Route::get('/group-chats/{chat}/messages', [GroupChatController::class, 'loadMoreMessages']);

    // You can add more group chat API routes here
    Route::post('/group-chats/{chat}/messages', [GroupChatController::class, 'sendMessage']);
    Route::post('/group-chats/{chat}/typing', [GroupChatController::class, 'typing']);
});

// Or without prefix:
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/group-chats/{chat}/messages', [GroupChatController::class, 'loadMoreMessages']);
    Route::post('/group-chats/{chat}/messages', [GroupChatController::class, 'sendMessage']);
    Route::post('/group-chats/{chat}/typing', [GroupChatController::class, 'typing']);
});

