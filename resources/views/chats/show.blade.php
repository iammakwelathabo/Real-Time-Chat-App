<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-purple-800 leading-tight">
            Chat with
            @if($chat->is_group)
                {{ $chat->name ?? 'Group Chat' }}
            @else
                {{ $chat->otherUser()->name ?? 'Unknown User' }}
            @endif
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-6">
        <div class="bg-white rounded-lg shadow-lg">
            <!-- Chat Header -->
            <div class="bg-gradient-to-r from-purple-500 to-purple-700 text-white p-4 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold">
                            @if($chat->is_group)
                                {{ $chat->name ?? 'Group Chat' }}
                            @else
                                {{ $chat->otherUser()->name ?? 'Unknown User' }}
                            @endif
                        </h2>
                        <div class="text-sm opacity-90">
                            <span id="onlineStatus">
                                {{ $chat->otherUser()->is_online ? 'Online' : 'Offline' }}
                            </span>
                            • <span id="typingIndicator"></span>
                        </div>
                    </div>
                    <a href="{{ route('chats.index') }}" class="text-white hover:underline bg-purple-800 px-3 py-1 rounded">
                        ← Back to Chats
                    </a>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="messagesContainer" class="h-96 overflow-y-auto p-4 space-y-4 bg-gray-50">
                @foreach($messages->reverse() as $message) <!-- oldest first -->
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
                            placeholder="Type your message..."
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

    <!-- Add Pusher script -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const messagesContainer = document.getElementById('messagesContainer');
            // Scroll to bottom initially
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            let currentPage = 1;
            let loadingOlder = false;
            let lastPage = {{ $messages->lastPage() }};

            messagesContainer.addEventListener('scroll', async () => {
                if (messagesContainer.scrollTop > 50 || loadingOlder) return;
                if (currentPage >= lastPage) return;

                loadingOlder = true;
                currentPage++;

                try {
                    const response = await fetch(`/chats/{{ $chat->id }}?page=${currentPage}`);
                    const html = await response.text();
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;

                    const newMessages = tempDiv.querySelectorAll('#messagesContainer > div');
                    newMessages.forEach(msg => messagesContainer.prepend(msg));

                    // Maintain scroll position
                    if (newMessages.length) {
                        newMessages[0].scrollIntoView();
                    }

                } catch (err) {
                    console.error(err);
                } finally {
                    loadingOlder = false;
                }
            });

            const messageForm = document.getElementById('messageForm');
            const messageInput = document.getElementById('messageInput');
            const typingIndicator = document.getElementById('typingIndicator');

            // Initialize Pusher connection
            initializeWebSocket();

            // Handle message submission
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const message = messageInput.value.trim();
                if (!message) return;

                // Disable form while sending
                const submitButton = messageForm.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.textContent = 'Sending...';

                fetch(`/chats/{{ $chat->id }}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ body: message })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        messageInput.value = '';
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                        alert('Error sending message. Please try again.');
                    })
                    .finally(() => {
                        // Re-enable form
                        submitButton.disabled = false;
                        submitButton.textContent = 'Send';
                    });
            });

            // WebSocket initialization function
            function initializeWebSocket() {

                if (typeof Pusher === 'undefined') {
                    console.error('Pusher not loaded');
                    return;
                }

                console.log('🔄 Initializing Pusher connection...');
                console.log('Reverb Config:', {
                    key: '{{ env('REVERB_APP_KEY') }}',
                    host: '{{ env('REVERB_HOST') }}',
                    port: {{ env('REVERB_PORT') }}
                });

                // Initialize Pusher with Reverb configuration
                const pusher = new Pusher('{{ env('REVERB_APP_KEY') }}', {
                    wsHost: '{{ env('REVERB_HOST') }}',
                    wsPort: {{ env('REVERB_PORT') }},
                    wssPort: {{ env('REVERB_PORT') }},
                    forceTLS: false,
                    enabledTransports: ['ws', 'wss'],
                    cluster: '',
                    authEndpoint: '/broadcasting/auth',
                });

                // Store pusher instance globally for debugging
                window.pusherInstance = pusher;

                // Connection event listeners
                pusher.connection.bind('connected', () => {

                    // Subscribe to the chat channel - use public channel
                    const channelName = 'chat.{{ $chat->id }}';

                    const channel = pusher.subscribe(channelName);

                    channel.bind('pusher:subscription_succeeded', () => {

                        // Listen for MessageSent event
                        channel.bind('message.sent', (data) => {
                            console.log('💬 REAL-TIME MESSAGE RECEIVED:', data);
                            addMessageToChat(data.message);
                        });

                        channel.bind('message.read', (data) => {
                            const messageDiv = document.querySelector(`[data-message-id="${data.message.id}"]`);
                            if (messageDiv) {
                                const statusSpan = messageDiv.querySelector('.text-xs span:last-child');
                                if (statusSpan) {
                                    statusSpan.textContent = 'Read';
                                }
                            }
                        });
                        // Also listen for the default event name (without broadcastAs)
                        channel.bind('MessageSent', (data) => {
                            addMessageToChat(data.message);
                        });

                        channel.bind('pusher:member_added', (data) => {
                        });

                        channel.bind('pusher:member_removed', (data) => {
                        });
                    });

                    channel.bind('pusher:subscription_error', (error) => {
                        console.error('❌ Subscription error:', error);
                    });

                    window.Echo.join('presence-online')
                        .here((users) => {
                            updateOnlineStatus(users);
                        })
                        .joining((user) => {
                            updateSingleUserStatus(user, true);
                        })
                        .leaving((user) => {
                            updateSingleUserStatus(user, false);
                        });

                    const currentUserId = {{ auth()->id() }};
                    const typingIndicator = document.getElementById('typingIndicator');

                    window.Echo.channel('chat.{{ $chat->id }}')
                        .listen('.user.typing', (data) => {
                            // Ignore events sent by myself
                            if (data.user.id === currentUserId) return;

                            if (data.typing) {
                                typingIndicator.textContent = `typing...`;
                                typingIndicator.style.display = 'block';
                            } else {
                                typingIndicator.textContent = '';
                                typingIndicator.style.display = 'none';
                            }
                        });

                    function updateOnlineStatus(users) {
                        const otherUserId = {{ $chat->otherUser()->id }};
                        const onlineStatusEl = document.getElementById('onlineStatus');
                        const isOnline = users.some(u => u.id === otherUserId);
                        onlineStatusEl.textContent = isOnline ? 'Online' : 'Offline';
                    }

                    function updateSingleUserStatus(user, online) {
                        const otherUserId = {{ $chat->otherUser()->id }};
                        if (user.id === otherUserId) {
                            const onlineStatusEl = document.getElementById('onlineStatus');
                            onlineStatusEl.textContent = online ? 'Online' : 'Offline';
                        }
                    }
                });

                pusher.connection.bind('connecting', () => {
                });

                pusher.connection.bind('disconnected', () => {
                });

                pusher.connection.bind('error', (error) => {
                });

                // Also bind to all events for debugging
                pusher.connection.bind('state_change', (states) => {
                });
            }

            function addMessageToChat(message) {
                // Check if message already exists to prevent duplicates
                const existingMessage = document.querySelector(`[data-message-id="${message.id}"]`);
                if (existingMessage) {
                    console.log('Message already exists, skipping...');
                    return;
                }

                const messageDiv = document.createElement('div');
                const isCurrentUser = message.user_id == {{ auth()->id() }};
                messageDiv.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'}`;
                messageDiv.setAttribute('data-message-id', message.id);

                messageDiv.innerHTML = `
                <div class="${isCurrentUser ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-800'} rounded-lg p-3 max-w-xs">
                    <p class="break-words">${message.body}</p>
                    <div class="text-xs mt-1 opacity-75">
                        <span>${new Date(message.created_at).toLocaleTimeString()}</span>
                        •
                        <span>${message.read_at ? 'Read' : 'Delivered'}</span>
                    </div>
                </div>
                `;

                messagesContainer.appendChild(messageDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                // Mark message as read if it's not from current user
                if (!isCurrentUser) {
                    markMessageAsRead(message.id);
                }
            }

            // Mark message as read
            function markMessageAsRead(messageId) {
                fetch(`/messages/${messageId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                })
                .catch(error => {
                    console.error('Error marking message as read:', error);
                });
            }

            // Handle user typing
            function handleUserTyping(data) {
                if (data.userId !== {{ auth()->id() }}) {
                    const userName = data.userName || 'Someone';
                    typingIndicator.textContent = `${userName} is typing...`;
                }
            }

            // Handle user stopped typing
            function handleUserStoppedTyping(data) {
                if (data.userId !== {{ auth()->id() }}) {
                    typingIndicator.textContent = '';
                }
            }

            let typingTimer;
            let isTyping = false;

            messageInput.addEventListener('input', () => {
                const hasContent = messageInput.value.trim().length > 0;

                if (!isTyping && hasContent) {
                    isTyping = true;
                    sendTyping(true);
                } else if (isTyping && !hasContent) {
                    isTyping = false;
                    clearTimeout(typingTimer);
                    sendTyping(false);
                }

                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    if (isTyping) {
                        isTyping = false;
                        sendTyping(false);
                    }
                }, 1500);
            });

            messageInput.addEventListener('blur', () => {
                if (isTyping) {
                    isTyping = false;
                    clearTimeout(typingTimer);
                    sendTyping(false);
                }
            });

            function sendTyping(typing) {
                fetch(`/chats/{{ $chat->id }}/typing`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ typing })
                });
            }

            messageInput.addEventListener('blur', function() {
                if (isTyping) {
                    isTyping = false;
                    clearTimeout(typingTimer);
                    broadcastTyping(false);
                }
            });

            // In your typing detection code
            function broadcastTyping(typing) {
                console.log('📤 SENDING typing event:', typing ? 'START' : 'STOP');

                fetch(`/chats/{{ $chat->id }}/typing`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ typing: typing })
                })
                    .then(response => response.json())
                    .then(data => {
                    })
                    .catch(error => {
                        console.error('❌ Error sending typing event:', error);
                    });
            }

            // Debug function to check connection status
            window.checkConnection = function() {
                if (window.pusherInstance) {
                    const state = window.pusherInstance.connection.state;
                    console.log('Connection state:', state);
                    return state;
                }
                return 'No Pusher instance';
            };
        });
    </script>

</x-app-layout>
