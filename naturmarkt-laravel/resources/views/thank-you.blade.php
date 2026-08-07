<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Danke für deine Bestellung bei Naturmarkt.">
    <title>Naturmarkt | Danke</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>
<main class="thank-you-page">
    <section class="thank-you-panel">
        <a class="brand" href="{{ route('home') }}" aria-label="Naturmarkt Startseite"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <span class="eyebrow">Bestellung erhalten</span>
        <h1>Danke für deine Bestellung.</h1>
        @if($order)
            <p>Deine Bestellung <strong>#{{ $order['id'] }}</strong> wurde gespeichert. Die Bestätigung geht an <strong>{{ $order['email'] }}</strong>.</p>
            <dl class="thank-you-summary">
                <div><dt>Gesamtbetrag</dt><dd>{{ number_format((float) $order['total'], 2, ',', '.') }} €</dd></div>
                <div><dt>Zahlungsart</dt><dd>{{ $order['payment_method'] === 'Ueberweisung' ? 'Überweisung' : $order['payment_method'] }}</dd></div>
                <div><dt>Status</dt><dd>Bestellung erhalten</dd></div>
            </dl>
            @if($order['payment_method'] === 'Ueberweisung')
                @php($bank = config('naturmarkt.payments.bank_transfer'))
                @php($bankIsConfigured = filled($bank['account_holder']) && filled($bank['iban']))
                <section class="bank-transfer-box" aria-labelledby="bank-transfer-title">
                    <span class="bank-transfer-kicker">Vorkasse per Überweisung</span>
                    <h2 id="bank-transfer-title">Bitte überweise den Gesamtbetrag, damit wir deine Bestellung bearbeiten können.</h2>
                    <p>Wir bereiten die Ware nach Zahlungseingang vor und versenden sie anschließend an deine Lieferadresse.</p>
                    @if($bankIsConfigured)
                        <dl class="bank-transfer-details">
                            <div><dt>Kontoinhaber</dt><dd>{{ $bank['account_holder'] }}</dd></div>
                            <div><dt>IBAN</dt><dd>{{ $bank['iban'] }}</dd></div>
                            @if(filled($bank['bic']))<div><dt>BIC</dt><dd>{{ $bank['bic'] }}</dd></div>@endif
                            @if(filled($bank['bank_name']))<div><dt>Bank</dt><dd>{{ $bank['bank_name'] }}</dd></div>@endif
                            <div><dt>Betrag</dt><dd>{{ number_format((float) $order['total'], 2, ',', '.') }} €</dd></div>
                            <div><dt>Verwendungszweck</dt><dd>Bestellung #{{ $order['id'] }}</dd></div>
                        </dl>
                        <p class="bank-transfer-note"><strong>Wichtig:</strong> Bitte gib im Verwendungszweck unbedingt deine Bestellnummer an.</p>
                    @else
                        <p class="bank-transfer-missing">Die Bankverbindung wird dir separat mit der Bestellbestätigung mitgeteilt. Bitte überweise erst, nachdem du diese erhalten hast.</p>
                    @endif
                </section>
            @endif
        @else
            <p>Deine Anfrage wurde gespeichert und wird jetzt bearbeitet. Wir melden uns schnellstmöglich zur Bestätigung und zu den nächsten Schritten.</p>
        @endif
        <div class="hero-actions">
            <a class="button primary" href="{{ route('home') }}">Zur Startseite</a>
            <a class="button ghost" href="{{ route('home') }}#kategorien">Weiter stöbern</a>
        </div>
    </section>
</main>
<script>
    localStorage.removeItem('naturmarkt-cart');
</script>
</body>
</html>
