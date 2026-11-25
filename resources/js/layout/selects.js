import TomSelect from 'tom-select';
import { label, onContent } from './content';

const MIN_OPTIONS = 8;

export function initSelects() {
    onContent((root) => root.querySelectorAll('select').forEach(enhance));
    document.addEventListener('hide.bs.modal', (event) => event.target.querySelectorAll('select').forEach((select) => select.tomselect?.close()));
}

export function releaseSelects(root) {
    root.querySelectorAll('select').forEach((select) => select.tomselect?.destroy());
}

function enhance(select) {
    if (select.tomselect || 'native' in select.dataset || select.disabled) {
        return;
    }

    if (!select.multiple && select.options.length < MIN_OPTIONS) {
        return;
    }

    const empty = [...select.options].find((option) => option.value === '');
    const plugins = select.multiple ? ['remove_button'] : [];

    if (!select.multiple && empty && !select.required) {
        plugins.push('clear_button');
    }

    const modal = select.closest('.modal');
    const control = new TomSelect(select, {
        allowEmptyOption: false,
        maxOptions: null,
        create: false,
        placeholder: select.dataset.placeholder ?? (empty?.textContent.trim() || label('gsSelectPlaceholder')),
        plugins,
        dropdownParent: modal ?? 'body',
        render: {
            no_results: () => `<div class="no-results">${label('gsNoResults')}</div>`,
        },
    });

    if (modal) {
        const place = () => control.isOpen && pin(control);

        control.on('dropdown_open', place);
        control.on('type', place);
        select.closest('.modal-body')?.addEventListener('scroll', place, { passive: true });
        window.addEventListener('resize', place);
    }
}

function pin(control) {
    const rect = control.control.getBoundingClientRect();
    const below = window.innerHeight - rect.bottom;
    const style = control.dropdown.style;

    style.position = 'fixed';
    style.left = `${rect.left}px`;
    style.width = `${rect.width}px`;

    if (below < 220 && rect.top > below) {
        style.top = '';
        style.bottom = `${window.innerHeight - rect.top}px`;
    } else {
        style.bottom = '';
        style.top = `${rect.bottom}px`;
    }
}
