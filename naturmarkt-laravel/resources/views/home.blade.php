<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Naturmarkt - Kaufseite für Honig, Bienenprodukte, Salben, Sirup, Seifen und Kerzen.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naturmarkt | Naturprodukte online kaufen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>
<aside class="shop-intro" id="shop-intro" aria-label="Naturmarkt Produktvorschau" aria-modal="true" role="dialog">
    <div class="shop-intro-slides" aria-hidden="true">
        @foreach(array_slice($products, 0, 5) as $product)
            <figure class="shop-intro-slide" style="--intro-index: {{ $loop->index }}">
                <img src="{{ str_starts_with($product['image'], 'http') ? $product['image'] : asset('assets/images/' . $product['image']) }}" alt="">
                <figcaption><span>{{ $product['category'] }}</span><strong>{{ $product['name'] }}</strong></figcaption>
            </figure>
        @endforeach
    </div>
    <div class="shop-intro-overlay"></div>
    <div class="shop-intro-content">
        <img src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt">
        <span>Natürlich. Persönlich. Mit Sorgfalt.</span>
        <h1>Entdecke unsere Naturprodukte.</h1>
        <p>Honig, Bienenkraft, natürliche Pflege und liebevoll ausgewählte Produkte.</p>
        <button type="button" id="shop-intro-close">Shop entdecken</button>
    </div>
    <button class="shop-intro-skip" type="button" id="shop-intro-skip">Überspringen</button>
    <div class="shop-intro-progress" aria-hidden="true"><span></span></div>
</aside>
<header class="site-header">
    <a class="skip-link" href="#kategorien">Zum Sortiment springen</a>
    <div class="announcement">Kostenloser Versand ab 60 EUR | Versand nach DE, AT, NL und LU | Sichere Zahlung</div>
    <nav class="nav container" aria-label="Hauptnavigation">
        <a class="brand" href="#start" aria-label="Naturmarkt Startseite"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <button class="menu-toggle" aria-expanded="false" aria-controls="nav-links">Menü</button>
        <div class="nav-links" id="nav-links">
            <div class="nav-dropdown">
                <button type="button">Kategorien</button>
                <div>
                    @foreach ($categories as $category)
                        <a href="{{ $category['url'] }}">{{ $category['name'] }}</a>
                    @endforeach
                </div>
            </div>
            <a href="#kontakt">Kontakt</a>
            <a href="{{ route('owner.login') }}">Besitzer</a>
            <form class="header-search" action="{{ route('search') }}" method="get">
                <label class="sr-only" for="global-search">Produkte suchen</label>
                <input id="global-search" name="q" type="search" placeholder="Produkte suchen">
            </form>
        </div>
        <a class="cart-button" href="{{ route('cart') }}" aria-label="Mein Warenkorb">
            <span>Warenkorb</span>
            <strong id="cart-count">0</strong>
        </a>
    </nav>
</header>

<main>
    <section class="hero-shop" id="start">
        <div class="container hero-shop-grid">
            <div class="hero-shop-copy">
                <span class="eyebrow">Naturmarkt Online</span>
                <h1>Naturprodukte mit Sorgfalt ausgewählt.</h1>
                <p>Honig, Bienenkraft, natürliche Pflege und kleine Dinge, die Wärme in den Alltag bringen. Übersichtlich, liebevoll präsentiert und persönlich bearbeitet.</p>
                <div class="hero-actions">
                    <a class="button primary" href="#kategorien">Sortiment ansehen</a>
                    <a class="button ghost" href="{{ route('cart') }}">Warenkorb</a>
                </div>
                <div class="trust-row" aria-label="Shop Vorteile">
                    <span>Kleine Manufakturen</span>
                    <span>Natürliche Inhaltsstoffe</span>
                    <span>Liebevoll verpackt</span>
                </div>
            </div>
            <div class="hero-product hero-top-slider" data-hero-slider aria-label="Top 5 Bestseller">
                <div class="hero-slider-label">
                    <span>Top 5</span>
                    <strong>Bestseller</strong>
                </div>
                <div class="hero-slider-track">
                    @foreach ($products as $product)
                        <a class="hero-slide" href="{{ $product['url'] }}" aria-label="{{ $product['name'] }} ansehen">
                            <img src="{{ str_starts_with($product['image'], 'http') ? $product['image'] : asset('assets/images/' . $product['image']) }}" alt="{{ $product['name'] }}">
                            <div class="hero-product-card">
                                <span>Top {{ $loop->iteration }} von 5</span>
                                <strong>{{ $product['name'] }}</strong>
                                <small>{{ $product['category'] }} · {{ $product['price'] }}</small>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="hero-slider-controls" aria-label="Top Seller Navigation">
                    <button type="button" class="hero-slider-button" data-hero-prev aria-label="Vorheriger Top Seller">‹</button>
                    <div class="hero-slider-dots" aria-hidden="true">
                        @foreach ($products as $product)
                            <span class="{{ $loop->first ? 'active' : '' }}"></span>
                        @endforeach
                    </div>
                    <button type="button" class="hero-slider-button" data-hero-next aria-label="Nächster Top Seller">›</button>
                </div>
            </div>
        </div>
    </section>

    <section class="service-band" aria-label="Naturmarkt Service">
        <div class="container service-band-grid">
            <span>Liebevoll verpackt</span>
            <span>Kostenloser Versand ab 60 EUR</span>
            <span>Persönlich bearbeitet</span>
            <span>Naturprodukte mit Sorgfalt</span>
        </div>
    </section>

    <section class="section" id="kategorien">
        <div class="container">
            <div class="section-heading compact">
                <div>
                    <span class="eyebrow">Kategorien</span>
                </div>
                <p>Entdecke Honig, Pflege, Kerzen, Sirupe und ausgewählte Naturgeschenke.</p>
            </div>
            <div class="category-grid shop-category-grid">
                @foreach ($categories as $category)
                    <a class="category-card image-card" href="{{ $category['url'] ?? '#kategorien' }}" data-filter="{{ $category['name'] }}">
                        <img src="{{ str_starts_with($category['image'], 'http') ? $category['image'] : asset('assets/images/' . $category['image']) }}" alt="{{ $category['name'] }}">
                        <span>{{ $category['count'] }}</span>
                        <h3>{{ $category['name'] }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section bestsellers" id="bestseller">
        <div class="container">
            <div class="section-heading compact slider-heading">
                <div>
                    <span class="eyebrow">Bestseller</span>
                    <h2>Top 5 Bestseller</h2>
                </div>
                <div class="slider-copy">
                    <p>Swipe oder nutze die Pfeile, um alle fünf beliebtesten Produkte zu sehen.</p>
                    <div class="slider-controls" aria-label="Bestseller Navigation">
                        <button type="button" class="slider-button" data-slider-prev aria-label="Vorherige Bestseller">‹</button>
                        <button type="button" class="slider-button" data-slider-next aria-label="Nächste Bestseller">›</button>
                    </div>
                </div>
            </div>
            <div class="bestseller-slider" data-slider>
                @foreach ($products as $product)
                    <article class="product-card" data-index="{{ $loop->index }}" data-product="{{ $product['search'] }}" data-name="{{ $product['name'] }}" data-price-value="{{ (float) str_replace(',', '.', str_replace('.', '', str_replace(' EUR', '', $product['price']))) }}">
                        <a class="product-media" href="{{ $product['url'] }}">
                            <img src="{{ str_starts_with($product['image'], 'http') ? $product['image'] : asset('assets/images/' . $product['image']) }}" alt="{{ $product['name'] }}">
                            <span class="product-badge">{{ $loop->first ? 'Beliebt' : $product['badge'] }}</span>
                        </a>
                        <div class="product-info">
                            <span class="product-category">{{ $product['category'] }}</span>
                            <h3><a href="{{ $product['url'] }}">{{ $product['name'] }}</a></h3>
                            <div class="product-bottom">
                                <div class="price"><strong>{{ $product['price'] }}</strong></div>
                                <button class="add-to-cart" type="button" data-name="{{ $product['name'] }}" data-price="{{ $product['price'] }}" data-weight="{{ $product['weight_grams'] }}" data-category="{{ $product['category'] }}" data-image="{{ $product['image'] }}" data-url="{{ $product['url'] }}" @disabled(!$product['active'] || $product['stock'] === 0)>{{ (!$product['active'] || $product['stock'] === 0) ? 'Nicht verfügbar' : 'In den Warenkorb' }}</button>
                            </div>
                            <button class="description-trigger" type="button" data-description-name="{{ $product['name'] }}" data-description-text="{{ $product['description'] }}" data-description-ingredients="{{ $product['ingredients'] }}" data-description-incomplete="{{ $product['description_incomplete'] ? '1' : '0' }}">Beschreibung <span aria-hidden="true">→</span></button>
                            <div class="product-mini-trust">
                                <span>Natürlich</span>
                                <span>Auf Anfrage</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="values product-story" id="werte">
        <div class="container values-grid">
            <div class="values-copy">
                <span class="eyebrow light">Unsere Naturprodukte</span>
                <h2>Natürliche Auswahl für Genuss, Pflege und ein warmes Zuhause.</h2>
                <p>Bei Naturmarkt findest du Produkte, die nah an der Natur bleiben: Honig, Propolis, Pflege mit Bienenwachs, handgemachte Seifen, Sirupe und Kerzen mit ruhigem, ehrlichem Charakter.</p>
            </div>
            <div class="value-list">
                <article>
                    <img src="{{ asset('assets/images/product-honig.png') }}" alt="Honig und Bienenprodukte">
                    <div>
                        <span>01</span>
                        <h3>Honig und Bienenprodukte</h3>
                        <p>Klassische Honigsorten, Honigmischungen, Propolis, Pollen und Gelee Royal bilden das Herz unseres Sortiments.</p>
                    </div>
                </article>
                <article>
                    <img src="{{ asset('assets/images/product-salben.png') }}" alt="Natürliche Pflegeprodukte">
                    <div>
                        <span>02</span>
                        <h3>Pflege mit natürlichen Zutaten</h3>
                        <p>Salben, Cremes, Seifen und Lippenpflege verbinden Bienenwachs, Pflanzenöle und Kräuter zu alltagstauglicher Naturpflege.</p>
                    </div>
                </article>
                <article>
                    <img src="{{ asset('assets/images/product-kerzen.png') }}" alt="Kerzen, Sirupe und Naturgeschenke">
                    <div>
                        <span>03</span>
                        <h3>Wärme, Duft und Genuss</h3>
                        <p>Kerzen, Sirupe und ausgewählte Naturgeschenke bringen ruhige Wärme in Zuhause, Küche und kleine tägliche Rituale.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="newsletter" id="kontakt">
        <div class="container newsletter-inner">
            <div>
                <span class="eyebrow light">Kontakt</span>
                <h2>Fragen zu Produkten oder Versand?</h2>
            </div>
            <form action="{{ route('newsletter') }}" method="post">
                @csrf
                <label class="sr-only" for="email">E-Mail-Adresse</label>
                <input id="email" name="email" type="email" placeholder="deine@email.de" required>
                <button type="submit">Anfragen</button>
                @if(isset($errors))
                    @error('email')<small>{{ $message }}</small>@enderror
                @endif
                @if(session('success'))<small>{{ session('success') }}</small>@endif
            </form>
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
        <div><a class="brand footer-brand" href="#start" aria-label="Naturmarkt Startseite"><img class="brand-logo footer-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a><p>Honig, Bienenprodukte, Pflege, Kerzen und Naturgeschenke mit Sorgfalt ausgewählt.</p></div>
        <div><strong>Shop</strong><a href="#kategorien">Kategorien</a><a href="{{ route('cart') }}">Warenkorb</a></div>
        <div><strong>Service</strong><a href="{{ route('contact') }}">Kontakt</a><a href="{{ route('shipping') }}">Versand</a><a href="{{ route('returns') }}">Widerruf</a></div>
        <div><strong>Rechtliches</strong><a href="{{ route('imprint') }}">Impressum</a><a href="{{ route('privacy') }}">Datenschutz</a><a href="{{ route('terms') }}">AGB</a></div>
    </div>
    <div class="container footer-bottom">
        <span>Hinweis gemäß § 19 UStG: Es wird keine Umsatzsteuer berechnet.</span>
        <span>&copy; {{ date('Y') }} Naturmarkt</span>
    </div>
</footer>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>


