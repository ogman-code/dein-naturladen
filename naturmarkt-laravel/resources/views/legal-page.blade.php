<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }} von Naturmarkt.">
    <title>Naturmarkt | {{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>
<header class="site-header">
    <div class="announcement">Naturmarkt | Rechtliche Informationen</div>
    <nav class="nav container" aria-label="Hauptnavigation">
        <a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <div class="nav-links"><a href="{{ route('home') }}#kategorien">Kategorien</a><a href="{{ route('contact') }}">Kontakt</a></div>
        <a class="cart-button" href="{{ route('cart') }}"><span>Warenkorb</span><strong id="cart-count">0</strong></a>
    </nav>
</header>
<main class="legal-page">
    <div class="container legal-layout">
        <aside class="legal-nav">
            <strong>Rechtliches</strong>
            <a href="{{ route('imprint') }}">Impressum</a>
            <a href="{{ route('privacy') }}">Datenschutz</a>
            <a href="{{ route('terms') }}">AGB</a>
            <a href="{{ route('returns') }}">Widerrufsbelehrung</a>
            <a class="withdraw-link" href="{{ route('withdrawal.form') }}">Vertrag widerrufen</a>
        </aside>
        <article class="legal-document">
            <nav class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>/</span><span>{{ $title }}</span></nav>
            <span class="eyebrow">Stand: {{ now()->format('d.m.Y') }}</span>
            <h1>{{ $title }}</h1>
            <p class="legal-intro">Hier findest du die rechtlichen Informationen zu unserem Shop übersichtlich zusammengefasst.</p>
            @if($notice)
                <div class="legal-notice"><strong>Hinweis</strong><span>{{ $notice }}</span></div>
            @endif
            @if($title === 'Widerrufsbelehrung')
                <p><a class="button primary withdraw-link" href="{{ route('withdrawal.form') }}">Vertrag widerrufen</a></p>
            @endif
            @foreach($sections as $section)
                <section><h2>{{ $loop->iteration }}. {{ $section['title'] }}</h2><p>{!! nl2br(e($section['text'])) !!}</p></section>
            @endforeach
        </article>
    </div>
</main>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>
