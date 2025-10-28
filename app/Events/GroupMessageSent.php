<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: GroupMessageSent
 *
 * This event is broadcast whenever a new message is sent in a group chat.
 * It sends the message data in real time to all users subscribed to that chat channel.
 */
class GroupMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message instance being broadcast.
     *
     * @var \App\Models\Message
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Message  $message
     *
     * Loads the message along with the user who sent it,
     * so that both are included in the broadcast payload.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->message->load('user');
    }

    /**
     * Get the broadcast channel the event should go out on.
     *
     * Each group chat has its own private/public channel, named `group-chat.{id}`.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('group-chat.' . $this->message->chat_id);
    }

    /**
     * Specify a custom event name for the broadcast.
     *
     * By default, Laravel uses the class name, but we override it here for clarity.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'group.message.sent';
    }

    /**
     * Define the data that should be broadcast with the event.
     *
     * Returns both the message details and the associated user
     * so that the frontend can display the message immediately.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'user' => $this->message->user,
        ];
    }
}
