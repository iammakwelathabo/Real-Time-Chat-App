<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Event: MessageSent
 *
 * This event is broadcast in real-time whenever a new message is sent
 * in a private or group chat. It ensures that all other users connected
 * to the same chat channel immediately receive the new message.
 */
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message model instance containing the chat message details.
     *
     * @var \App\Models\Message
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Message  $message
     *
     * The constructor automatically loads the related 'user' model
     * so that user details (e.g., name, avatar) can be included in
     * the broadcast payload for frontend display.
     */
    public function __construct(Message $message)
    {
        // Load related user data to include in broadcast
        $this->message = $message->load('user');

        // Optional: You can log the broadcast for debugging
        // Log::info('MessageSent event triggered', ['chat_id' => $message->chat_id, 'message_id' => $message->id]);
    }

    /**
     * Define which channel the event will be broadcast on.
     *
     * The channel name includes the chat ID, allowing each chat
     * to have its own dedicated real-time communication channel.
     *
     * Example channel: chat.5
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('chat.' . $this->message->chat_id);
    }

    /**
     * Define the name of the event to broadcast as.
     *
     * This will be used on the frontend when listening with Echo:
     * Echo.channel('chat.5').listen('.message.sent', callback)
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'message.sent';
    }

    /**
     * Define the payload data that will be sent with the broadcast.
     *
     * This makes it easy for the frontend to display the message
     * immediately without fetching it again from the database.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'message' => $this->message
        ];
    }
}
