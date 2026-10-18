const OPEN_WIDTH = '200px';

export function initHeaderSearch() {
    const form = document.querySelector('[data-header-search]');

    if (!form) {
        return;
    }

    const input = form.querySelector('input');

    form.querySelector('.search-btn').addEventListener('click', () => {
        if (form.classList.contains('open') && input.value.trim()) {
            form.requestSubmit();
            return;
        }

        form.classList.add('open');
        input.style.width = OPEN_WIDTH;
        input.focus();
    });

    form.querySelector('.search-close').addEventListener('click', () => {
        input.style.width = '0';
        form.classList.remove('open');
    });
}
