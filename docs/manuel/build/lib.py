import html

LANG = 'fr'
HEADINGS = []
COUNTERS = {'h1': 0, 'h2': 0}


def set_lang(lang):
    global LANG
    LANG = lang
    HEADINGS.clear()
    COUNTERS.update({'h1': 0, 'h2': 0})


def t(fr, en):
    return fr if LANG == 'fr' else en


def h1(fr, en, anchor=None, numbered=True):
    COUNTERS['h1'] += 1 if numbered else 0
    COUNTERS['h2'] = 0
    number = f"{COUNTERS['h1']}. " if numbered else ''
    ident = f"h{len(HEADINGS)}"
    text = t(fr, en)
    HEADINGS.append((1, number + text, ident))
    return f'<h1 id="{ident}">{number}{text}</h1>'


def h2(fr, en):
    COUNTERS['h2'] += 1
    number = f"{COUNTERS['h1']}.{COUNTERS['h2']} "
    ident = f"h{len(HEADINGS)}"
    text = t(fr, en)
    HEADINGS.append((2, number + text, ident))
    return f'<h2 id="{ident}">{number}{text}</h2>'


def h3(fr, en):
    return f'<h3>{t(fr, en)}</h3>'


def p(fr, en):
    return f'<p>{t(fr, en)}</p>'


def ul(items):
    return '<ul>' + ''.join(f'<li>{t(*item)}</li>' for item in items) + '</ul>'


def ol(items):
    return '<ol>' + ''.join(f'<li>{t(*item)}</li>' for item in items) + '</ol>'


def note(kind, fr, en):
    labels = {'tip': ('Conseil', 'Tip'), 'warn': ('Attention', 'Warning'), 'info': ('À savoir', 'Good to know')}
    return f'<div class="note note-{kind}"><strong>{t(*labels[kind])}</strong><span>{t(fr, en)}</span></div>'


def table(head, rows, widths=None):
    cols = ''
    if widths:
        cols = '<colgroup>' + ''.join(f'<col style="width:{w}%">' for w in widths) + '</colgroup>'
    out = f'<table>{cols}<thead><tr>' + ''.join(f'<th>{t(*c) if isinstance(c, tuple) else c}</th>' for c in head) + '</tr></thead><tbody>'
    for row in rows:
        out += '<tr>' + ''.join(f'<td>{t(*c) if isinstance(c, tuple) else c}</td>' for c in row) + '</tr>'
    return out + '</tbody></table>'


def img(slug, fr, en):
    return f'<figure><img src="screens/{LANG}/{slug}.jpg" alt=""><figcaption>{t(fr, en)}</figcaption></figure>'


def proc(title, who, steps, result=None):
    out = f'<div class="proc"><div class="proc-head"><span class="proc-title">{t(*title)}</span><span class="proc-who">{t(*who)}</span></div><ol>'
    out += ''.join(f'<li>{t(*step)}</li>' for step in steps)
    out += '</ol>'
    if result:
        out += f'<div class="proc-result"><strong>{t("Résultat", "Result")}</strong> {t(*result)}</div>'
    return out + '</div>'


def check(items):
    return '<ul class="check">' + ''.join(f'<li>{t(*item)}</li>' for item in items) + '</ul>'


def kbd(text):
    return f'<kbd>{html.escape(text)}</kbd>'
