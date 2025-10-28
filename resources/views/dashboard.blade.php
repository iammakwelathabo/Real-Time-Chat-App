<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-purple-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-gradient-to-r from-purple-50 to-white border-b border-purple-100">
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Welcome Card -->
                        <div class="bg-white rounded-lg shadow-md p-6 border border-purple-200">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-700 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-purple-800">Welcome back, {{ Auth::user()->name }}!</h3>
                                    <p class="text-purple-600">Ready to start chatting?</p>
                                </div>
                            </div>
                            <a href="{{ route('chats.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-800 transition ease-in-out duration-150">
                                Start Chatting
                            </a>
                        </div>

                        <!-- Quick Stats -->
                        <div class="bg-white rounded-lg shadow-md p-6 border border-purple-200">
                            <h3 class="text-lg font-semibold text-purple-800 mb-4">Your Chat Stats</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-purple-600">Active Chats</span>
                                    <span class="font-semibold text-purple-800">{{ Auth::user()->chats()->count() }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-purple-600">Total Messages</span>
                                    <span class="font-semibold text-purple-800">{{ Auth::user()->messages()->count() }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-purple-600">Online Friends</span>
                                    <span class="font-semibold text-purple-800">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('chats.index') }}" class="bg-purple-100 hover:bg-purple-200 p-4 rounded-lg text-center transition duration-150 ease-in-out">
                            <div class="text-purple-600 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-purple-800">My Chats</h4>
                            <p class="text-sm text-purple-600 mt-1">Continue your conversations</p>
                        </a>

                        <a href="{{ route('users.index') }}" class="bg-purple-100 hover:bg-purple-200 p-4 rounded-lg text-center transition duration-150 ease-in-out">
                            <div class="text-purple-600 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-purple-800">New Chat</h4>
                            <p class="text-sm text-purple-600 mt-1">Start a new conversation</p>
                        </a>

                        <a href="{{ route('group-chats.create') }}" class="bg-purple-100 hover:bg-purple-200 p-4 rounded-lg text-center transition duration-150 ease-in-out">
                            <div class="text-purple-600 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-purple-800">Group Chat</h4>
                            <p class="text-sm text-purple-600 mt-1">Create a group conversation</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
