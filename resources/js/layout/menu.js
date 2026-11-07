export function initMenuFolders() {
    document.querySelector('.gs-menu li.active')?.scrollIntoView({ block: 'nearest' });

    document.addEventListener('click', (event) => {
        const row = event.target.closest('[data-gs-folder]');

        if (!row) {
            return;
        }

        const folder = row.closest('.gs-folder');
        const open = folder.classList.toggle('is-open');

        row.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
}
