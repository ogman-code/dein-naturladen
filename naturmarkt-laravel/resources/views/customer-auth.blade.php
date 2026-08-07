<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
    <title>Naturmarkt | Kundenkonto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body class="customer-auth-body">
<header class="account-simple-header container"><a href="{{ route('home') }}"><img src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a><a href="{{ route('home') }}">Zurück zum Shop</a></header>
<main class="customer-auth-shell container">
    <section class="customer-auth-intro"><span class="eyebrow">Mein Naturmarkt</span><h1>Ein Konto für deine Bestellungen.</h1><p>Speichere deine Lieferadresse, fülle die Kasse schneller aus und behalte deine Bestellungen im Blick.</p><ul><li>Bestellverlauf einsehen</li><li>Adresse sicher speichern</li><li>Schneller bestellen</li></ul></section>
    <div class="customer-auth-forms">
        <section class="customer-auth-card"><span class="eyebrow">Willkommen zurück</span><h2>Anmelden</h2>
            @if(session('error'))<p class="auth-error">{{ session('error') }}</p>@endif
            @if($errors->any())<p class="auth-error">{{ $errors->first() }}</p>@endif
            <form method="post" action="{{ route('customer.authenticate') }}">@csrf
                <label><span>E-Mail-Adresse</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></label>
                <label><span>Passwort</span><input type="password" name="password" autocomplete="current-password" required></label>
                <button type="submit">Anmelden</button>
            </form>
        </section>
        <section class="customer-auth-card"><span class="eyebrow">Neu dabei</span><h2>Konto erstellen</h2>
            <form method="post" action="{{ route('customer.register') }}">@csrf
                <label><span>Name</span><input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required></label>
                <label><span>E-Mail-Adresse</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></label>
                <label><span>Passwort</span><input type="password" name="password" minlength="10" autocomplete="new-password" required></label>
                <label><span>Passwort wiederholen</span><input type="password" name="password_confirmation" minlength="10" autocomplete="new-password" required></label>
                <small>Mindestens 10 Zeichen.</small><button type="submit">Kostenlos registrieren</button>
            </form>
        </section>
    </div>
</main>
</body></html>
