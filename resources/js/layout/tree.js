export function initTrees() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-gs-tree]');

        if (!trigger) {
            return;
        }

        event.preventDefault();

        const open = trigger.dataset.gsTree === 'open';
        document.querySelectorAll('[data-gs-tree-root] details').forEach((details) => {
            details.open = open;
        });
    });
}
