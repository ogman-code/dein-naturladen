# Baut ein Upload-fertiges ZIP fuer Shared Hosting: release\naturmarkt-release.zip
$ErrorActionPreference = 'Stop'
$project = Split-Path -Parent $PSScriptRoot
$releaseDir = Join-Path (Split-Path -Parent $project) 'release'
$stage = Join-Path $releaseDir 'naturmarkt'
$zip = Join-Path $releaseDir 'naturmarkt-release.zip'

if (Test-Path $stage) { Remove-Item -Recurse -Force $stage }
New-Item -ItemType Directory -Force $stage | Out-Null

Write-Host 'Kopiere Projektdateien...'
foreach ($dir in 'app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes') {
    robocopy (Join-Path $project $dir) (Join-Path $stage $dir) /E /NFL /NDL /NJH /NJS /NP /XF '*.log' | Out-Null
}
foreach ($file in 'artisan', 'composer.json', 'composer.lock', '.env.production.example') {
    Copy-Item (Join-Path $project $file) $stage
}
Copy-Item (Join-Path $PSScriptRoot 'schema.sql') (Join-Path $stage 'database\schema.sql')
Copy-Item (Join-Path $PSScriptRoot 'root.htaccess') (Join-Path $stage 'root.htaccess')
Get-ChildItem (Join-Path $stage 'bootstrap\cache') -Filter '*.php' -ErrorAction SilentlyContinue | Remove-Item

# Leere, beschreibbare Storage-Struktur (ohne lokale Logs, Sessions, Backups)
foreach ($dir in 'app\public', 'framework\cache\data', 'framework\sessions', 'framework\views', 'logs') {
    $path = Join-Path $stage "storage\$dir"
    New-Item -ItemType Directory -Force $path | Out-Null
    Set-Content -Path (Join-Path $path '.gitignore') -Value "*`n!.gitignore" -Encoding ascii
}

Write-Host 'Installiere Produktions-Abhaengigkeiten (ohne Dev-Pakete)...'
Push-Location $stage
$ErrorActionPreference = 'Continue'  # Composer schreibt Fortschritt nach stderr; Erfolg pruefen wir per Exit-Code
try {
    composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts
    if ($LASTEXITCODE -ne 0) { throw 'composer install ist fehlgeschlagen.' }
} finally { Pop-Location; $ErrorActionPreference = 'Stop' }

Write-Host 'Packe ZIP...'
if (Test-Path $zip) { Remove-Item -Force $zip }
# tar.exe erzeugt ZIPs mit "/" als Pfadtrenner (Compress-Archive in PS 5.1 nicht - das bricht auf Linux-Servern)
tar.exe -a -c -f $zip -C $stage .
if ($LASTEXITCODE -ne 0) { throw 'ZIP konnte nicht erstellt werden.' }

$size = '{0:N1}' -f ((Get-Item $zip).Length / 1MB)
Write-Host ""
Write-Host "Fertig: $zip ($size MB)"
