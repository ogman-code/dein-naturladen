<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naturmarkt | Besitzerzugang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body class="owner-auth-body">
<main class="owner-auth-shell">
    <section class="owner-auth-brand">
        <a href="{{ route('home') }}"><img src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
        <div>
            <span class="admin-kicker">Besitzerbereich</span>
            <h1>{{ $ownerExists ? 'Willkommen zurück.' : 'Deinen Shop einrichten.' }}</h1>
            <p>{{ $ownerExists ? 'Melde dich mit deinen persönlichen Zugangsdaten an, um Bestellungen und Produkte zu verwalten.' : 'Erstelle einmalig das sichere Besitzerkonto. Danach wird die Registrierung automatisch geschlossen.' }}</p>
        </div>
        <ul>
            <li>Bestellungen übersichtlich bearbeiten</li>
            <li>Preise und Lagerbestand verwalten</li>
            <li>Geschützter Zugang nur für den Besitzer</li>
        </ul>
    </section>
    <section class="owner-auth-panel">
        <div class="owner-auth-card">
            <a class="owner-back-link" href="{{ route('home') }}">← Zurück zum Shop</a>
            <span class="admin-kicker">{{ $ownerExists ? 'Anmeldung' : 'Ersteinrichtung' }}</span>
            <h2>{{ $ownerExists ? 'Im Shop anmelden' : 'Besitzerkonto erstellen' }}</h2>
            @if(session('success'))<p class="admin-success">{{ session('success') }}</p>@endif
            @if(session('error'))<p class="auth-error">{{ session('error') }}</p>@endif
            @if($errors->any())<p class="auth-error">{{ $errors->first() }}</p>@endif
            <form method="post" action="{{ $ownerExists ? route('owner.authenticate') : route('owner.register') }}">
                @csrf
                @unless($ownerExists)
                    <label><span>Dein Name</span><input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus></label>
                @endunless
                <label><span>E-Mail-Adresse</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required @if($ownerExists) autofocus @endif></label>
                <label><span>Passwort</span><input type="password" name="password" autocomplete="{{ $ownerExists ? 'current-password' : 'new-password' }}" minlength="{{ $ownerExists ? 1 : 10 }}" required></label>
                @unless($ownerExists)
                    <label><span>Passwort wiederholen</span><input type="password" name="password_confirmation" autocomplete="new-password" minlength="10" required></label>
                    <small>Mindestens 10 Zeichen. Verwende ein nur für diesen Shop bestimmtes Passwort.</small>
                @endunless
                <button type="submit">{{ $ownerExists ? 'Sicher anmelden' : 'Besitzerkonto erstellen' }}</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>
