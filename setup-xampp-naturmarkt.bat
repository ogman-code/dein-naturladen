@echo off
title Naturmarkt XAMPP Setup
cd /d "%~dp0naturmarkt-laravel"

echo Starte MySQL-Datenbank fuer Naturmarkt...
C:\xampp\mysql\bin\mysql.exe -uroot -e "CREATE DATABASE IF NOT EXISTS naturmarkt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo Fuehre Laravel-Migrationen aus...
php artisan migrate

echo.
echo Fertig. Danach kannst du nutzen:
echo   php artisan serve
echo oder mit Apache:
echo   http://naturmarkt.local
echo.
pause
