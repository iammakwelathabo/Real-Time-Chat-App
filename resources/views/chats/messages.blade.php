@foreach($messages->reverse() as $message)
    <div class="flex {{ $message->user_id == auth()->id() ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
        <div class="{{ $message->user_id == auth()->id() ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-800' }} rounded-lg p-3 max-w-xs">
            <p class="break-words">{{ $message->body }}</p>
            <div class="text-xs mt-1 opacity-75">
                <span>{{ $message->created_at->format('h:i A') }}</span> •
                <span>{{ $message->read_at ? 'Read' : 'Delivered' }}</span>
            </div>
        </div>
    </div>
@endforeach
