@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-purple-600 bg-purple-50 p-3 rounded-lg border border-purple-200']) }}>
        {{ $status }}
    </div>
@endif
