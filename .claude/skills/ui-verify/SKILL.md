---
name: ui-verify
description: Check a page of the application visually in headless Chrome (login, fetch, screenshot, probe computed styles) and diagnose stale CSS. Use after any UI, theme or view change, before reporting a UI task done, or when styles do not seem to update.
---

# Verify the UI

Everything runs from the scratchpad directory (never from the repo). Demo accounts (password `password`): `admin@`, `director@`, `teamlead@`, `agent@example.com`. If the database was wiped, `docker compose exec -T app php artisan migrate:fresh --seed` first.

## Procedure

1. **Log in with curl** and keep the cookie jar:
   ```bash
   TOKEN=$(curl -sS -m 20 -c jar http://localhost:8000/login | grep -oP 'name="_token" value="\K[^"]+' | head -1)
   curl -sS -m 20 -b jar -c jar -o /dev/null -w "%{http_code}\n" --data-urlencode "_token=$TOKEN" --data-urlencode "email=director@example.com" --data-urlencode "password=password" http://localhost:8000/login   # expect 302
   curl -sS -m 20 -b jar -o page.html -w "%{http_code} %{size_download}b\n" http://localhost:8000/<path>
   ```
   A page of ~350 bytes is a redirect to login: the session is missing.
2. **Serve and shoot**: assets load from Vite on `localhost:5173` (the saved HTML already points there).
   ```bash
   nohup python3 -m http.server <free-port> --bind 127.0.0.1 >/dev/null 2>&1 &
   timeout 60 google-chrome --headless=new --no-sandbox --disable-gpu --hide-scrollbars --screenshot=out.png --window-size=1440,900 --virtual-time-budget=6000 http://127.0.0.1:<port>/page.html
   ```
   Then read the PNG; crop or zoom with PIL (`from PIL import Image`) to inspect details; use `--window-size=400,800` for mobile.
3. **Probe computed styles** (what the browser really applies): copy the HTML, inject a `<script>` that writes `getComputedStyle(...)` values into the DOM on load, run Chrome with `--dump-dom` and grep the output. Cross-origin stylesheets cannot be read from `document.styleSheets`.
4. **JavaScript that calls the API** (notification polling) needs a same-origin proxy: small Python server that serves the saved page and forwards `/notifications/feed` to `localhost:8000` with the cookie.

## Pitfalls we hit

- **Stale CSS**: Vite's file watcher in the `node` container silently stops seeing host edits. Symptom: a rule you changed does not apply. Fix: `docker restart bdoci-mirah-node-1` and wait for `curl -sf http://localhost:5173/@vite/client`; confirm with `curl "http://localhost:5173/resources/scss/app.scss?t=$(date +%s)"`. Always restart after adding a new SCSS/JS file.
- **Stopping the server**: use `pkill -f "[h]ttp.server <port>"` in its own command. Without the bracket trick, or inside the command that starts it, `pkill` matches its own shell (exit 144) or Chrome hangs and the tool times out.
- Always wrap curl (`-m 20`) and Chrome (`timeout 60`) so a hang cannot block the session.
- Check mobile (400 px) and the empty state of lists, not only the happy path.
