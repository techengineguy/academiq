<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="{{ asset('images/logo.png') }}" sizes="any">
<link rel="icon" href="{{ asset('images/logo.png') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<tallstackui:script /> 
@livewireStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
