<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Produkte im Naturmarkt durchsuchen.">
    <meta name="robots" content="noindex,follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naturmarkt | Suche</title>
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>
<header class="site-header">
    <div class="announcement">Naturmarkt Produktsuche</div>
    <nav class="nav container">
        <a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <form class="header-search" action="{{ route('search') }}" method="get">
            <input name="q" type="search" value="{{ $query }}" placeholder="Produkte suchen" autofocus>
            <button type="submit">Suchen</button>
        </form>
        <a class="cart-button" href="{{ route('cart') }}"><span>Warenkorb</span><strong id="cart-count">0</strong></a>
    </nav>
</header>
<main class="section">
    <div class="container">
        <div class="section-heading compact">
            <div><span class="eyebrow">Suche</span><h1>{{ count($products) }} Treffer für „{{ $query }}“</h1></div>
        </div>
        <div class="product-grid">
            @forelse ($products as $product)
                <article class="product-card">
                    <a class="product-media" href="{{ $product['url'] }}"><img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" decoding="async"></a>
                    <div class="product-info">
                        <span class="product-category">{{ $product['category'] }}</span>
                        <h3><a href="{{ $product['url'] }}">{{ $product['name'] }}</a></h3>
                        <div class="product-bottom">
                            <strong>{{ $product['price'] }}</strong>
                            <button class="add-to-cart" type="button" data-name="{{ $product['name'] }}" data-price="{{ $product['price'] }}" data-weight="{{ $product['weight_grams'] }}" data-category="{{ $product['category'] }}" data-image="{{ $product['image'] }}" data-url="{{ $product['url'] }}" @disabled(!$product['active'] || $product['stock'] === 0)>{{ (!$product['active'] || $product['stock'] === 0) ? 'Nicht verfügbar' : 'In den Warenkorb' }}</button>
                        </div>
                        <button class="description-trigger" type="button" data-description-name="{{ $product['name'] }}" data-description-text="{{ $product['description'] }}" data-description-ingredients="{{ $product['ingredients'] }}" data-description-incomplete="{{ $product['description_incomplete'] ? '1' : '0' }}">Beschreibung <span aria-hidden="true">→</span></button>
                    </div>
                </article>
            @empty
                <div class="cart-empty-state"><strong>Keine Produkte gefunden.</strong><a class="button primary" href="{{ route('home') }}">Zum Sortiment</a></div>
            @endforelse
        </div>
    </div>
</main>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>
