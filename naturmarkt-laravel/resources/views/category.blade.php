<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $category['name'] }} online kaufen bei Naturmarkt.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naturmarkt | {{ $category['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>
<header class="site-header">
    <a class="skip-link" href="#produkte">Zum Sortiment springen</a>
    <div class="announcement">Kostenloser Versand ab 60 EUR | {{ $category['name'] }} direkt aus dem Naturladen | Sichere Zahlung</div>
    <nav class="nav container" aria-label="Hauptnavigation">
        <a class="brand" href="{{ route('home') }}" aria-label="Naturmarkt Startseite"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <button class="menu-toggle" aria-expanded="false" aria-controls="nav-links">Menü</button>
        <div class="nav-links" id="nav-links">
            <a href="{{ route('home') }}#kategorien">Kategorien</a>
            <a href="#produkte">{{ $category['name'] }}</a>
        </div>
        <a class="cart-button" href="{{ route('cart') }}" aria-label="Mein Warenkorb">
            <span>Warenkorb</span>
            <strong id="cart-count">0</strong>
        </a>
    </nav>
</header>

<main>
    <section class="collection-hero">
        <div class="container collection-hero-grid">
            <div>
                <nav class="breadcrumbs" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}">Home</a>
                    <span>/</span>
                    <span>{{ $category['name'] }}</span>
                </nav>
                <span class="eyebrow">Kategorie</span>
                <h1>{{ $category['name'] }}</h1>
                <p>{{ $category['count'] ?? count($products) . ' Produkte' }} aus deinem Natursortiment.</p>
                <div class="trust-row" aria-label="Kategorie Vorteile">
                    @foreach ($category['chips'] ?? [$category['count'] ?? count($products) . ' Produkte', 'Direkt bestellbar', 'Liebevoll verpackt'] as $chip)
                        <span>{{ $chip }}</span>
                    @endforeach
                </div>
            </div>
            <img src="{{ str_starts_with($category['image'], 'http') ? $category['image'] : asset('assets/images/' . $category['image']) }}" alt="{{ $category['name'] }}">
        </div>
    </section>

    <section class="shop-tools">
        <div class="container shop-tools-grid">
            <label class="search-box" for="product-search">
                <span>Suchen</span>
                <input id="product-search" type="search" placeholder="{{ $category['search_placeholder'] ?? 'Produkt suchen...' }}">
            </label>
            <div class="service-strip" aria-label="Service">
                <span>{{ $category['count'] ?? count($products) . ' Produkte' }}</span>
                <span>Auf Anfrage</span>
                <span>Liebevoll verpackt</span>
                <span>Rückgabe 14 Tage</span>
            </div>
            <label class="sort-box" for="product-sort">
                <span>Sortieren</span>
                <select id="product-sort">
                    <option value="default">Empfohlen</option>
                    <option value="price-asc">Preis aufsteigend</option>
                    <option value="price-desc">Preis absteigend</option>
                    <option value="name-asc">Name A-Z</option>
                </select>
            </label>
        </div>
    </section>

    <section class="section product-section" id="produkte">
        <div class="container">
            <div class="section-heading compact">
                <div>
                    <span class="eyebrow">Sortiment</span>
                    <h2>{{ $category['name'] }}</h2>
                </div>
            </div>
            <div class="product-grid">
                @foreach ($products as $product)
                    <article class="product-card" data-index="{{ $loop->index }}" data-product="{{ $product['search'] }}" data-name="{{ $product['name'] }}" data-price-value="{{ (float) str_replace(',', '.', str_replace('.', '', str_replace(' EUR', '', $product['price']))) }}">
                        <a class="product-media honey-media" href="{{ $product['url'] }}">
                            <img src="{{ str_starts_with($product['image'], 'http') ? $product['image'] : asset('assets/images/' . $product['image']) }}" alt="{{ $product['name'] }}">
                            <span class="product-badge">{{ $product['badge'] }}</span>
                        </a>
                        <div class="product-info">
                            <span class="product-category">{{ $category['name'] }}</span>
                            <h3><a href="{{ $product['url'] }}">{{ $product['name'] }}</a></h3>
                            <div class="product-bottom">
                                <div class="price">
                                    <strong>{{ $product['price'] }}</strong>
                                </div>
                                <button class="add-to-cart" type="button" data-name="{{ $product['name'] }}" data-price="{{ $product['price'] }}" data-weight="{{ $product['weight_grams'] }}" data-category="{{ $category['name'] }}" data-image="{{ $product['image'] }}" data-url="{{ $product['url'] }}" @disabled(!$product['active'] || $product['stock'] === 0)>{{ (!$product['active'] || $product['stock'] === 0) ? 'Nicht verfügbar' : 'In den Warenkorb' }}</button>
                            </div>
                            <button class="description-trigger" type="button" data-description-name="{{ $product['name'] }}" data-description-text="{{ $product['description'] }}" data-description-ingredients="{{ $product['ingredients'] }}" data-description-incomplete="{{ $product['description_incomplete'] ? '1' : '0' }}">Beschreibung <span aria-hidden="true">→</span></button>
                            <div class="product-mini-trust">
                                <span>Natürlich</span>
                                <span>Liebevoll verpackt</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

</main>

<aside class="cart-drawer" id="cart-drawer" aria-live="polite">
    <div>
        <strong>Warenkorb</strong>
        <button type="button" id="cart-close" aria-label="Warenkorb schließen">x</button>
    </div>
    <ul id="cart-items"></ul>
    <p id="cart-empty">Noch keine Produkte im Warenkorb.</p>
    <a class="checkout-button" href="{{ route('cart') }}">Mein Warenkorb</a>
</aside>

<footer>
    <div class="container footer-grid">
        <div><a class="brand footer-brand" href="{{ route('home') }}" aria-label="Naturmarkt Startseite"><img class="brand-logo footer-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a><p>Natürliche Produkte aus kleinen Manufakturen, professionell präsentiert für deinen Online-Verkauf.</p></div>
        <div><strong>Shop</strong><a href="{{ route('home') }}#kategorien">Kategorien</a><a href="#produkte">{{ $category['name'] }}</a></div>
        <div><strong>Service</strong><a href="{{ route('cart') }}">Warenkorb</a><a href="{{ route('shipping') }}">Versand</a><a href="{{ route('returns') }}">Widerruf</a></div>
        <div><strong>Rechtliches</strong><a href="{{ route('imprint') }}">Impressum</a><a href="{{ route('privacy') }}">Datenschutz</a><a href="{{ route('terms') }}">AGB</a></div>
    </div>
    <div class="container footer-bottom">
        <span>Hinweis gemäß § 19 UStG: Es wird keine Umsatzsteuer berechnet.</span>
        <span>© {{ date('Y') }} Naturmarkt</span>
    </div>
</footer>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>


