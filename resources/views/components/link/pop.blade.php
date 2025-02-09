<a {{ $attributes->merge(['class' => 'kat-link', 'target' => '_blank']) }}>
    <span>{{ $slot }}</span> <x-icon.newTab class="mb-1 -mr-0.5" />
</a>
