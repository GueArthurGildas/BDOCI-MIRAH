export function initBackButtons() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-gs-back]');

        if (!button || event.ctrlKey || event.metaKey || event.shiftKey) {
            return;
        }

        const previous = document.referrer ? new URL(document.referrer) : null;

        if (previous && previous.origin === window.location.origin && previous.href !== window.location.href && window.history.length > 1) {
            event.preventDefault();
            window.history.back();
        }
    });
}
