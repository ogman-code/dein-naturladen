<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Bestelldaten bei Naturmarkt eintragen.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naturmarkt | Kasse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>
<header class="site-header">
    <a class="skip-link" href="#kasse">Zur Kasse springen</a>
    <div class="announcement">Kasse | Lieferdaten eintragen | Bestellung absenden</div>
    <nav class="nav container" aria-label="Hauptnavigation">
        <a class="brand" href="{{ route('home') }}" aria-label="Naturmarkt Startseite"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <div class="nav-links">
            <a href="{{ route('cart') }}">Zurück zum Warenkorb</a>
        </div>
        <a class="cart-button" href="{{ route('cart') }}" aria-label="Mein Warenkorb">
            <span>Warenkorb</span>
            <strong id="cart-count">0</strong>
        </a>
    </nav>
</header>

<main>
    <section class="checkout-page" id="kasse">
        <div class="container">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <a href="{{ route('cart') }}">Warenkorb</a>
                <span>/</span>
                <span>Kasse</span>
            </nav>

            <div class="cart-heading">
                <div>
                    <h1 class="checkout-title">Lieferdaten eintragen.</h1>
                    <p class="checkout-intro">Trage deine Kontaktdaten und Lieferadresse ein. Danach wird deine Bestellung vorbereitet und von uns bearbeitet.</p>
                </div>
                <a class="button ghost" href="{{ route('cart') }}">Warenkorb bearbeiten</a>
            </div>

            <div class="checkout-layout">
                <form class="checkout-form-panel" id="checkout-form">
                    <div class="honeypot" aria-hidden="true"><label>Webseite<input name="company_website" type="text" tabindex="-1" autocomplete="off"></label></div>
                    <div class="form-section">
                        <span class="payment-title">Kontakt</span>
                        <div class="checkout-field-grid">
                            <label>
                                Name
                                <input type="text" name="name" autocomplete="name" placeholder="Vor- und Nachname" required>
                            </label>
                            <label>
                                Handy / Telefon
                                <input type="tel" name="phone" autocomplete="tel" placeholder="Telefonnummer" required>
                            </label>
                            <label>
                                E-Mail
                                <input type="email" name="email" autocomplete="email" placeholder="deine@email.de" required>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <span class="payment-title">Lieferadresse</span>
                        <div class="checkout-field-grid">
                            <label class="wide-field">
                                Straße und Hausnummer
                                <input type="text" name="street" autocomplete="street-address" placeholder="Musterstraße 12" required>
                            </label>
                            <label>
                                PLZ
                                <input type="text" name="postal_code" autocomplete="postal-code" placeholder="12345" required>
                            </label>
                            <label>
                                Ort
                                <input type="text" name="city" autocomplete="address-level2" placeholder="Musterstadt" required>
                            </label>
                            <label>
                                Lieferland
                                <select name="country_code" id="shipping-country" required>
                                    @foreach ($shippingCountries as $code => $country)
                                        <option value="{{ $code }}" @selected($code === 'DE')>{{ $country['name'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Gutscheincode
                                <input type="text" name="coupon_code" id="coupon-code" autocomplete="off" placeholder="Optional">
                            </label>
                            <label class="wide-field">
                                Hinweis zur Bestellung
                                <textarea name="notes" rows="4" placeholder="Optional: Abstellort, Wunsch, Rückfrage ..."></textarea>
                            </label>
                        </div>
                    </div>

                    <div class="payment-box">
                        <span class="payment-title">Bezahlmethode</span>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="PayPal">
                            <span class="payment-icon paypal-icon" aria-hidden="true">PayPal</span>
                            <span>PayPal @if(!$payments['paypal_enabled'])<small>Zahlungslink folgt nach der Bestellung</small>@endif</span>
                        </label>
                        @if($payments['stripe_enabled'])
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="Kreditkarte">
                            <span class="payment-icon visa-icon" aria-hidden="true">VISA</span>
                            <span>Kreditkarte</span>
                        </label>
                        @endif
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="Ueberweisung" checked>
                            <span class="payment-icon bank-icon" aria-hidden="true">IBAN</span>
                            <span>Überweisung</span>
                        </label>
                        @if(!$payments['paypal_enabled'] || !$payments['stripe_enabled'])
                            <small>PayPal ist als manuelle Zahlung verfügbar. Die automatische Weiterleitung wird später mit dem Händlerkonto verbunden.</small>
                        @endif
                    </div>

                    <label class="checkout-consent">
                        <input type="checkbox" name="terms_accepted" value="1" required>
                        <span>Ich akzeptiere die <a href="{{ route('terms') }}" target="_blank">AGB</a> und habe die <a href="{{ route('privacy') }}" target="_blank">Datenschutzerklärung</a> sowie die <a href="{{ route('returns') }}" target="_blank">Widerrufsbelehrung</a> gelesen.</span>
                    </label>
                    <button class="place-order-button" type="submit" id="place-order">Zahlungspflichtig bestellen</button>
                    <div class="checkout-trust-row">
                        <span>Persönlich geprüft</span>
                        <span>Sorgfältig verpackt</span>
                        <span>Sichere Anfrage</span>
                    </div>
                    <small id="checkout-message"></small>
                </form>

                <aside class="checkout-panel">
                    <strong>Deine Bestellung</strong>
                    <div id="cart-page-items" class="cart-page-items checkout-summary-items"></div>
                    <div id="cart-page-empty" class="cart-empty-state">
                        <strong>Dein Warenkorb ist leer.</strong>
                        <p>Lege zuerst Produkte in den Warenkorb, bevor du zur Kasse gehst.</p>
                        <a class="button primary" href="{{ route('home') }}#kategorien">Zum Shop</a>
                    </div>
                    <dl class="summary-list">
                        <div><dt>Zwischensumme</dt><dd id="cart-subtotal">0,00 EUR</dd></div>
                        <div><dt>Gesamtgewicht</dt><dd id="cart-weight">0 g</dd></div>
                        <div><dt>Versand</dt><dd id="cart-shipping">4,90 EUR</dd></div>
                        <div id="discount-row" hidden><dt>Gutschein</dt><dd id="cart-discount">−0,00 EUR</dd></div>
                        <div><dt>Gesamt</dt><dd id="cart-total">0,00 EUR</dd></div>
                    </dl>
                </aside>
            </div>
        </div>
    </section>
</main>

<script>window.NATURMARKT_SHIPPING = @json(config('naturmarkt.shipping'));</script>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>
