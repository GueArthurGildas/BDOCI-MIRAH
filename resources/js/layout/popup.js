import { Modal } from 'bootstrap';

export function initPopup() {
    const element = document.querySelector('[data-autoshow]');

    if (element) {
        new Modal(element).show();
    }
}
