import { Tooltip } from 'bootstrap';

export function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => new Tooltip(element));
}
