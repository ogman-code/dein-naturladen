@echo off
"C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --no-autoupdate --url http://127.0.0.1:8000 1>>cloudflare-tunnel.log 2>>cloudflare-tunnel-error.log
