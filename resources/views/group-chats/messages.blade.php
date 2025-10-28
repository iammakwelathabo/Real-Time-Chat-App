@foreach($messages as $message)
    <div class="flex {{ $message->user_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
        <div class="{{ $message->user_id == auth()->id() ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-800' }} rounded-lg p-3 max-w-xs">
            @if($message->user_id != auth()->id())
                <div class="mb-1">
                    <span class="text-xs font-semibold text-purple-600">{{ $message->user->name }}</span>
                </div>
            @endif
            <p class="break-words">{{ $message->body }}</p>
            <div class="text-xs mt-1 opacity-75">
                <span>{{ $message->created_at->format('h:i A') }}</span>
            </div>
        </div>
    </div>
@endforeach
