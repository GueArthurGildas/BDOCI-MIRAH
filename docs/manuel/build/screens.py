import http.server
import pathlib
import re
import subprocess
import sys
import time
import urllib.request

from PIL import Image

BASE = 'http://localhost:8000'
HERE = pathlib.Path(__file__).resolve().parent
OUT = HERE.parent / 'screens'
WORK = pathlib.Path(sys.argv[1]) if len(sys.argv) > 1 else HERE / 'work'
ACCOUNTS = {'agent': 'agent@example.com', 'teamlead': 'teamlead@example.com', 'director': 'director@example.com', 'admin': 'admin@example.com'}

POPUP = {
    'fr': ('Terminé', 'Dossier clos.', 'Continuer'),
    'en': ('Done', 'Case closed.', 'Continue'),
}

PAGES = [
    ('agent', 'dashboard', 'dashboard', None),
    ('agent', 'vessels', 'vessels', None),
    ('agent', 'vessel', 'vessels/2', None),
    ('agent', 'map', 'tracking/map', None),
    ('agent', 'alerts', 'tracking/alerts', None),
    ('agent', 'board', 'watch/board', None),
    ('agent', 'observation-new', 'watch/observations/create', None),
    ('agent', 'logbook', 'logbook/days/today', None),
    ('agent', 'operations', 'operations', None),
    ('agent', 'eez', 'eez-declarations', None),
    ('agent', 'witness-new', 'witnesses/create', None),
    ('agent', 'missions', 'missions', None),
    ('agent', 'notifications', 'notifications', None),
    ('agent', 'infractions-quick', 'infractions', ('click', '[data-gs-modal]')),
    ('agent', 'tour-intro', 'vessels', ('tour', 0)),
    ('agent', 'tour-step', 'vessels', ('tour', 2)),
    ('teamlead', 'rotations', 'watch/rotations', None),
    ('teamlead', 'slots', 'tracking/slots', None),
    ('teamlead', 'duplicates', 'vessels/duplicates', None),
    ('teamlead', 'licenses', 'licenses', None),
    ('teamlead', 'license', 'licenses/1', None),
    ('teamlead', 'authorized', 'licenses/authorized', None),
    ('teamlead', 'infractions', 'infractions', None),
    ('teamlead', 'infraction', 'infractions/6', None),
    ('teamlead', 'infraction-confirm', 'infractions/6', ('click', 'form[action$="/close"] button')),
    ('teamlead', 'infraction-popup', 'infractions/6', ('popup', None)),
    ('teamlead', 'messages', 'communications/messages', None),
    ('teamlead', 'message-confirm', 'communications/messages/7', ('click', 'form[data-confirm] button')),
    ('teamlead', 'mission', 'missions/4', None),
    ('teamlead', 'exports', 'reports/exports', None),
    ('director', 'fleet', 'fleet', None),
    ('director', 'reports', 'reports', None),
    ('director', 'archives', 'reports/archives', None),
    ('director', 'ai-agent', 'communications/ai-agent', None),
    ('admin', 'users', 'administration/users', None),
    ('admin', 'roles', 'administration/roles', None),
    ('admin', 'groups', 'administration/groups', None),
    ('admin', 'settings', 'administration/settings', None),
    ('admin', 'connectors', 'administration/connectors', None),
    ('admin', 'audit', 'administration/audit', None),
    ('admin', 'reference-lists', 'administration/reference-lists', None),
    ('admin', 'alert-rules', 'tracking/rules', None),
    ('admin', 'infraction-types', 'infractions/types', None),
]


def curl(args):
    return subprocess.run(['curl', '-sS', '-m', '30', *args], capture_output=True, text=True, check=True).stdout


def login(email, lang, jar):
    page = curl(['-c', jar, f'{BASE}/login'])
    token = re.search(r'name="_token" value="([^"]+)"', page).group(1)
    curl(['-b', jar, '-c', jar, '-o', '/dev/null', '--data-urlencode', f'_token={token}', '--data-urlencode', f'email={email}', '--data-urlencode', 'password=password', f'{BASE}/login'])
    curl(['-b', jar, '-c', jar, '-o', '/dev/null', f'{BASE}/locale/{lang}'])


def build_html(html, action, lang):
    html = html.replace(BASE, '')
    if action is None:
        return html
    kind, value = action
    if kind == 'tour':
        nexts = ''.join(f"setTimeout(()=>document.querySelector('[data-tour=next]').click(),{2400 + i * 700});" for i in range(value))
        script = f"<script>window.addEventListener('load',()=>{{setTimeout(()=>document.querySelector('.gs-guide [data-gs-guide]').click(),1800);{nexts}}})</script></body>"
        return html.replace('</body>', script)
    if kind == 'click':
        script = f"<script>window.addEventListener('load',()=>setTimeout(()=>document.querySelector('{value}').click(),1800))</script></body>"
        return html.replace('</body>', script)
    title, message, label = POPUP[lang]
    popup = (
        '<div class="modal fade gs-modal gs-modal-success" id="gs-popup" tabindex="-1" aria-hidden="true" data-autoshow>'
        '<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body">'
        '<span class="gs-modal-icon"><i class="feather icon-check"></i></span>'
        f'<h5>{title}</h5><p>{message}</p>'
        f'<div class="gs-modal-actions"><button type="button" class="btn btn-success" data-bs-dismiss="modal">{label}</button></div>'
        '</div></div></div></div></body>'
    )
    return html.replace('</body>', popup)


PROXY = '''
import http.server, sys, urllib.request
jar, port, root = sys.argv[1], int(sys.argv[2]), sys.argv[3]
cookies = {}
for line in open(jar):
    if line.startswith('#HttpOnly_'):
        line = line[10:]
    parts = line.strip().split('\\t')
    if len(parts) >= 7:
        cookies[parts[5]] = parts[6]
cookie = '; '.join(f'{k}={v}' for k, v in cookies.items())
class Handler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *a, **k):
        super().__init__(*a, directory=root, **k)
    def log_message(self, *a):
        pass
    def do_GET(self):
        if self.path.endswith('.html'):
            return super().do_GET()
        request = urllib.request.Request('http://localhost:8000' + self.path, headers={'Cookie': cookie, 'X-Requested-With': 'XMLHttpRequest'})
        try:
            body = urllib.request.urlopen(request).read()
            self.send_response(200)
            self.send_header('Content-Type', 'text/html; charset=utf-8')
            self.end_headers()
            self.wfile.write(body)
        except Exception:
            self.send_response(500)
            self.end_headers()
http.server.ThreadingHTTPServer(('127.0.0.1', port), Handler).serve_forever()
'''


def main():
    langs = sys.argv[2].split(',') if len(sys.argv) > 2 else ['fr', 'en']
    WORK.mkdir(parents=True, exist_ok=True)
    proxy_file = WORK / 'proxy.py'
    proxy_file.write_text(PROXY)
    port = 8800
    for lang in langs:
        (OUT / lang).mkdir(parents=True, exist_ok=True)
        for role, email in ACCOUNTS.items():
            jar = str(WORK / f'jar_{lang}_{role}')
            login(email, lang, jar)
            port += 1
            server = subprocess.Popen([sys.executable, '-I', str(proxy_file), jar, str(port), str(WORK)])
            time.sleep(1)
            try:
                for page_role, slug, path, action in PAGES:
                    if page_role != role:
                        continue
                    html = curl(['-b', jar, '-c', jar, f'{BASE}/{path}'])
                    name = f'{lang}_{role}_{slug}.html'
                    (WORK / name).write_text(build_html(html, action, lang))
                    target = OUT / lang / f'{slug}.png'
                    subprocess.run(['timeout', '90', 'google-chrome', '--headless=new', '--no-sandbox', '--disable-gpu', '--hide-scrollbars', '--force-prefers-reduced-motion', f'--screenshot={target}', '--window-size=1440,900', '--virtual-time-budget=12000', f'http://127.0.0.1:{port}/{name}'], capture_output=True)
                    if target.exists():
                        Image.open(target).convert('RGB').save(target.with_suffix('.jpg'), quality=82, optimize=True)
                        target.unlink()
                    print(lang, role, slug, target.with_suffix('.jpg').exists())
            finally:
                server.terminate()


main()
