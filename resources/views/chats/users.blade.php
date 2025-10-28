<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-purple-800 leading-tight">
            {{ __('Find Users') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-6">
        <div class="bg-white rounded-lg shadow-md">
            <div class="bg-gradient-to-r from-purple-500 to-purple-700 text-white p-6 rounded-t-lg">
                <h1 class="text-2xl font-bold">Start a New Chat</h1>
                <p class="text-purple-100">Choose a user to start chatting with</p>
                <p class="text-purple-200 text-sm mt-2">
                    {{ $users->count() }} users found
                </p>
            </div>

            <div class="p-6">
                @if($users->count() > 0)
                    <div class="grid gap-4">
                        @foreach($users as $user)
                            <div class="flex items-center justify-between p-4 border border-purple-100 rounded-lg hover:bg-purple-50 transition duration-150">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-purple-900">{{ $user->name }}</h3>
                                        <p class="text-purple-600 text-sm">{{ $user->email }}</p>
                                        <p class="text-purple-400 text-xs mt-1">
                                            Joined {{ $user->created_at->format('M j, Y') }}
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('chats.create.private', $user) }}"
                                   class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-150 font-semibold">
                                    Start Chat
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-purple-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-purple-800 mb-2">No Other Users Found</h3>
                        <p class="text-purple-600 mb-4">There are no other users registered in the system yet.</p>
                        <p class="text-purple-500 text-sm">
                            Ask someone to register so you can start chatting!
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('chats.index') }}" class="text-purple-600 hover:text-purple-800 font-semibold">
                ← Back to My Chats
            </a>
        </div>
    </div>
</x-app-layout>
