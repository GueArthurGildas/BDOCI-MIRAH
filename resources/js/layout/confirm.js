import { Modal } from 'bootstrap';

export function initConfirmations() {
    const element = document.getElementById('gs-confirm');

    if (!element) {
        return;
    }

    const modal = new Modal(element);
    const title = element.querySelector('[data-confirm-title]');
    const message = element.querySelector('[data-confirm-message]');
    const details = element.querySelector('[data-confirm-details]');
    const accept = element.querySelector('[data-confirm-accept]');
    const defaults = { title: title.textContent, label: accept.textContent };
    let pending = null;

    document.addEventListener('submit', (event) => {
        const form = event.target;
        const submitter = event.submitter;
        const source = submitter?.dataset.confirm ? submitter : form;

        if (!(form instanceof HTMLFormElement) || !source.dataset.confirm || form.dataset.confirmed) {
            return;
        }

        event.preventDefault();
        pending = { form, submitter };

        title.textContent = source.dataset.confirmTitle || defaults.title;
        message.textContent = source.dataset.confirm;
        accept.textContent = source.dataset.confirmLabel || defaults.label;
        renderDetails(details, source.dataset.confirmDetails);
        modal.show();
    });

    accept.addEventListener('click', () => {
        const request = pending;

        pending = null;
        modal.hide();

        if (request) {
            request.form.dataset.confirmed = '1';
            request.form.requestSubmit(request.submitter);
        }
    });

    element.addEventListener('hidden.bs.modal', () => {
        pending = null;
    });
}

function renderDetails(container, json) {
    container.replaceChildren();

    if (!json) {
        return;
    }

    JSON.parse(json).forEach(({ label, value }) => {
        const tile = document.createElement('div');
        const caption = document.createElement('small');
        const text = document.createElement('strong');

        caption.textContent = label;
        text.textContent = value;
        tile.append(caption, text);
        container.append(tile);
    });
}
