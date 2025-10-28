<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: UserTyping
 *
 * This event is broadcast in real-time whenever a user starts or stops typing
 * in a private (one-on-one) chat conversation.
 *
 * It enables the frontend to show a “user is typing…” indicator in the chat UI
 * without requiring a page refresh or additional polling.
 */
class UserTyping implements ShouldBroadcast
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
     * The ID of the chat where typing is occurring.
     *
     * @var int
     */
    public $chatId;

    /**
     * Indicates whether the user is currently typing or has stopped.
     *
     * @var bool
     */
    public $typing;

    /**
     * Create a new event instance.
     *
     * @param int    $userId   The ID of the user who triggered the event.
     * @param string $userName The name of the user (used for display on UI).
     * @param int    $chatId   The chat ID where the typing activity occurred.
     * @param bool   $typing   True if typing started, false if typing stopped.
     *
     * This constructor initializes the typing event data which will be broadcast
     * to the specific chat channel so other participants can see typing activity.
     */
    public function __construct($userId, $userName, $chatId, $typing)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->chatId = $chatId;
        $this->typing = $typing;
    }

    /**
     * The broadcast channel for this event.
     *
     * @return \Illuminate\Broadcasting\Channel
     *
     * This event broadcasts over a **public channel** (e.g., `chat.{id}`),
     * allowing both participants in a chat to receive typing status updates.
     */
    public function broadcastOn()
    {
        return new Channel('chat.' . $this->chatId);
    }

    /**
     * The broadcast event name.
     *
     * @return string
     *
     * This name will be used on the frontend when listening via Echo:
     * ```js
     * Echo.channel(`chat.${chatId}`)
     *     .listen('.user.typing', (event) => { ... });
     * ```
     */
    public function broadcastAs()
    {
        return 'user.typing';
    }

    /**
     * The payload data that should be broadcast to listeners.
     *
     * @return array
     *
     * This data will be sent to the client-side so it can show or hide
     * the “typing…” indicator for the given user.
     */
    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->userId,
                'name' => $this->userName,
            ],
            'typing' => $this->typing,
        ];
    }
}
