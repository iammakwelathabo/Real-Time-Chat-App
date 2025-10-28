<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    /**
     * The attributes that are mass assignable
     *
     * @var array<string>
     */
    protected $fillable = ['name', 'is_group', 'description', 'created_by', 'avatar'];

    /**
     * Many-to-many relationship with users
     *
     * Represents all users participating in this chat (both group and one-on-one)
     * Includes timestamps to track when users joined the chat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * One-to-many relationship with messages
     *
     * A chat can have many messages, each message belongs to one chat
     * Used to retrieve all messages in chronological order
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Relationship to get the most recent message in the chat
     *
     * Useful for displaying previews in chat lists
     * Uses latest() scope to get the most recently created message
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Get the other participant in a one-on-one chat
     *
     * For non-group chats, returns the user who isn't the current authenticated user
     * Returns null for group chats since they have multiple participants
     *
     * @return \App\Models\User|null
     */
    public function otherUser()
    {
        if ($this->is_group) {
            return null;
        }

        return $this->users()->where('user_id', '!=', auth()->id())->first();
    }

    /**
     * Relationship to the user who created the chat
     *
     * Particularly relevant for group chats where we track the creator/admin
     * Uses 'created_by' as the foreign key on the chats table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Many-to-many relationship with admin users
     *
     * Represents users who have administrative privileges in group chats
     * Uses a pivot table 'chat_admins' to track admin assignments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany(User::class, 'chat_admins')->withTimestamps();
    }

    /**
     * Check if a user is a member of this chat
     *
     * Verifies whether a specific user (or current user by default)
     * is participating in this chat. Works for both group and one-on-one chats.
     *
     * @param int|null $userId
     * @return bool
     */
    public function isMember($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->users()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a user is an admin of this group chat
     *
     * Verifies admin privileges for group chats. Returns false for one-on-one chats
     * or if the user doesn't have admin rights. Uses current user by default.
     *
     * @param int|null $userId
     * @return bool
     */
    public function isAdmin($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->admins()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a user is the creator of this chat
     *
     * Determines if the specified user (or current user by default)
     * is the original creator of the chat. Particularly important
     * for group chat administration.
     *
     * @param int|null $userId
     * @return bool
     */
    public function isCreator($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->created_by == $userId;
    }
}
