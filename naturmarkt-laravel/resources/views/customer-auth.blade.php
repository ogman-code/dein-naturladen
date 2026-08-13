<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Naturmarkt | {{ $mode==='login'?'Kundenlogin':'Registrierung' }}</title><link rel="stylesheet" href="{{ asset('assets/css/site.css') }}"></head><body>
<main class="auth-page"><section class="auth-card"><a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/images/naturmarkt-logo.svg') }}" alt="Naturmarkt"></a>
<span class="eyebrow">Mein Naturmarkt</span><h1>{{ $mode==='login'?'Willkommen zurück.':'Kundenkonto erstellen.' }}</h1>@if($mode==='login')<p>Ein Login für Kunden und Besitzer.</p>@endif
@if(session('success'))<p class="admin-success">{{ session('success') }}</p>@endif @if(session('error'))<p class="auth-error">{{ session('error') }}</p>@endif
<form method="post" action="{{ $mode==='login'?route('customer.login.store'):route('customer.register.store') }}">@csrf
<div class="honeypot" aria-hidden="true"><input name="company_website" tabindex="-1" autocomplete="off"></div>
@if($mode==='register')<label><span>Name</span><input name="name" value="{{ old('name') }}" required autocomplete="name"></label>@endif
<label><span>E-Mail</span><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
<label><span>Passwort</span><input type="password" name="password" required minlength="{{ $mode==='login'?1:12 }}" autocomplete="{{ $mode==='login'?'current-password':'new-password' }}"></label>
@if($mode==='register')<label><span>Passwort wiederholen</span><input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></label><label class="checkout-consent"><input type="checkbox" name="privacy_accepted" value="1" required><span>Ich akzeptiere die <a href="{{ route('privacy') }}">Datenschutzerklärung</a>.</span></label>@endif
@if($errors->any())<p class="auth-error">{{ $errors->first() }}</p>@endif
<button class="button primary" type="submit">{{ $mode==='login'?'Anmelden':'Konto erstellen' }}</button></form>
@if($mode==='login')<p><a href="{{ route('customer.password.request') }}">Passwort vergessen?</a></p><p>Noch kein Konto? <a href="{{ route('customer.register') }}">Jetzt registrieren</a></p>@else<p>Schon registriert? <a href="{{ route('customer.login') }}">Zum Login</a></p>@endif
</section></main></body></html>
