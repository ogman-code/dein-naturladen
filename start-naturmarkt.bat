@echo off
title Naturmarkt Laravel Server
cd /d "%~dp0naturmarkt-laravel"

echo Projektordner:
cd
echo.

if not exist artisan (
    echo FEHLER: artisan wurde nicht gefunden.
    echo Diese Datei muss im Ordner C:\Users\gabri\Desktop\Proicete\naturmarkt-laravel liegen.
    echo.
    pause
    exit /b 1
)

if not exist vendor\autoload.php (
    echo FEHLER: vendor\autoload.php fehlt.
    echo Fuehre zuerst aus: composer install
    echo.
    pause
    exit /b 1
)

echo Starte Laravel auf http://127.0.0.1:8000
echo Dieses Fenster offen lassen, solange du die Seite sehen willst.
echo.
php artisan serve --host=127.0.0.1 --port=8000
echo.
echo Laravel wurde beendet.
pause
