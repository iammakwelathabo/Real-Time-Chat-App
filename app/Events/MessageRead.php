<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: MessageRead
 *
 * This event is broadcast in real time when a user reads a message
 * in a private or group chat. It allows other participants to update
 * the message status (e.g., mark it as "seen" or display read receipts).
 */
class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message that was read.
     *
     * @var \App\Models\Message
     */
    public $message;

    /**
     * The ID of the user who read the message.
     *
     * @var int
     */
    public $userId;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Message  $message
     * @param  int  $userId
     *
     * The constructor loads the associated user relation for the message
     * and stores the ID of the user who performed the read action.
     */
    public function __construct(Message $message, $userId)
    {
        // Load related user to include in broadcast payload
        $this->message = $message->load('user');

        // The user who read the message
        $this->userId = $userId;
    }

    /**
     * Determine which channel the event should be broadcast on.
     *
     * Each chat has its own channel in the format: `chat.{chatId}`.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('chat.' . $this->message->chat_id);
    }

    /**
     * Define the name of the broadcast event.
     *
     * This will be used on the frontend when listening for the event.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'message.read';
    }

    /**
     * (Optional) Define the data to send with the broadcast.
     *
     * You could include message and reader info for more detailed updates.
     * Uncomment if you want to include extra payload data.
     */
     public function broadcastWith()
     {
         return [
             'message' => $this->message,
             'read_by' => $this->userId,
         ];
     }
}
