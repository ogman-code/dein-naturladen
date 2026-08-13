<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $product['name'] }} online kaufen bei Naturmarkt.">
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $product['name'] }} | Naturmarkt">
    <meta property="og:description" content="{{ Str::limit($product['description'], 155) }}">
    <meta property="og:image" content="{{ $product['image'] }}">
    <meta property="og:url" content="{{ $product['url'] }}">
    <link rel="canonical" href="{{ $product['url'] }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naturmarkt | {{ $product['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
    <script type="application/ld+json">{!! $productSchema !!}</script>
</head>
<body>
<header class="site-header">
    <a class="skip-link" href="#produkt">Zum Produkt springen</a>
    <div class="announcement">Kostenloser Versand ab 60 EUR | Sichere Zahlung | Rückgabe 14 Tage</div>
    <nav class="nav container" aria-label="Hauptnavigation">
        <a class="brand" href="{{ route('home') }}" aria-label="Naturmarkt Startseite"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <button class="menu-toggle" aria-expanded="false" aria-controls="nav-links">Menü</button>
        <div class="nav-links" id="nav-links">
            <a href="{{ route('home') }}#kategorien">Kategorien</a>
            <a href="{{ $category['url'] }}">{{ $category['name'] }}</a>
            <a href="{{ route('contact') }}">Kontakt</a>
        </div>
        <a class="cart-button" href="{{ route('cart') }}" aria-label="Mein Warenkorb">
            <span>Warenkorb</span>
            <strong id="cart-count">0</strong>
        </a>
    </nav>
</header>

<main>
    <section class="product-detail" id="produkt">
        <div class="container">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <a href="{{ $category['url'] }}">{{ $category['name'] }}</a>
                <span>/</span>
                <span>{{ $product['name'] }}</span>
            </nav>

            <div class="product-detail-grid">
                <div class="product-detail-media">
                    <img src="{{ str_starts_with($product['image'], 'http') ? $product['image'] : asset('assets/images/' . $product['image']) }}" alt="{{ $product['name'] }}">
                </div>
                <div class="product-detail-info">
                    <span class="eyebrow">{{ $category['name'] }}</span>
                    <h1>{{ $product['name'] }}</h1>
                    <strong class="detail-price">{{ $product['price'] }}</strong>

                    <div class="product-benefit-grid">
                        <span>Natürlich ausgewählt</span>
                        <span>Liebevoll verpackt</span>
                        <span>Persönlich bearbeitet</span>
                    </div>

                    @if ($product['description'])
                        <button class="description-trigger detail-description-trigger" type="button"
                            data-description-name="{{ $product['name'] }}"
                            data-description-text="{{ $product['description'] }}"
                            data-description-ingredients="{{ $product['ingredients'] }}"
                            data-description-incomplete="{{ $product['description_incomplete'] ? '1' : '0' }}">
                            Beschreibung & Zutaten
                            <span aria-hidden="true">→</span>
                        </button>
                    @endif

                    <div class="detail-actions">
                        <button class="add-to-cart primary detail-cart-button" type="button" data-name="{{ $product['name'] }}" data-price="{{ $product['price'] }}" data-weight="{{ $product['weight_grams'] }}" data-category="{{ $category['name'] }}" data-image="{{ $product['image'] }}" data-url="{{ $product['url'] }}" @disabled(!$product['active'] || $product['stock'] === 0)>{{ (!$product['active'] || $product['stock'] === 0) ? 'Nicht verfügbar' : 'In den Warenkorb' }}</button>
                        <a class="button ghost" href="{{ $category['url'] }}">Zurück zur Kategorie</a>
                    </div>

                    <div class="product-info-panels">
                        <article>
                            <strong>Warum dieses Produkt?</strong>
                            <p>Ausgewählt für Menschen, die natürliche Produkte mit angenehmer Anwendung, ehrlicher Produktwelt und persönlicher Note suchen.</p>
                        </article>
                        <article>
                            <strong>Anwendung & Hinweis</strong>
                            <p>Bitte beachte die jeweilige Produktbeschreibung. Bei Unverträglichkeiten Inhaltsstoffe prüfen und im Zweifel Rücksprache halten.</p>
                        </article>
                        <article>
                            <strong>Versand</strong>
                            <p>Wir bereiten deine Bestellung sorgfältig vor. Ab 60 EUR ist der Versand kostenlos.</p>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($relatedProducts)
        <section class="section product-section">
            <div class="container">
                <div class="section-heading compact">
                    <div>
                        <span class="eyebrow">Dazu passend</span>
                        <h2>Ähnliche Produkte</h2>
                    </div>
                </div>
                <div class="product-grid featured-grid">
                    @foreach ($relatedProducts as $related)
                        <article class="product-card" data-product="{{ $related['search'] }}">
                            <a class="product-media" href="{{ $related['url'] }}">
                                <img src="{{ str_starts_with($related['image'], 'http') ? $related['image'] : asset('assets/images/' . $related['image']) }}" alt="{{ $related['name'] }}">
                                <span class="product-badge">{{ $related['badge'] }}</span>
                            </a>
                            <div class="product-info">
                                <span class="product-category">{{ $related['category'] }}</span>
                                <h3><a href="{{ $related['url'] }}">{{ $related['name'] }}</a></h3>
                                <div class="product-bottom">
                                    <div class="price"><strong>{{ $related['price'] }}</strong></div>
                                    <button class="add-to-cart" type="button" data-name="{{ $related['name'] }}" data-price="{{ $related['price'] }}" data-weight="{{ $related['weight_grams'] }}" data-category="{{ $related['category'] }}" data-image="{{ $related['image'] }}" data-url="{{ $related['url'] }}" @disabled(!$related['active'] || $related['stock'] === 0)>{{ (!$related['active'] || $related['stock'] === 0) ? 'Nicht verfügbar' : 'In den Warenkorb' }}</button>
                                </div>
                                <button class="description-trigger" type="button" data-description-name="{{ $related['name'] }}" data-description-text="{{ $related['description'] }}" data-description-ingredients="{{ $related['ingredients'] }}" data-description-incomplete="{{ $related['description_incomplete'] ? '1' : '0' }}">Beschreibung <span aria-hidden="true">→</span></button>
                                <div class="product-mini-trust">
                                    <span>Natürlich</span>
                                    <span>Passend dazu</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
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
        <div><strong>Shop</strong><a href="{{ route('home') }}#kategorien">Kategorien</a><a href="{{ $category['url'] }}">{{ $category['name'] }}</a></div>
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
