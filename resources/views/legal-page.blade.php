<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }} von Naturmarkt.">
    <title>Naturmarkt | {{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            <a href="{{ route('returns') }}">Widerruf</a>
        </aside>
        <article class="legal-document">
            <nav class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>/</span><span>{{ $title }}</span></nav>
            <span class="eyebrow">Stand: {{ now()->format('d.m.Y') }}</span>
            <h1>{{ $title }}</h1>
            <p class="legal-intro">Hier findest du die rechtlichen Informationen zu unserem Shop übersichtlich zusammengefasst.</p>
            <div class="legal-notice"><strong>Hinweis</strong><span>{{ $notice }}</span></div>
            @foreach($sections as $section)
                <section><h2>{{ $loop->iteration }}. {{ $section['title'] }}</h2><p>{!! nl2br(e($section['text'])) !!}</p></section>
            @endforeach
        </article>
    </div>
</main>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>
