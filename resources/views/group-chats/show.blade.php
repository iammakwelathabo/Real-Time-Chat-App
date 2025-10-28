<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-purple-800 leading-tight">
            {{ $chat->name }}
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Chat Messages Section -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-lg">
                    <!-- Group Chat Header -->
                    <div class="bg-gradient-to-r from-purple-500 to-purple-700 text-white p-4 rounded-t-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-xl font-semibold">{{ $chat->name }}</h2>
                                <div class="text-sm opacity-90">
                                    <span>{{ $chat->users->count() }} members</span>
                                    •
                                    <!--<span id="onlineStatus">{{ $chat->users->where('is_online', true)->count() }} online</span>-->
                                    •
                                    <span id="typingIndicator" style="display: none; font-style: italic;"></span>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('chats.index') }}" class="text-white hover:underline bg-purple-800 px-3 py-1 rounded">
                                    ← Back
                                </a>
                                @if($chat->isAdmin())
                                    <a href="#group-settings" class="text-white hover:underline bg-purple-800 px-3 py-1 rounded">
                                        Settings
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="messagesContainer" class="h-96 overflow-y-auto p-4 space-y-4 bg-gray-50">
                        @foreach($messages->reverse() as $message)
                            <div class="flex {{ $message->user_id == auth()->id() ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
                                <div class="{{ $message->user_id == auth()->id() ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-800' }} rounded-lg p-3 max-w-xs">
                                    <p class="break-words">{{ $message->body }}</p>
                                    <div class="text-xs mt-1 opacity-75">
                                        <span>{{ $message->created_at->format('h:i A') }}</span> •
                                        <span>{{ $message->readBy->contains(auth()->id()) ? 'Read' : 'Delivered' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Message Input Form -->
                    <div class="p-4 border-t">
                        <form id="messageForm">
                            @csrf
                            <div class="flex space-x-2">
                                <input
                                    type="text"
                                    id="messageInput"
                                    placeholder="Type your message to {{ $chat->name }}..."
                                    class="flex-1 border border-purple-300 rounded-lg px-4 py-2 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                                    required
                                >
                                <button
                                    type="submit"
                                    class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition duration-150 font-semibold"
                                >
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Group Info Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md border border-purple-100">
                    <div class="bg-purple-100 p-4 rounded-t-lg">
                        <h3 class="font-semibold text-purple-800">Group Information</h3>
                    </div>

                    <div class="p-4 space-y-4">
                        <!-- Group Details -->
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $chat->name }}</h4>
                            @if($chat->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $chat->description }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-2">
                                Created by {{ $chat->creator->name }}<br>
                                {{ $chat->created_at->format('M j, Y') }}
                            </p>
                        </div>

                        <!-- Members List -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Members ({{ $chat->users->count() }})</h4>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach($chat->users as $user)
                                    <div class="flex items-center justify-between group">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-8 h-8 bg-gradient-to-r from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="text-sm {{ $user->is_online ? 'text-green-600 font-semibold' : 'text-gray-700' }}">
                                                    {{ $user->name }}
                                                </span>
                                                @if($chat->admins->contains($user->id))
                                                    <span class="text-xs text-purple-600 ml-1">Admin</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($chat->isAdmin() && $user->id != auth()->id())
                                            <form action="{{ route('group-chats.remove-member', [$chat, $user]) }}" method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs" onclick="return confirm('Remove {{ $user->name }} from group?')">
                                                    Remove
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Group Actions -->
                        <div class="pt-4 border-t border-gray-200 space-y-2">
                            @if($chat->isAdmin())
                                <!-- Add Members Form -->
                                <details class="group">
                                    <summary class="cursor-pointer text-sm text-purple-600 hover:text-purple-800 font-medium">
                                        + Add Members
                                    </summary>
                                    <div class="mt-2 p-3 bg-gray-50 rounded">
                                        <form action="{{ route('group-chats.add-members', $chat) }}" method="POST">
                                            @csrf
                                            <div class="space-y-2">
                                                @foreach($nonMembers as $user)
                                                    <label class="flex items-center space-x-2">
                                                        <input type="checkbox" name="members[]" value="{{ $user->id }}" class="rounded border-purple-300 text-purple-600">
                                                        <span class="text-sm">{{ $user->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <button type="submit" class="w-full mt-2 bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">
                                                Add Selected
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            @endif

                            <!-- Leave Group -->
                            <form action="{{ route('group-chats.leave', $chat) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-red-600 hover:text-red-800 text-sm font-medium" onclick="return confirm('Are you sure you want to leave this group?')">
                                    Leave Group
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.getElementById('messagesContainer');
            const messageForm = document.getElementById('messageForm');
            const messageInput = document.getElementById('messageInput');
            const typingIndicator = document.getElementById('typingIndicator');

            // Scroll to bottom of messages
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // State management
            let typingTimer;
            let isTyping = false;
            const typingTimers = {}; // Store timers for each user typing

            // Initialize WebSocket
            initializeGroupWebSocket();

            // Handle message submission
            messageForm.addEventListener('submit', handleMessageSubmit);

            // Typing detection
            messageInput.addEventListener('input', handleTypingInput);
            messageInput.addEventListener('blur', stopTyping);

            async function handleMessageSubmit(e) {
                e.preventDefault();

                const message = messageInput.value.trim();
                if (!message) return;

                // Stop typing when sending
                stopTyping();

                // Disable form while sending
                const submitButton = messageForm.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Sending...';

                try {
                    const response = await fetch(`/group-chats/{{ $chat->id }}/messages`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ body: message })
                    });

                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                    const data = await response.json();
                    messageInput.value = '';

                } catch (error) {
                    console.error('Error sending group message:', error);
                    alert('Error sending message. Please try again.');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            }

            function handleTypingInput() {
                if (!isTyping) startTyping();

                clearTimeout(typingTimer);
                typingTimer = setTimeout(stopTyping, 1000);
            }

            function startTyping() {
                isTyping = true;
                broadcastGroupTyping(true);
            }

            function stopTyping() {
                if (!isTyping) return;
                isTyping = false;
                clearTimeout(typingTimer);
                broadcastGroupTyping(false);
            }

            function broadcastGroupTyping(typing) {
                fetch(`/group-chats/{{ $chat->id }}/typing`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ typing: typing })
                }).catch(error => {
                    console.error('Group typing broadcast error:', error);
                });
            }

            function initializeGroupWebSocket() {
                if (typeof Pusher === 'undefined') {
                    console.error('Pusher not loaded');
                    return;
                }

                const pusher = new Pusher('{{ env('REVERB_APP_KEY') }}', {
                    wsHost: '{{ env('REVERB_HOST') }}',
                    wsPort: {{ env('REVERB_PORT') }},
                    wssPort: {{ env('REVERB_PORT') }},
                    forceTLS: false,
                    enabledTransports: ['ws', 'wss'],
                    cluster: '',
                    authEndpoint: '/broadcasting/auth',
                });

                window.pusherInstance = pusher;

                pusher.connection.bind('connected', () => {

                    const channelName = 'group-chat.{{ $chat->id }}';
                    const channel = pusher.subscribe(channelName);

                    channel.bind('pusher:subscription_succeeded', () => {
                        // Listen for events
                        channel.bind('group.message.sent', (data) => {
                            addMessageToGroupChat(data.message);
                        });

                        channel.bind('group.user.typing', (data) => {
                            handleGroupUserTyping(data);
                        });

                        channel.bind('message.sent', (data) => {
                            addMessageToGroupChat(data.message);
                        });
                    });

                    channel.bind('pusher:subscription_error', (error) => {
                        console.error('❌ GROUP subscription error:', error);
                    });
                });

                pusher.connection.bind('error', (error) => {
                    console.error('❌ GROUP WebSocket error:', error);
                });
            }

            function handleGroupUserTyping(data) {
                if (data.user.id === {{ auth()->id() }}) return;

                const userId = data.user.id;

                if (data.typing) {
                    typingIndicator.textContent = `typing...`;
                    typingIndicator.style.display = 'block';

                    if (typingTimers[userId]) clearTimeout(typingTimers[userId]);

                    typingTimers[userId] = setTimeout(() => {
                        delete typingTimers[userId];
                        updateTypingIndicator();
                    }, 3000);
                } else {
                    if (typingTimers[userId]) {
                        clearTimeout(typingTimers[userId]);
                        delete typingTimers[userId];
                    }
                    updateTypingIndicator();
                }
            }

            function updateTypingIndicator() {
                if (Object.keys(typingTimers).length === 0) {
                    typingIndicator.textContent = '';
                    typingIndicator.style.display = 'none';
                }
            }

            function addMessageToGroupChat(message) {
                const existingMessage = document.querySelector(`[data-message-id="${message.id}"]`);
                if (existingMessage) return;

                const isCurrentUser = message.user_id == {{ auth()->id() }};
                const messageDiv = document.createElement('div');
                messageDiv.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'}`;
                messageDiv.setAttribute('data-message-id', message.id);

                messageDiv.innerHTML = `
                <div class="${isCurrentUser ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-800'} rounded-lg p-3 max-w-xs">
                    ${!isCurrentUser ? `
                        <div class="mb-1">
                            <span class="text-xs font-semibold text-purple-600">${message.user.name}</span>
                        </div>
                    ` : ''}
                    <p class="break-words">${message.body}</p>
                    <div class="text-xs mt-1 opacity-75">
                        <span>${new Date(message.created_at).toLocaleTimeString()}</span>
                    </div>
                </div>
                `;

                messagesContainer.appendChild(messageDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;

                updateTypingIndicator();
            }

            // Debug utilities
            window.checkGroupConnection = function() {
                if (window.pusherInstance) {
                    return window.pusherInstance.connection.state;
                }
                return 'No Pusher instance';
            };

            window.testGroupTyping = function() {
                startTyping();
                setTimeout(stopTyping, 2000);
            };

            let currentPage = 1;
            let loadingOlder = false;
            let hasMorePages = true;
            const chatId = {{ $chat->id }};
            const userId = {{ auth()->id() }};

            async function loadOlderMessages(chatId) {
                if (loadingOlder || !hasMorePages) return;
                loadingOlder = true;

                try {
                    const response = await fetch(`/group-chats/${chatId}/messages?page=${currentPage + 1}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'include'
                    });

                    // Ensure server returns JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await response.text();
                        console.error('Expected JSON but got:', text.substring(0, 200));
                        throw new Error('Server returned HTML instead of JSON. Check your API route.');
                    }

                    if (!response.ok) {
                        console.error('HTTP Error:', response.status);
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        const messages = data.messages;
                        const container = document.getElementById('messagesContainer');
                        const scrollHeightBefore = container.scrollHeight;

                        messages.reverse().forEach(msg => {
                            // Skip duplicates
                            if (document.querySelector(`[data-message-id="${msg.id}"]`)) return;

                            const div = document.createElement('div');
                            div.className = `flex ${msg.user_id === userId ? 'justify-end' : 'justify-start'}`;
                            div.setAttribute('data-message-id', msg.id);
                            div.innerHTML = `
                            <div class="${msg.user_id === userId ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-800'} rounded-lg p-3 max-w-xs">
                                ${msg.user_id !== userId ? `<div class="mb-1"><span class="text-xs font-semibold text-purple-600">${msg.user.name}</span></div>` : ''}
                                <p class="break-words">${msg.body}</p>
                                <div class="text-xs mt-1 opacity-75">
                                    <span>${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                                </div>
                            </div>
                        `;
                            container.insertBefore(div, container.firstChild);
                        });

                        // Restore scroll position
                        const scrollHeightAfter = container.scrollHeight;
                        container.scrollTop = scrollHeightAfter - scrollHeightBefore;

                        hasMorePages = data.hasMore;
                        currentPage = data.nextPage;
                    } else {
                        console.warn('Server returned error:', data.error);
                        hasMorePages = false;
                    }
                } catch (error) {
                    console.error('Error loading older messages:', error);
                    hasMorePages = false;
                } finally {
                    loadingOlder = false;
                }
            }

            messagesContainer.addEventListener('scroll', () => {
                if (messagesContainer.scrollTop < 50 && hasMorePages) {
                    loadOlderMessages(chatId);
                }
            });
        });
    </script>
</x-app-layout>
