<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: UserStatusUpdated
 *
 * This event is broadcast in real-time whenever a user's online status
 * changes (e.g., they log in, become active, go idle, or log out).
 *
 * It allows the frontend (e.g., chat app or user list) to update the
 * displayed status of users instantly without requiring manual refresh.
 */
class UserStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The user instance whose status is being updated.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Whether the user is currently online (true) or offline (false).
     *
     * @var bool
     */
    public $online;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\User $user  The user whose status changed.
     * @param bool             $online  Indicates the user's online state.
     *
     * This constructor initializes the event with the given user and
     * their new status. It will be triggered whenever user activity
     * changes — for example, on login or logout.
     */
    public function __construct(User $user, bool $online)
    {
        $this->user = $user;
        $this->online = $online;
    }

    /**
     * Determine which channel this event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     *
     * Using a PresenceChannel allows multiple authenticated users to
     * join and track each other’s online/offline status in real time.
     *
     * Example channel name: presence-online
     */
    public function broadcastOn(): Channel
    {
        return new PresenceChannel('presence-online');
    }

    /**
     * Define the event name for broadcasting.
     *
     * @return string
     *
     * This name will be used on the frontend when listening via Echo:
     * Echo.join('presence-online').listen('.user.status.updated', callback)
     */
    public function broadcastAs(): string
    {
        return 'user.status.updated';
    }

    /**
     * Define the payload data that will be sent with the broadcast.
     *
     * @return array
     *
     * This data is received on the client side and used to update
     * the user's status in real time (e.g., show green dot for online).
     */
    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'online' => $this->online,
        ];
    }
}
