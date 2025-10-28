<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Event: GroupUserTyping
 *
 * This event is broadcast when a user starts or stops typing
 * in a group chat. It allows other group members to see a
 * "User is typing..." indicator in real time.
 */
class GroupUserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The ID of the user who is typing.
     *
     * @var int
     */
    public $userId;

    /**
     * The name of the user who is typing.
     *
     * @var string
     */
    public $userName;

    /**
     * The ID of the group chat where typing occurs.
     *
     * @var int
     */
    public $chatId;

    /**
     * Whether the user is typing (true) or has stopped typing (false).
     *
     * @var bool
     */
    public $typing;

    /**
     * Create a new event instance.
     *
     * @param  int     $userId
     * @param  string  $userName
     * @param  int     $chatId
     * @param  bool    $typing
     *
     * Logs the event creation for debugging and stores typing data.
     */
    public function __construct($userId, $userName, $chatId, $typing)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->chatId = $chatId;
        $this->typing = $typing;

        // Log the event creation (useful for debugging Reverb broadcasts)
        Log::info('GroupUserTyping event created', [
            'user_id' => $userId,
            'chat_id' => $chatId,
            'typing' => $typing,
            'channel' => 'group-chat.' . $chatId
        ]);
    }

    /**
     * Get the broadcast channel the event should go out on.
     *
     * Each group chat has its own channel, named `group-chat.{id}`.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('group-chat.' . $this->chatId);
    }

    /**
     * Specify the event name to use when broadcasting.
     *
     * This name is used by the frontend listener when subscribing.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'group.user.typing';
    }

    /**
     * Define the data that should be broadcast with the event.
     *
     * Includes user details, typing state, and the chat ID.
     * This payload is received by the frontend in real time.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->userId,
                'name' => $this->userName
            ],
            'typing' => $this->typing,
            'chat_id' => $this->chatId
        ];
    }
}
