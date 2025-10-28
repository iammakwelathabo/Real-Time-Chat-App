<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('presence-online', function ($user) {
    return Auth::check() ? ['id' => $user->id, 'name' => $user->name] : false;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::find($chatId);

    // Only allow users that belong to this chat
    return $chat && $chat->users->contains($user->id);
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Only allow if user is part of this chat
    return \App\Models\Chat::find($chatId)?->users->contains($user->id);
});
