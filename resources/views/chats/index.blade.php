<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-purple-800 leading-tight">
            {{ __('My Chats') }}
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-6">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-purple-800">Your Chats</h1>
            <a href="{{ route('users.index') }}"
               class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition duration-150 font-semibold">
                Start New Chat
            </a>
        </div>

        @if($chats->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($chats as $chat)
                    <div class="bg-white rounded-lg shadow-md border border-purple-100 hover:shadow-lg transition duration-150">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                        @if($chat->is_group)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            </div>
                                        @else
                                            {{ strtoupper(substr($chat->otherUser()->name ?? 'U', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-purple-900">
                                            @if($chat->is_group)
                                                {{ $chat->name ?? 'Group Chat' }}
                                            @else
                                                {{ $chat->otherUser()->name ?? 'Unknown User' }}
                                            @endif
                                        </h3>
                                        <p class="text-sm text-purple-600">
                                            @if($chat->is_group)
                                                {{ $chat->users->count() }} members
                                            @else
                                                {{ $chat->otherUser()->email ?? '' }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if($chat->latestMessage)
                                <div id="chat-item-{{ $chat->id }}" class="p-4 border-b cursor-pointer">
                                    <p class="text-gray-600 mb-2 truncate">
                                        <span class="font-semibold text-purple-700">
                                            {{ $chat->latestMessage->user_id == auth()->id() ? 'You: ' : $chat->latestMessage->user->name . ': ' }}
                                        </span>
                                        {{ $chat->latestMessage->body }}
                                    </p>
                                    <p id="unread-count-{{ $chat->id }}" class="font-semibold text-xs text-red-500 ml-2 mb-2"></p>
                                    <p class="text-xs text-purple-500">
                                        {{ $chat->latestMessage->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            @else
                                <p class="text-gray-400 mb-4 italic">No messages yet</p>
                            @endif

                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('chats.show', $chat) }}"
                                   class="flex-1 bg-purple-600 text-white text-center px-4 py-2 rounded hover:bg-purple-700 transition duration-150">
                                    Open Chat
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-8 text-center border border-purple-100">
                <div class="text-purple-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold text-purple-800 mb-4">No chats yet</h2>
                <p class="text-purple-600 mb-6">Start your first conversation with someone!</p>
                <a href="{{ route('users.index') }}"
                   class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition duration-150 font-semibold">
                    Find Users to Chat With
                </a>
            </div>
        @endif
    </div>
</x-app-layout>

<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Pusher === 'undefined') {
            console.error('Pusher not loaded');
            return;
        }

        // Initialize Pusher/Reverb
        window.pusherInstance = new Pusher('{{ env("REVERB_APP_KEY") }}', {
            wsHost: '{{ env("REVERB_HOST") }}',
            wsPort: {{ env("REVERB_PORT") }},
            wssPort: {{ env("REVERB_PORT") }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
            cluster: '',
            authEndpoint: '/broadcasting/auth',
        });

        const userChatIds = @json($chats->pluck('id'));
        console.log('Subscribing to chats:', userChatIds);

        userChatIds.forEach(chatId => {
            const channelName = 'chat.' + chatId;
            const channel = window.pusherInstance.subscribe(channelName);

            channel.bind('message.sent', (data) => {
                console.log('💬 New message in chat', chatId, data);
                const chatItem = document.querySelector(`#chat-item-${chatId}`);
                if (chatItem) {
                    const unreadCountEl = document.querySelector(`#unread-count-${chatId}`);
                    if (unreadCountEl) {
                        const count = parseInt(unreadCountEl.textContent) || 0;
                        unreadCountEl.textContent = count + 1 + ' Unread Message(s)';
                    }
                    chatItem.classList.add('bg-yellow-100');
                }
            });
        });
    });
</script>

