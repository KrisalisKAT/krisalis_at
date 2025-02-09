@props(['title', 'skipSiteName' => false, 'remSize' => null])
<!doctype html>
<html @if($remSize) style="font-size: {{ $remSize }}" @endif>
<head>
    <title>{{ $title ?? '' }}{{ $title && !$skipSiteName ? ' | ' : '' }}{{ !$skipSiteName ? 'Krisalis.@' : '' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
{{ $slot }}

<div class="absolute top-1 right-1">
    <label class="grid cursor-pointer place-items-center">
        <input
            x-data="{
                _checked: true,
                get checked() {
                    return this._checked;
                },
                set checked(value) {
                    this._checked = value;
                    document.documentElement.dataset.theme = value ? 'coffee' : 'garden';
                    window.localStorage.setItem('dark', JSON.stringify(value));
                },
                init() {
                    this.checked = JSON.parse(window.localStorage.getItem('dark')) ?? window.matchMedia?.('(prefers-color-scheme:dark)')?.matches ?? true;
                }
            }"
            x-model="checked"
            type="checkbox"
            class="toggle theme-controller bg-base-content col-span-2 col-start-1 row-start-1"/>
        <svg
            class="stroke-base-100 fill-base-100 col-start-1 row-start-1"
            xmlns="http://www.w3.org/2000/svg"
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <circle cx="12" cy="12" r="5"/>
            <path
                d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/>
        </svg>
        <svg
            class="stroke-base-100 fill-base-100 col-start-2 row-start-1"
            xmlns="http://www.w3.org/2000/svg"
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </label>
</div>
<svg xmlns="http://www.w3.org/2000/svg">
    @stack('icon')
</svg>
@stack('scripts')
</body>
</html>
