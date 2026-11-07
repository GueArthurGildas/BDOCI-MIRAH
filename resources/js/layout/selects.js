import TomSelect from 'tom-select';
import { label, onContent } from './content';

const MIN_OPTIONS = 8;

export function initSelects() {
    onContent((root) => root.querySelectorAll('select').forEach(enhance));
}

function enhance(select) {
    if (select.tomselect || 'native' in select.dataset || select.disabled) {
        return;
    }

    if (!select.multiple && select.options.length < MIN_OPTIONS) {
        return;
    }

    new TomSelect(select, {
        allowEmptyOption: true,
        maxOptions: null,
        create: false,
        placeholder: select.dataset.placeholder ?? label('gsSelectPlaceholder'),
        plugins: select.multiple ? ['remove_button'] : [],
        dropdownParent: select.closest('.modal-content') ?? 'body',
        render: {
            no_results: () => `<div class="no-results">${label('gsNoResults')}</div>`,
        },
    });
}
