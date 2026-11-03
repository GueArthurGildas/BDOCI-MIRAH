import { refreshList } from './live-list';

const TYPING_DELAY = 350;
const TEXT_FIELDS = 'input[type="search"], input[type="text"]';
let timer = null;

function submit(form) {
    if (form.matches('.gs-table-toolbar')) {
        refreshList(form);

        return;
    }

    form.requestSubmit();
}

export function initAutoSubmit() {
    document.addEventListener('change', (event) => {
        const field = event.target.closest('[data-auto-submit]');

        if (!field?.form || field.matches(TEXT_FIELDS)) {
            return;
        }

        submit(field.form);
    });

    document.addEventListener('input', (event) => {
        const field = event.target;

        if (!field.form || !field.matches(TEXT_FIELDS) || !field.matches('[data-auto-submit], .gs-toolbar-search')) {
            return;
        }

        clearTimeout(timer);
        timer = setTimeout(() => submit(field.form), TYPING_DELAY);
    });
}
