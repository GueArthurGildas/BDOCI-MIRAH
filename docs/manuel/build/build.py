import pathlib
import re
import subprocess
import sys

import content_a
import content_b
import content_c
import lib

HERE = pathlib.Path(__file__).resolve().parent
ROOT = HERE.parent
OUT = ROOT / 'BDOCI-MIRAH_Manuel_FR_EN.pdf'
CSS = (HERE / 'style.css').read_text()
WORK = HERE / 'work'

COVER = '''<section class="cover">
<div class="brand">BDOCI MIRAH</div>
<h1>Manuel d'utilisation<br>et feuille de route<br><small style="font-size:60%;font-weight:400;color:#f9ab56">User manual and roadmap</small></h1>
<p class="sub">Surveillance des pêches de Côte d'Ivoire — guide par rôle, programme de formation et feuille de route.<br><em>Fisheries monitoring of Côte d'Ivoire — role-based guide, training programme and roadmap.</em></p>
<div class="meta">Centre de Surveillance des Pêches (CSP) · MIRAH<br>Version 1.0 · 29 octobre 2026 / 29 October 2026<br>Partie I : Français · Part II: English</div>
<div class="band"></div>
</section>'''


def chapters(lang):
    lib.set_lang(lang)
    body = ''
    for fn in (content_a.intro, content_a.overview, content_a.interface, content_a.roles, content_b.agent, content_b.teamlead, content_c.director, content_c.admin, content_c.processes, content_c.roadmap, content_c.training, content_c.faq, content_c.glossary, content_c.annex):
        body += fn()
    return body, list(lib.HEADINGS)


def localize(text, lang):
    if lang != 'en':
        return text
    for a, b in (('licences', 'licenses'), ('Licences', 'Licenses'), ('licence', 'license'), ('Licence', 'License'), ('synchronis', 'synchroniz'), ('Synchronis', 'Synchroniz'), ('programme', 'program'), ('Programme', 'Program')):
        text = text.replace(a, b)
    return text


def toc(headings, pages, title):
    out = f'<section class="toc"><h1 class="no-break" style="border-bottom:3px solid #f58f35">{title}</h1><ol>'
    for level, text, ident in headings:
        page = pages.get(ident, '')
        out += f'<li class="l{level}"><a href="#{ident}">{text}</a><span class="dots"></span><span class="pg">{page}</span></li>'
    return out + '</ol></section>'


def document(pages_fr, pages_en):
    body_fr, heads_fr = chapters('fr')
    body_en, heads_en = chapters('en')
    body_en = localize(body_en, 'en')
    heads_en = [(l, localize(x, 'en'), i) for l, x, i in heads_en]
    en_offset = len(heads_fr)
    heads_en = [(l, x, f'e{i}') for l, x, i in heads_en]
    body_en = re.sub(r'<h([12]) id="h(\d+)"', r'<h\1 id="eh\2"', body_en)
    parts = [
        '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>BDOCI MIRAH — Manuel / Manual</title>',
        f'<base href="file://{WORK}/"><style>{CSS}</style></head><body>',
        COVER,
        '<section class="part" data-part="Partie I — Français"><h1>Partie I</h1><p>Français</p></section>',
        toc(heads_fr, pages_fr, 'Table des matières'),
        body_fr,
        '<section class="part" data-part="Part II — English"><h1>Part II</h1><p>English</p></section>',
        toc(heads_en, pages_en, 'Table of contents'),
        body_en,
        '</body></html>',
    ]
    return ''.join(parts), heads_fr, heads_en


def render(html_path, pdf_path):
    subprocess.run(['google-chrome', '--headless=new', '--no-sandbox', '--disable-gpu', '--no-pdf-header-footer', f'--print-to-pdf={pdf_path}', '--virtual-time-budget=30000', f'file://{html_path}'], check=True, capture_output=True, timeout=300)


def page_texts(pdf):
    count = int(re.search(r'Pages:\s+(\d+)', subprocess.run(['pdfinfo', str(pdf)], capture_output=True, text=True).stdout).group(1))
    pages = []
    for n in range(1, count + 1):
        pages.append(subprocess.run(['pdftotext', '-f', str(n), '-l', str(n), '-layout', str(pdf), '-'], capture_output=True, text=True).stdout)
    return pages


def normalize(text):
    return re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', '', text)).strip().lower()


def locate(headings, texts, start):
    pages, cursor = {}, start
    for level, text, ident in headings:
        needle = normalize(text)[:40]
        for index in range(cursor, len(texts)):
            if needle in normalize(texts[index]):
                pages[ident] = index + 1
                cursor = index
                break
    return pages


def main():
    work = pathlib.Path(sys.argv[1]) if len(sys.argv) > 1 else HERE / 'work'
    global WORK
    work.mkdir(parents=True, exist_ok=True)
    WORK = work
    (work / 'screens').unlink(missing_ok=True)
    link = work / 'screens'
    if not link.exists():
        link.symlink_to(ROOT / 'screens')
    html_path, pdf_path = work / 'manuel.html', work / 'manuel.pdf'
    pages_fr, pages_en = {}, {}
    for _ in range(2):
        html, heads_fr, heads_en = document(pages_fr, pages_en)
        html_path.write_text(html, encoding='utf-8')
        render(html_path, pdf_path)
        texts = page_texts(pdf_path)
        marker = next(i for i, text in enumerate(texts) if 'Part II' in text and 'English' in text)
        start_fr = next(i for i, text in enumerate(texts) if 'Ce manuel accompagne la livraison' in text)
        start_en = next(i for i, text in enumerate(texts) if 'This manual accompanies the delivery' in text)
        pages_fr = locate(heads_fr, texts, start_fr)
        pages_en = locate(heads_en, texts, start_en)
    subprocess.run(['gs', '-q', '-dNOPAUSE', '-dBATCH', '-sDEVICE=pdfwrite', '-dPDFSETTINGS=/ebook', '-dColorImageResolution=200', '-dAutoFilterColorImages=false', '-dColorImageFilter=/DCTEncode', '-dJPEGQ=82', f'-sOutputFile={OUT}', str(pdf_path)], check=True)
    print(OUT, len(texts), 'pages')


main()
