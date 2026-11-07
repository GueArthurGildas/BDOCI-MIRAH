import { announceContent } from './content';

function targetUrl(form) {
    const params = new URLSearchParams();

    new FormData(form).forEach((value, key) => {
        if (value !== '') {
            params.append(key, value);
        }
    });

    const query = params.toString();

    return query ? `${form.action}?${query}` : form.action;
}

async function load(card, formId, url) {
    card.classList.add('is-loading');

    try {
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });

        if (!response.ok) {
            throw new Error(response.status);
        }

        const document_ = new DOMParser().parseFromString(await response.text(), 'text/html');
        const fresh = document_.getElementById(formId)?.closest('.card');

        if (!fresh) {
            throw new Error('missing');
        }

        const active = document.activeElement;
        const focus = active && card.contains(active) ? { name: active.name, start: active.selectionStart, end: active.selectionEnd } : null;

        card.querySelectorAll('select').forEach((select) => select.tomselect?.destroy());
        card.replaceWith(fresh);
        history.replaceState(null, '', url);
        announceContent(fresh);

        if (focus?.name) {
            const field = fresh.querySelector(`[name="${CSS.escape(focus.name)}"]`) ?? document.querySelector(`[form="${formId}"][name="${CSS.escape(focus.name)}"]`);

            if (field && typeof field.focus === 'function' && !field.tomselect) {
                field.focus();

                if (focus.start !== null && typeof field.setSelectionRange === 'function') {
                    field.setSelectionRange(focus.start, focus.end);
                }
            }
        }
    } catch {
        window.location.assign(url);
    }
}

export function refreshList(form) {
    const card = form.closest('.card');

    if (!card || !form.id) {
        form.requestSubmit();

        return;
    }

    load(card, form.id, targetUrl(form));
}

export function initLiveLists() {
    document.addEventListener('click', (event) => {
        const link = event.target.closest('.card-footer .pagination a.page-link');
        const card = link?.closest('.card');
        const form = card?.querySelector('form.gs-table-toolbar');

        if (!link || !form || !form.id) {
            return;
        }

        event.preventDefault();
        load(card, form.id, link.href);
    });
}
