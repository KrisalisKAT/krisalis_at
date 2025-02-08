@props(['name', 'size' => 'size-4', 'display' => 'inline'])
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
    {{ $attributes->merge(['class' => "{$size} {$display}"]) }}>
    <use href="#icon-{{ $name }}" />
</svg>
