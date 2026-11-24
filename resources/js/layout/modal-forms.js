import { Modal } from 'bootstrap';
import { announceContent, onContent } from './content';

const FORM_PATH = /\/(create|edit|rectify)\/?$/;
const FIELD_SELECTOR = 'input, select, textarea';

export function initModalForms() {
    onContent(markRequired);

    const element = document.getElementById('gs-form-modal');

    if (!element) {
        return;
    }

    const modal = new Modal(element);
    const title = element.querySelector('[data-form-title]');
    const body = element.querySelector('[data-form-body]');
    const pageLink = element.querySelector('[data-form-page]');

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('a[href]');

        if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || !opensInModal(link)) {
            return;
        }

        event.preventDefault();
        await open(link.href, link.textContent.trim());
    });

    element.addEventListener('click', (event) => {
        const dismiss = event.target.closest('[data-gs-dismiss]');

        if (dismiss && body.contains(dismiss)) {
            event.preventDefault();
            modal.hide();
        }
    });

    body.addEventListener('submit', (event) => {
        const form = event.target;

        if ((form.getAttribute('method') ?? 'get').toLowerCase() !== 'post') {
            return;
        }

        event.preventDefault();
        submit(form, event.submitter);
    });

    async function open(url, fallbackTitle) {
        title.textContent = fallbackTitle;
        pageLink.href = url;
        body.innerHTML = '<div class="gs-modal-loading"><span class="spinner-border"></span></div>';
        modal.show();

        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });

            if (!response.ok || response.redirected) {
                throw new Error(String(response.status));
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const content = page.querySelector('.page-body');

            if (!content) {
                throw new Error('no content');
            }

            title.textContent = page.querySelector('.page-header h4')?.textContent.trim() || fallbackTitle;
            body.replaceChildren(...content.childNodes);
            announceContent(body);
            body.querySelector(`${FIELD_SELECTOR}:not([type="hidden"]):not([disabled])`)?.focus({ preventScroll: true });
        } catch {
            modal.hide();
            window.location.href = url;
        }
    }

    async function submit(form, submitter) {
        clearErrors(form);
        submitter?.setAttribute('disabled', 'disabled');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form, submitter ?? undefined),
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', 'X-Gs-Modal': '1' },
                credentials: 'same-origin',
            });

            if (response.status === 422) {
                showErrors(form, (await response.json()).errors ?? {});

                return;
            }

            if (response.status === 419) {
                window.location.reload();

                return;
            }

            const type = response.headers.get('Content-Type') ?? '';

            if (response.ok && type.includes('application/json')) {
                const data = await response.json();
                window.location.href = data.redirect ?? window.location.href;

                return;
            }

            if (response.ok && type.includes('text/html')) {
                document.open();
                document.write(await response.text());
                document.close();

                return;
            }

            throw new Error(String(response.status));
        } catch {
            alertOn(form, element.dataset.formFailed);
        } finally {
            submitter?.removeAttribute('disabled');
        }
    }

    function showErrors(form, errors) {
        let first = null;
        const general = [];

        Object.entries(errors).forEach(([key, messages]) => {
            const field = fieldFor(form, key);
            const message = Array.isArray(messages) ? messages[0] : messages;

            if (!field) {
                general.push(message);

                return;
            }

            field.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            feedback.dataset.gsError = '';
            feedback.textContent = message;
            const anchor = field.closest('.ts-wrapper') ?? field.parentElement.querySelector('.ts-wrapper') ?? field;
            (field.closest('.checkbox-fade, .input-group') ?? anchor).after(feedback);
            first ??= field;
        });

        alertOn(form, [element.dataset.formError, ...general].join(' '));
        (first ?? form).scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function alertOn(form, message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.dataset.gsError = '';
        alert.textContent = message;
        form.prepend(alert);
    }
}

function opensInModal(link) {
    if ('gsNoModal' in link.dataset || link.target === '_blank' || link.hasAttribute('download') || link.closest('.gs-sidebar, .gs-topbar, nav')) {
        return false;
    }

    const url = new URL(link.href, window.location.href);

    if (url.origin !== window.location.origin || url.pathname === window.location.pathname) {
        return false;
    }

    return 'gsModalForm' in link.dataset || FORM_PATH.test(url.pathname);
}

function fieldFor(form, key) {
    const parts = key.split('.');
    const bracketed = parts[0] + parts.slice(1).map((part) => `[${part}]`).join('');
    const candidates = [key, bracketed, `${bracketed}[]`, `${parts[0]}[]`, parts[0]];

    for (const name of candidates) {
        const field = form.querySelector(`[name="${CSS.escape(name)}"]:not([type="hidden"])`);

        if (field) {
            return field;
        }
    }

    return null;
}

function clearErrors(form) {
    form.querySelectorAll('[data-gs-error]').forEach((node) => node.remove());
    form.querySelectorAll('.is-invalid').forEach((node) => node.classList.remove('is-invalid'));
}

function markRequired(root) {
    root.querySelectorAll('[required]').forEach((field) => {
        const label = (field.id && root.querySelector?.(`label[for="${CSS.escape(field.id)}"]`))
            || field.closest('.row, .mb-3, .form-group')?.querySelector('label.form-label, label.col-form-label');

        label?.classList.add('gs-required');
    });
}
