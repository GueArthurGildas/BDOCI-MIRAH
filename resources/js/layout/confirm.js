import { Modal } from 'bootstrap';
import { label } from './content';

const MAX_DETAILS = 8;

export function confirmationSource(form, submitter) {
    if (!(form instanceof HTMLFormElement) || form.dataset.confirmed) {
        return null;
    }

    if (submitter?.dataset.confirm) {
        return { ...submitter.dataset };
    }

    if (form.dataset.confirm) {
        return { ...form.dataset };
    }

    if (isRecord(form, submitter)) {
        return {
            confirm: label('gsConfirmRecord'),
            confirmTitle: label('gsConfirmRecordTitle'),
            confirmLabel: label('gsConfirmRecordLabel'),
            confirmDetails: JSON.stringify(summary(form)),
        };
    }

    return null;
}

export function initConfirmations() {
    const element = document.getElementById('gs-confirm');

    if (!element) {
        return;
    }

    document.body.append(element);

    const modal = new Modal(element);
    const title = element.querySelector('[data-confirm-title]');
    const message = element.querySelector('[data-confirm-message]');
    const details = element.querySelector('[data-confirm-details]');
    const accept = element.querySelector('[data-confirm-accept]');
    const defaults = { title: title.textContent, label: accept.textContent };
    let pending = null;

    document.addEventListener('submit', (event) => {
        const form = event.target;
        const source = confirmationSource(form, event.submitter);

        if (!source) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        pending = { form, submitter: event.submitter };

        title.textContent = source.confirmTitle || defaults.title;
        message.textContent = source.confirm;
        accept.textContent = source.confirmLabel || defaults.label;
        renderDetails(details, source.confirmDetails);
        modal.show();
    }, true);

    accept.addEventListener('click', () => {
        const request = pending;

        pending = null;
        modal.hide();

        if (request) {
            request.form.dataset.confirmed = '1';
            request.form.requestSubmit(request.submitter);
            setTimeout(() => delete request.form.dataset.confirmed, 0);
        }
    });

    element.addEventListener('hidden.bs.modal', () => {
        pending = null;
    });
}

function isRecord(form, submitter) {
    if ((form.getAttribute('method') ?? 'get').toLowerCase() !== 'post' || 'noConfirm' in form.dataset) {
        return false;
    }

    if (form.querySelector('input[name="_method"]')) {
        return false;
    }

    return Boolean(submitter?.hasAttribute('data-gs-record') || form.querySelector('[data-gs-record]'));
}

function summary(form) {
    const tiles = [];

    form.querySelectorAll('input, select, textarea').forEach((field) => {
        if (tiles.length >= MAX_DETAILS || !field.name || field.name.startsWith('_') || field.disabled || ['hidden', 'submit', 'button', 'file', 'password'].includes(field.type)) {
            return;
        }

        const caption = captionFor(form, field);
        const value = valueOf(field);

        if (caption && value) {
            tiles.push({ label: caption, value: value.length > 80 ? `${value.slice(0, 77)}…` : value });
        }
    });

    return tiles;
}

function captionFor(form, field) {
    const labelElement = (field.id && form.querySelector(`label[for="${CSS.escape(field.id)}"]`))
        || field.closest('.row, .mb-3, .form-group')?.querySelector('label');

    return labelElement?.textContent.replace('*', '').trim() ?? '';
}

function valueOf(field) {
    if (field.type === 'checkbox' || field.type === 'radio') {
        return field.checked ? (field.closest('label')?.textContent.trim() || '✓') : '';
    }

    if (field instanceof HTMLSelectElement) {
        return [...field.selectedOptions].filter((option) => option.value !== '').map((option) => option.textContent.trim()).join(', ');
    }

    if (field.type === 'datetime-local' && field.value) {
        return new Date(field.value).toLocaleString(document.documentElement.lang || undefined, { dateStyle: 'short', timeStyle: 'short' });
    }

    return field.value.trim();
}

function renderDetails(container, json) {
    container.replaceChildren();

    if (!json) {
        return;
    }

    JSON.parse(json).forEach(({ label: caption, value }) => {
        const tile = document.createElement('div');
        const small = document.createElement('small');
        const text = document.createElement('strong');

        small.textContent = caption;
        text.textContent = value;
        tile.append(small, text);
        container.append(tile);
    });
}
