<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }} bei Naturmarkt.">
    <title>Naturmarkt | {{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>
<header class="site-header">
    <div class="announcement">Naturmarkt | {{ $title }}</div>
    <nav class="nav container" aria-label="Hauptnavigation">
        <a class="brand" href="{{ route('home') }}" aria-label="Naturmarkt Startseite"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <div class="nav-links">
            <a href="{{ route('home') }}#kategorien">Kategorien</a>
            <a href="{{ route('contact') }}">Kontakt</a>
        </div>
        <a class="cart-button" href="{{ route('cart') }}" aria-label="Mein Warenkorb">
            <span>Warenkorb</span>
            <strong id="cart-count">0</strong>
        </a>
    </nav>
</header>
<main class="info-page">
    <section class="container info-panel">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <span>{{ $title }}</span>
        </nav>
        <span class="eyebrow">Naturmarkt</span>
        <h1>{{ $title }}</h1>
                <p>{!! nl2br(e($text)) !!}</p>
        @if ($title === 'Kontakt')
            <div class="info-contact-box">
                <strong>Direkter Kontakt</strong>
                @php($legal = config('naturmarkt.legal'))
                <span>E-Mail: <a href="mailto:{{ $legal['email'] }}">{{ $legal['email'] }}</a></span>
                <span>Telefon: {{ $legal['phone'] }}</span>
                <span>Post: {{ $legal['owner'] }}, {{ $legal['street'] }}, {{ $legal['city'] }}</span>
            </div>
        @endif
        <a class="button primary" href="{{ route('home') }}">Zur Startseite</a>
    </section>
</main>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>
