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
            <a href="{{ $customer ? route('customer.account') : route('customer.login') }}">{{ $customer ? 'Mein Konto' : 'Anmelden' }}</a>
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
                    @if(request('payment') === 'cancelled')
                        <div class="review-errors" role="alert"><strong>Zahlung abgebrochen.</strong> Dein Warenkorb ist noch vorhanden. Du kannst es erneut versuchen oder eine andere Bezahlmethode wählen.</div>
                    @endif
                    <div class="form-section">
                        <span class="payment-title">Kontakt</span>
                        <div class="checkout-field-grid">
                            <label>
                                Name
                                <input type="text" name="name" value="{{ $customer?->name }}" autocomplete="name" placeholder="Vor- und Nachname" required>
                            </label>
                            <label>
                                Handy / Telefon
                                <input type="tel" name="phone" value="{{ $customer?->phone }}" autocomplete="tel" placeholder="Telefonnummer" required>
                            </label>
                            <label>
                                E-Mail
                                <input type="email" name="email" value="{{ $customer?->email }}" autocomplete="email" placeholder="deine@email.de" required>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <span class="payment-title">Lieferadresse</span>
                        <div class="checkout-field-grid">
                            <label class="wide-field">
                                Straße und Hausnummer
                                <input type="text" name="street" value="{{ $customer?->street }}" autocomplete="street-address" placeholder="Musterstraße 12" required>
                            </label>
                            <label>
                                PLZ
                                <input type="text" name="postal_code" value="{{ $customer?->postal_code }}" autocomplete="postal-code" placeholder="12345" required>
                            </label>
                            <label>
                                Ort
                                <input type="text" name="city" value="{{ $customer?->city }}" autocomplete="address-level2" placeholder="Musterstadt" required>
                            </label>
                            <label>
                                Lieferland
                                <select name="country_code" id="shipping-country" required>
                                    @foreach ($shippingCountries as $code => $country)
                                        <option value="{{ $code }}" @selected($code === ($customer?->country_code ?? 'DE'))>{{ $country['name'] }}</option>
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
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="Kreditkarte" @disabled(!$payments['stripe_enabled'])>
                            <span class="payment-icon visa-icon" aria-hidden="true">VISA</span>
                            <span>Kreditkarte <small>Visa, Mastercard und weitere Karten · sicher über Stripe</small>@if(!$payments['stripe_enabled'])<small>Noch nicht aktiviert</small>@endif</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="Ueberweisung" checked>
                            <span class="payment-icon bank-icon" aria-hidden="true">IBAN</span>
                            <span>Überweisung</span>
                        </label>
                        @if(!$payments['paypal_enabled'] || !$payments['stripe_enabled'])
                            <small>Nicht aktivierte Zahlungsarten werden verfügbar, sobald die Händlerzugangsdaten hinterlegt sind.</small>
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
