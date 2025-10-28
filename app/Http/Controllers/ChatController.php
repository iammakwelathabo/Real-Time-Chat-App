<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\UserTyping;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Display a list of all chats for the authenticated user.
     *
     * Loads each chat with its users, latest message, and total message count.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $chats = $user->chats()->with(['users', 'latestMessage'])->withCount('messages')->get();

        return view('chats.index', compact('chats'));
    }

    /**
     * Display a list of all users available for private chat.
     *
     * Excludes the currently authenticated user.
     *
     * @return \Illuminate\View\View
     */
    public function users()
    {
        // Get all users except the authenticated one
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at']);

        return view('chats.users', compact('users'));
    }

    /**
     * Create or retrieve a private chat between the authenticated user and another user.
     *
     * If a chat already exists between the two users, it is reused;
     * otherwise, a new one is created and both users are attached.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createOrGetPrivateChat(User $user)
    {
        $currentUser = Auth::user();

        // Prevent chatting with self
        if ($currentUser->id === $user->id) {
            return redirect()->back()->with('error', 'Cannot start a chat with yourself.');
        }

        // Find existing private chat between these two users
        $chat = Chat::where('is_group', false)
            ->whereHas('users', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        // Create new chat if none exists
        if (!$chat) {
            $chat = Chat::create(['is_group' => false]);
            $chat->users()->attach([$currentUser->id, $user->id]);
        }

        return redirect()->route('chats.show', $chat);
    }

    /**
     * Display the selected chat and its messages.
     *
     * Loads messages with their senders, marks unread messages as read,
     * and broadcasts the read event to other users in real time.
     *
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\View\View|\Illuminate\Http\Response|string
     */
    public function show(Chat $chat)
    {
        $userId = Auth::id();

        // Ensure authorized access
        if (!$chat->users->contains($userId)) {
            abort(403, 'Unauthorized access to this chat.');
        }

        // Redirect group chats to their controller
        if ($chat->is_group) {
            return redirect()->route('group-chats.show', $chat);
        }

        // Update "last seen" timestamp for this user
        DB::table('chat_user_last_seen')->updateOrInsert(
            ['chat_id' => $chat->id, 'user_id' => $userId],
            ['last_seen_at' => now()]
        );

        // Load messages (latest first)
        $messages = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Mark unread messages as read and broadcast
        foreach ($messages as $message) {
            if ($message->user_id != $userId && !$message->readBy->contains($userId)) {
                $message->readBy()->syncWithoutDetaching([
                    $userId => ['read_at' => now()]
                ]);

                broadcast(new MessageRead($message, $userId))->toOthers();
            }
        }

        // For AJAX requests, return only the messages view
        if (request()->ajax()) {
            return view('chats.messages', compact('messages'))->render();
        }

        // For normal requests, return the full chat view
        return view('chats.show', [
            'chat' => $chat,
            'messages' => $messages ?? collect()
        ]);
    }

    /**
     * Store a new message in a chat and broadcast it.
     *
     * Validates the message body, creates it, and emits a MessageSent event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeMessage(Request $request, Chat $chat)
    {
        $chat->load('users');

        // Ensure user is part of this chat
        if (!$chat->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate message input
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        // Create the message record
        $message = $chat->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        $message->load('user');

        // Broadcast message event
        event(new \App\Events\MessageSent($message));

        return response()->json($message);
    }

    /**
     * Mark all unread messages in a chat as read for the current user.
     *
     * Also broadcasts the MessageRead event to other users.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Message $message)
    {
        $userId = Auth::id();

        // Ensure user is part of the chat
        if (!$message->chat->users->contains($userId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat = $message->chat;

        // Fetch all unread messages
        $unreadMessages = $chat->messages()
            ->where('user_id', '!=', $userId)
            ->whereDoesntHave('readBy', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        // Mark each message as read and broadcast
        foreach ($unreadMessages as $msg) {
            $msg->readBy()->syncWithoutDetaching([
                $userId => ['read_at' => now()]
            ]);

            broadcast(new MessageRead($msg, $userId))->toOthers();
        }

        return response()->json([
            'status' => 'success',
            'read_message_ids' => $unreadMessages->pluck('id')
        ]);
    }

    /**
     * Broadcast typing indicator when the current user is typing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\JsonResponse
     */
    public function userTyping(Request $request, Chat $chat)
    {
        // Ensure the user is part of this chat
        if (!$chat->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $typing = $request->input('typing', true);
        $currentUser = Auth::user();

        // Broadcast typing state to others
        broadcast(new UserTyping($currentUser->id, $currentUser->name, $chat->id, $typing));

        return response()->json(['status' => 'success', 'typing' => $typing]);
    }

    /**
     * Broadcast stop typing event when the user stops typing.
     *
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function stopTyping(Chat $chat)
    {
        broadcast(new UserTyping($chat->id, Auth::user()->forceFill(['name' => ''])))->toOthers();
        return response()->noContent();
    }
}
