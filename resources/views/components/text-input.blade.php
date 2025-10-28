@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-purple-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm']) !!}>
