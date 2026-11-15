<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Vertrag widerrufen bei Naturmarkt.">
    <meta name="robots" content="noindex">
    <title>Naturmarkt | Vertrag widerrufen</title>
    <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>
<header class="site-header">
    <div class="announcement">Naturmarkt | Vertrag widerrufen</div>
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
            <a href="{{ route('withdrawal.form') }}">Vertrag widerrufen</a>
        </aside>
        <article class="legal-document withdrawal">
            <nav class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>/</span><span>Vertrag widerrufen</span></nav>

            @isset($done)
                <span class="eyebrow">Widerruf eingegangen</span>
                <h1>Dein Widerruf ist bei uns eingegangen.</h1>
                <p class="legal-intro">Wir haben deine Widerrufserklärung am <strong>{{ \Illuminate\Support\Carbon::parse($done->received_at)->format('d.m.Y') }} um {{ \Illuminate\Support\Carbon::parse($done->received_at)->format('H:i') }} Uhr</strong> erhalten. Eine Eingangsbestätigung haben wir an <strong>{{ $done->email }}</strong> gesendet.</p>
                <dl class="withdrawal-summary">
                    <div><dt>Name</dt><dd>{{ $done->name }}</dd></div>
                    <div><dt>Widerrufener Vertrag</dt><dd>{{ $done->contract_reference }}</dd></div>
                    <div><dt>E-Mail für die Bestätigung</dt><dd>{{ $done->email }}</dd></div>
                    <div><dt>Eingang</dt><dd>{{ \Illuminate\Support\Carbon::parse($done->received_at)->format('d.m.Y, H:i:s') }} Uhr</dd></div>
                </dl>
                <p>Bitte sende die Ware innerhalb von 14 Tagen an: {{ $legal['business_name'] }}, {{ $legal['owner'] }}, {{ $legal['street'] }}, {{ $legal['city'] }}. Die Einzelheiten findest du in unserer <a href="{{ route('returns') }}">Widerrufsbelehrung</a>.</p>
                <a class="button primary" href="{{ route('home') }}">Zur Startseite</a>
            @else
                <span class="eyebrow">Widerrufsfunktion</span>
                <h1>Vertrag widerrufen</h1>
                <p class="legal-intro">Hier kannst du einen bei uns geschlossenen Kaufvertrag ganz oder teilweise widerrufen. Einen Grund musst du nicht angeben. Nach dem Absenden erhältst du sofort eine Eingangsbestätigung per E-Mail.</p>

                @if ($errors->any())
                    <div class="withdrawal-errors" role="alert">
                        @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif

                <form class="withdrawal-form" method="post" action="{{ route('withdrawal.submit') }}">
                    @csrf
                    <label>
                        <span>Dein Name *</span>
                        <input type="text" name="name" value="{{ $prefill['name'] }}" autocomplete="name" maxlength="200" required>
                    </label>
                    <label>
                        <span>Welche Bestellung möchtest du widerrufen? *</span>
                        <input type="text" name="contract_reference" value="{{ $prefill['contract_reference'] }}" maxlength="500" placeholder="z. B. Bestellung #12, oder Bestelldatum und Ware" required>
                        <small>Die Bestellnummer findest du in deiner Bestellbestätigung. Möchtest du nur einzelne Artikel widerrufen, nenne sie hier bitte.</small>
                    </label>
                    <label>
                        <span>E-Mail-Adresse für die Eingangsbestätigung *</span>
                        <input type="email" name="email" value="{{ $prefill['email'] }}" autocomplete="email" maxlength="255" required>
                    </label>
                    <button class="button primary" type="submit">Widerruf bestätigen</button>
                    <small>Mit Klick auf „Widerruf bestätigen“ wird dein Widerruf verbindlich an uns übermittelt.</small>
                </form>

                <p class="withdrawal-alt">Du kannst uns deinen Widerruf auch per E-Mail an <a href="mailto:{{ $legal['email'] }}">{{ $legal['email'] }}</a> oder per Post an {{ $legal['owner'] }}, {{ $legal['street'] }}, {{ $legal['city'] }} senden.</p>
            @endisset
        </article>
    </div>
</main>
<script src="{{ asset('assets/js/site.js') }}"></script>
</body>
</html>
