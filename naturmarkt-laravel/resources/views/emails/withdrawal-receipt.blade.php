@php($received = \Illuminate\Support\Carbon::parse($withdrawal->received_at))
<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f2efe6;color:#26362a;font-family:Arial,sans-serif;padding:28px 12px">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#fff;border-radius:22px;overflow:hidden;box-shadow:0 12px 35px rgba(43,73,47,.12)">
<tr><td style="height:8px;background:#6c8c4a"></td></tr>
<tr><td style="padding:34px 42px 38px">
<div style="display:inline-block;padding:7px 12px;border-radius:999px;background:#e9f0df;color:#466137;font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase">{{ !empty($forShop) ? 'Neuer Widerruf' : 'Eingangsbestätigung' }}</div>
@if (!empty($forShop))
<h1 style="margin:18px 0 14px;font-family:Georgia,serif;font-size:26px;line-height:1.25;color:#294932">Über die Widerrufsfunktion ist ein Widerruf eingegangen.</h1>
<p style="margin:0 0 20px;color:#596459;font-size:16px;line-height:1.6">Die Eingangsbestätigung wurde automatisch an den Kunden gesendet. Bitte die Rückabwicklung (Rücksendung, Erstattung binnen 14 Tagen) veranlassen. Antworten auf diese E-Mail gehen direkt an den Kunden.</p>
@else
<h1 style="margin:18px 0 14px;font-family:Georgia,serif;font-size:28px;line-height:1.25;color:#294932">Hallo {{ $withdrawal->name }},</h1>
<p style="margin:0 0 20px;color:#596459;font-size:16px;line-height:1.6">hiermit bestätigen wir den Eingang deiner Widerrufserklärung, die du über die Widerrufsfunktion auf unserer Webseite abgegeben hast.</p>
@endif
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:15px;line-height:1.5">
<tr><td style="padding:10px 0;border-top:1px solid #e6e9e2;color:#6b755f;width:42%">Inhalt der Erklärung</td><td style="padding:10px 0;border-top:1px solid #e6e9e2">Hiermit widerrufe ich den folgenden Vertrag.</td></tr>
<tr><td style="padding:10px 0;border-top:1px solid #e6e9e2;color:#6b755f">Name</td><td style="padding:10px 0;border-top:1px solid #e6e9e2">{{ $withdrawal->name }}</td></tr>
<tr><td style="padding:10px 0;border-top:1px solid #e6e9e2;color:#6b755f">Widerrufener Vertrag</td><td style="padding:10px 0;border-top:1px solid #e6e9e2">{{ $withdrawal->contract_reference }}</td></tr>
<tr><td style="padding:10px 0;border-top:1px solid #e6e9e2;color:#6b755f">E-Mail-Adresse</td><td style="padding:10px 0;border-top:1px solid #e6e9e2">{{ $withdrawal->email }}</td></tr>
<tr><td style="padding:10px 0;border-top:1px solid #e6e9e2;border-bottom:1px solid #e6e9e2;color:#6b755f">Eingang</td><td style="padding:10px 0;border-top:1px solid #e6e9e2;border-bottom:1px solid #e6e9e2"><strong>{{ $received->format('d.m.Y') }} um {{ $received->format('H:i:s') }} Uhr</strong></td></tr>
</table>
@if (empty($forShop))
<p style="margin:22px 0 0;color:#596459;font-size:15px;line-height:1.6">Bitte sende die Ware unverzüglich, spätestens binnen vierzehn Tagen ab heute, an:<br><strong>{{ $legal['business_name'] }}, {{ $legal['owner'] }}, {{ $legal['street'] }}, {{ $legal['city'] }}</strong></p>
<p style="margin:14px 0 0;color:#596459;font-size:15px;line-height:1.6">Wir erstatten dir alle Zahlungen einschließlich der Standard-Lieferkosten spätestens binnen vierzehn Tagen. Einzelheiten findest du in unserer <a href="{{ route('returns') }}" style="color:#315d38">Widerrufsbelehrung</a>. Bei Fragen erreichst du uns unter {{ $legal['email'] }}.</p>
@endif
</td></tr>
<tr><td align="center" style="padding:24px;background:#294932;color:#dce7d7;font-size:12px;line-height:1.6">{{ $legal['business_name'] }} · {{ $legal['owner'] }} · {{ $legal['street'] }} · {{ $legal['city'] }}<br><a href="{{ route('imprint') }}" style="color:#fff">Impressum</a> · <a href="{{ route('privacy') }}" style="color:#fff">Datenschutz</a></td></tr>
</table></td></tr></table></body></html>
