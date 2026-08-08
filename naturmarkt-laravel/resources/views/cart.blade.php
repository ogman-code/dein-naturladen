<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dein Warenkorb bei Naturmarkt.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naturmarkt | Warenkorb</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>
<header class="site-header">
    <a class="skip-link" href="#warenkorb">Zum Warenkorb springen</a>
    <div class="announcement">Warenkorb | Mengen bearbeiten | Zur Kasse gehen</div>
    <nav class="nav container" aria-label="Hauptnavigation">
        <a class="brand" href="{{ route('home') }}" aria-label="Naturmarkt Startseite"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <button class="menu-toggle" aria-expanded="false" aria-controls="nav-links">Menü</button>
        <div class="nav-links" id="nav-links">
            <div class="nav-dropdown">
                <button type="button">Kategorien</button>
                <div>
                    @foreach ($categories ?? [] as $category)
                        <a href="{{ $category['url'] }}">{{ $category['name'] }}</a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('home') }}#kontakt">Kontakt</a>
        </div>
        <a class="cart-button" href="{{ route('cart') }}" aria-label="Mein Warenkorb">
            <span>Warenkorb</span>
            <strong id="cart-count">0</strong>
        </a>
    </nav>
</header>

<main>
    <section class="cart-page" id="warenkorb">
        <div class="container">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <span>Warenkorb</span>
            </nav>

            <div class="cart-heading">
                <div>
                    <span class="eyebrow">Mein Warenkorb</span>
                    <h1>Bestellung prüfen.</h1>
                </div>
                <a class="button ghost" href="{{ route('home') }}#kategorien">Weiter einkaufen</a>
            </div>

            <div class="cart-layout">
                <section class="cart-panel">
                    <div class="cart-panel-head">
                        <strong>Artikel</strong>
                        <span id="cart-page-count">0 Produkte</span>
                    </div>
                    <div id="cart-page-items" class="cart-page-items"></div>
                    <div id="cart-page-empty" class="cart-empty-state">
                        <strong>Dein Warenkorb ist leer.</strong>
                        <p>Füge Produkte aus einer Kategorie hinzu, dann erscheinen sie hier mit Bild, Menge und Preis.</p>
                        <a class="button primary" href="{{ route('home') }}#kategorien">Zum Shop</a>
                    </div>
                </section>

                <aside class="checkout-panel">
                    <strong>Zusammenfassung</strong>
                    <dl class="summary-list">
                        <div><dt>Zwischensumme</dt><dd id="cart-subtotal">0,00 EUR</dd></div>
                        <div><dt>Gesamtgewicht</dt><dd id="cart-weight">0 g</dd></div>
                        <div><dt>Versand</dt><dd id="cart-shipping">4,90 EUR</dd></div>
                        <div><dt>Gesamt</dt><dd id="cart-total">0,00 EUR</dd></div>
                    </dl>
                    <p class="summary-note">Ab 60,00 EUR wird der Versand automatisch kostenlos berechnet.</p>

                    <a class="place-order-button checkout-link-button" href="{{ route('checkout.page') }}" id="go-to-checkout">Zur Kasse</a>
                    <small id="checkout-message"></small>
                    <form class="cart-reminder-form" method="post" action="{{ route('cart-reminders.store') }}" id="cart-reminder-form">@csrf<input type="hidden" name="cart" id="reminder-cart"><strong>Später erinnern</strong><input type="email" name="email" value="{{ $customer?->email }}" placeholder="E-Mail-Adresse" required><label><input type="checkbox" name="consent" value="1" required> Ich möchte einmalig per E-Mail an diesen Warenkorb erinnert werden.</label><button type="submit">Erinnerung aktivieren</button>@if(session('reminder_success'))<small>{{ session('reminder_success') }}</small>@endif</form>
                </aside>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="container footer-grid">
        <div><a class="brand footer-brand" href="{{ route('home') }}" aria-label="Naturmarkt Startseite"><img class="brand-logo footer-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a><p>Natürliche Produkte aus kleinen Manufakturen, professionell präsentiert für deinen Online-Verkauf.</p></div>
        <div><strong>Shop</strong><a href="{{ route('home') }}#kategorien">Kategorien</a><a href="{{ route('cart') }}">Warenkorb</a></div>
        <div><strong>Service</strong><a href="{{ route('home') }}#kontakt">Kontakt</a><a href="#">Versand</a><a href="#">Widerruf</a></div>
        <div><strong>Rechtliches</strong><a href="#">Impressum</a><a href="#">Datenschutz</a><a href="#">AGB</a></div>
    </div>
    <div class="container footer-bottom">
        <span>Hinweis gemäß § 19 UStG: Es wird keine Umsatzsteuer berechnet.</span>
        <span>© {{ date('Y') }} Naturmarkt</span>
    </div>
</footer>
<script>window.NATURMARKT_SHIPPING = @json(config('naturmarkt.shipping'));</script>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>


