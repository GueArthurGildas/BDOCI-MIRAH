import { Tooltip } from 'bootstrap';
import { onContent } from './content';

export function initTooltips() {
    onContent((root) => root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => Tooltip.getOrCreateInstance(element)));
}
