@echo off
title Naturmarkt Release bauen
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0naturmarkt-laravel\deploy\build-release.ps1"
echo.
pause
