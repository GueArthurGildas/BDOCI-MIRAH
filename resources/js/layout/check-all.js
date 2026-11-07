export function initCheckAll() {
    document.addEventListener('change', (event) => {
        const toggle = event.target.closest('[data-gs-check-all]');

        if (!toggle) {
            return;
        }

        toggle.closest('table')?.querySelectorAll('tbody input[type="checkbox"]:not(:disabled)').forEach((box) => {
            box.checked = toggle.checked;
        });
    });
}
