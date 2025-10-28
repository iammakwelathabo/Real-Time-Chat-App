<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-purple-800 leading-tight">
            {{ __('Create Group Chat') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto py-6">
        <div class="bg-white rounded-lg shadow-md border border-purple-100">
            <div class="bg-gradient-to-r from-purple-500 to-purple-700 text-white p-6 rounded-t-lg">
                <h1 class="text-2xl font-bold">Create New Group Chat</h1>
                <p class="text-purple-100">Start a conversation with multiple people</p>
            </div>

            <form method="POST" action="{{ route('group-chats.store') }}" class="p-6">
                @csrf

                <!-- Group Name -->
                <div class="mb-6">
                    <x-input-label for="name" value="Group Name" class="text-purple-700" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        class="mt-1 block w-full border-purple-300 focus:border-purple-500"
                        placeholder="Enter group name"
                        required
                        autofocus
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Group Description -->
                <div class="mb-6">
                    <x-input-label for="description" value="Description (Optional)" class="text-purple-700" />
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        class="mt-1 block w-full border border-purple-300 rounded-md shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                        placeholder="What's this group about?"
                    ></textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <!-- Members Selection -->
                <div class="mb-6">
                    <x-input-label value="Add Members" class="text-purple-700 mb-3" />

                    @if($users->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-3 border border-purple-200 rounded-lg">
                            @foreach($users as $user)
                                <label class="flex items-center space-x-3 p-2 hover:bg-purple-50 rounded cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="members[]"
                                        value="{{ $user->id }}"
                                        class="rounded border-purple-300 text-purple-600 focus:ring-purple-500"
                                    >
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 bg-gradient-to-r from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Select at least one member to create the group</p>
                    @else
                        <div class="text-center py-4 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p class="mt-2">No other users found to add to the group.</p>
                        </div>
                    @endif
                    <x-input-error :messages="$errors->get('members')" class="mt-2" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('chats.index') }}" class="text-purple-600 hover:text-purple-900 font-medium">
                        ← Back to Chats
                    </a>
                    <div class="flex space-x-3">
                        <a href="{{ route('chats.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-150">
                            Cancel
                        </a>
                        <x-primary-button class="bg-purple-600 hover:bg-purple-700">
                            Create Group
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
