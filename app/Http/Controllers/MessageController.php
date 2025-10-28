<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Chat;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Store a newly created message and broadcast it in real-time
     *
     * Handles message creation for one-on-one chats with validation,
     * automatic user association, and real-time broadcasting to
     * other participants in the chat
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Chat $chat)
    {
        // Validate message content
        $request->validate(['body' => 'required|string']);

        // Create message associated with current user and chat
        $message = $chat->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ])->load('user'); // Eager load user relationship for frontend display

        // Broadcast message to other chat participants (excluding sender)
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['message' => $message]);
    }

    /**
     * Fetch all messages for a specific chat in chronological order
     *
     * Retrieves complete message history for a chat, including user data,
     * sorted from oldest to newest. Used for initial chat load or history view.
     *
     * @param int $chatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($chatId)
    {
        $messages = Message::where('chat_id', $chatId)
            ->with('user') // Include user details to avoid N+1 queries
            ->orderBy('created_at', 'asc') // Oldest first for chronological display
            ->get();

        return response()->json($messages);
    }
}
