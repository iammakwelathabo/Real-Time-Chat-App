<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * The attributes that are mass assignable
     *
     * These fields can be set when creating a message via mass assignment
     * - chat_id: The chat this message belongs to
     * - user_id: The user who sent the message
     * - body: The actual message content
     * - read_at: Timestamp indicating when the message was read (nullable)
     *
     * @var array<string>
     */
    protected $fillable = ['chat_id', 'user_id', 'body', 'read_at'];

    /**
     * Belongs-to relationship with the Chat model
     *
     * Each message belongs to exactly one chat (either one-on-one or group)
     * This allows easy access to the chat context for any message
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Belongs-to relationship with the User model
     *
     * Each message is sent by one user. This relationship provides
     * access to the sender's information (name, avatar, etc.)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Many-to-many relationship with users who have read the message
     *
     * Tracks read receipts for messages, particularly useful in group chats
     * to show which users have seen each message. Uses a pivot table
     * 'message_user_reads' with additional pivot data for read_at timestamp.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function readBy()
    {
        return $this->belongsToMany(User::class, 'message_user_reads')
            ->withPivot('read_at')  // Include the timestamp when the message was read
            ->withTimestamps();     // Include created_at and updated_at for the pivot record
    }
}
