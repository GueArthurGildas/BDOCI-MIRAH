import { Modal } from 'bootstrap';

export function initRemoteModals() {
    const element = document.getElementById('gs-remote');

    if (!element) {
        return;
    }

    const modal = new Modal(element);
    const body = element.querySelector('[data-remote-body]');

    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('[data-gs-modal]');

        if (!trigger) {
            return;
        }

        event.preventDefault();
        body.innerHTML = '<div class="gs-modal-loading"><span class="spinner-border"></span></div>';
        modal.show();

        try {
            const response = await fetch(trigger.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });

            if (!response.ok) {
                throw new Error(response.status);
            }

            body.innerHTML = await response.text();
        } catch {
            modal.hide();
            window.location.href = trigger.href;
        }
    });
}
