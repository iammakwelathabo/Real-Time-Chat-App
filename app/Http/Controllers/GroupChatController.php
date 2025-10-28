<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use App\Events\GroupMessageSent;
use App\Events\GroupUserTyping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupChatController extends Controller
{
    /**
     * Display the group chat creation form
     *
     * Shows a form for creating new group chats and fetches all users
     * (excluding the current user) for member selection
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('group-chats.create', compact('users'));
    }

    /**
     * Store a newly created group chat in database
     *
     * Validates input, creates group chat record, attaches members,
     * and handles transaction rollback on failure
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            // Create group chat
            $chat = Chat::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_group' => true,
                'created_by' => Auth::id()
            ]);

            // Add members (including creator)
            $members = array_unique(array_merge([Auth::id()], $request->members));
            $chat->users()->attach($members);

            DB::commit();

            return redirect()->route('group-chats.show', $chat)
                ->with('success', 'Group chat created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create group chat: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve paginated messages for a group chat
     *
     * Supports both AJAX requests (returns JSON) and regular requests
     * (returns view). Used for loading message history.
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function messages(Chat $chat)
    {
        $messages = $chat->messages()
            ->with('user')
            ->latest()
            ->paginate(10);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'messages' => $messages->items(),
                'has_more' => $messages->hasMorePages(),
                'next_page_url' => $messages->nextPageUrl(),
            ]);
        }

        return view('group-chats.partials.messages', compact('messages'));
    }

    /**
     * Load more messages for infinite scrolling
     *
     * API endpoint for AJAX requests to load older messages
     * with pagination. Returns messages in descending order.
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadMoreMessages(Chat $chat)
    {
        if (!$chat->is_group) {
            return response()->json(['success' => false, 'error' => 'Invalid group chat'], 400);
        }

        $messages = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'success' => true,
            'messages' => $messages->items(),
            'hasMore' => $messages->hasMorePages(),
            'nextPage' => $messages->currentPage() + 1,
        ]);
    }

    /**
     * Display the main group chat interface
     *
     * Shows the group chat view with messages, member list,
     * and non-members for invitation. Includes authorization checks.
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\View\View
     */
    public function show(Chat $chat)
    {
        if (!$chat->is_group) {
            abort(404);
        }

        if (!$chat->users->contains(Auth::id())) {
            abort(403, 'You are not a member of this group.');
        }

        $chat->load(['users', 'creator']);

        $messages = $chat->messages()
            ->with('user', 'readBy')
            ->latest()
            ->paginate(20);

        $nonMembers = User::whereNotIn('id', $chat->users->pluck('id'))->get();

        return view('group-chats.show', compact('chat', 'messages', 'nonMembers'));
    }

    /**
     * Fetch messages via AJAX for real-time updates
     *
     * API endpoint specifically for fetching messages in JSON format
     * used by JavaScript for dynamic message loading
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchMessages(Chat $chat)
    {
        if (!$chat->is_group) {
            return response()->json(['error' => 'Invalid group chat'], 400);
        }

        $messages = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'messages' => $messages->items(),
            'next_page_url' => $messages->nextPageUrl(),
        ]);
    }

    /**
     * Store a new message in group chat
     *
     * Handles message creation with validation, authorization checks,
     * and real-time broadcasting to other group members
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeMessage(Request $request, Chat $chat)
    {
        try {
            \Log::info('Group storeMessage called', [
                'chat_id' => $chat->id,
                'user_id' => Auth::id(),
                'is_group' => $chat->is_group
            ]);

            // Verify it's a group chat
            if (!$chat->is_group) {
                \Log::error('Not a group chat', ['chat_id' => $chat->id]);
                return response()->json(['error' => 'This is not a group chat'], 400);
            }

            // Check if user is part of this chat
            if (!$chat->users->contains(Auth::id())) {
                \Log::error('User not in group', [
                    'user_id' => Auth::id(),
                    'chat_id' => $chat->id
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate(['body' => 'required|string|max:1000']);

            \Log::info('Creating group message', [
                'chat_id' => $chat->id,
                'user_id' => Auth::id(),
                'body' => $request->body
            ]);

            $message = $chat->messages()->create([
                'user_id' => Auth::id(),
                'body' => $request->body
            ]);

            $message->load('user');

            \Log::info('Group message created, about to broadcast', [
                'message_id' => $message->id
            ]);

            // Broadcast using group-specific event
            try {
                broadcast(new GroupMessageSent($message));
                \Log::info('Group message broadcast successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast group message: ' . $e->getMessage());
                // Don't fail the request if broadcasting fails
            }

            return response()->json($message);

        } catch (\Exception $e) {
            \Log::error('Group storeMessage error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle typing indicator events
     *
     * Broadcasts typing status to other group members in real-time
     * when a user starts or stops typing
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\JsonResponse
     */
    public function userTyping(Request $request, Chat $chat)
    {
        // Verify it's a group chat
        if (!$chat->is_group) {
            return response()->json(['error' => 'This is not a group chat'], 400);
        }

        // Check if user is part of this chat
        if (!$chat->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $typing = $request->input('typing', true);

        // Broadcast using group-specific event
        broadcast(new GroupUserTyping(
            Auth::id(),
            Auth::user()->name,
            $chat->id,
            $typing
        ))->toOthers();

        return response()->json(['status' => 'success', 'typing' => $typing]);
    }

    /**
     * Get paginated group chat messages
     *
     * Alternative message fetching endpoint that supports both
     * AJAX and regular requests with consistent pagination
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function getMessages(Chat $chat)
    {
        $messages = $chat->messages()->latest()->paginate(20);

        if(request()->ajax()){
            return response()->json([
                'success' => true,
                'messages' => $messages->items(),
                'hasMore' => $messages->hasMorePages(),
                'nextPage' => $messages->currentPage() + 1,
            ]);
        }

        return view('group-chats.show', compact('chat', 'messages'));
    }

    /**
     * Add new members to an existing group chat
     *
     * Allows group admins to add multiple users to the group
     * with validation to prevent duplicate members
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addMembers(Request $request, Chat $chat)
    {
        if (!$chat->is_group) {
            abort(404);
        }

        if (!$chat->isAdmin()) {
            abort(403, 'Only group admins can add members.');
        }

        $request->validate([
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id'
        ]);

        $newMembers = array_diff($request->members, $chat->users->pluck('id')->toArray());

        if (!empty($newMembers)) {
            $chat->users()->attach($newMembers);
        }

        return redirect()->back()
            ->with('success', count($newMembers) . ' members added to the group.');
    }

    /**
     * Remove a member from the group chat
     *
     * Handles member removal with safety checks to prevent
     * removing the last admin. Users can remove themselves.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Chat $chat
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeMember(Request $request, Chat $chat, User $user)
    {
        if (!$chat->is_group) {
            abort(404);
        }

        if (!$chat->isAdmin() && Auth::id() !== $user->id) {
            abort(403, 'You can only remove yourself from the group.');
        }

        // Prevent removing the last admin
        if ($chat->admins->contains($user->id) && $chat->admins->count() === 1) {
            return redirect()->back()
                ->with('error', 'Cannot remove the only admin from the group.');
        }

        $chat->users()->detach($user->id);
        $chat->admins()->detach($user->id);

        return redirect()->back()
            ->with('success', 'Member removed from the group.');
    }

    /**
     * Update group chat information
     *
     * Allows group admins to modify group name and description
     * with proper validation and authorization
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Chat $chat)
    {
        if (!$chat->is_group) {
            abort(404);
        }

        if (!$chat->isAdmin()) {
            abort(403, 'Only group admins can update group info.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500'
        ]);

        $chat->update($request->only(['name', 'description']));

        return redirect()->back()
            ->with('success', 'Group information updated successfully.');
    }

    /**
     * Allow user to leave a group chat
     *
     * Handles user leaving the group with safety checks to
     * prevent leaving if they are the only admin
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leave(Chat $chat)
    {
        if (!$chat->is_group) {
            abort(404);
        }

        $user = Auth::user();

        // If user is the last admin, prevent leaving
        if ($chat->admins->contains($user->id) && $chat->admins->count() === 1) {
            return redirect()->back()
                ->with('error', 'You are the only admin. Please assign another admin before leaving.');
        }

        $chat->users()->detach($user->id);
        $chat->admins()->detach($user->id);

        return redirect()->route('chats.index')
            ->with('success', 'You have left the group.');
    }

    /**
     * Check if user is admin of the group chat
     *
     * Helper method to determine admin status based on
     * group creation or admin role assignment
     *
     * @param \App\Models\Chat $chat
     * @param int|null $userId
     * @return bool
     */
    private function isAdmin(Chat $chat, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        return $chat->created_by == $userId;
    }
}
