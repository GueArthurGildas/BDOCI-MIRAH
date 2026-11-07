const OPEN_DELAY = 250;
const CLOSE_DELAY = 200;
const CACHE_SECONDS = 30;

export function initHoverPreview() {
    const cache = new Map();
    let panel = null;
    let openTimer = null;
    let closeTimer = null;
    let current = null;

    const close = () => {
        clearTimeout(openTimer);
        panel?.remove();
        panel = null;
        current = null;
    };

    const scheduleClose = () => {
        clearTimeout(openTimer);
        closeTimer = setTimeout(close, CLOSE_DELAY);
    };

    const content = async (url) => {
        const hit = cache.get(url);

        if (hit && Date.now() - hit.at < CACHE_SECONDS * 1000) {
            return hit.html;
        }

        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });

        if (!response.ok) {
            throw new Error(response.status);
        }

        const html = await response.text();
        cache.set(url, { html, at: Date.now() });

        return html;
    };

    const open = async (item) => {
        try {
            const html = await content(item.dataset.gsPreview);

            if (current !== item) {
                return;
            }

            panel?.remove();
            panel = document.createElement('div');
            panel.className = 'gs-preview-panel';
            panel.innerHTML = html;
            panel.addEventListener('mouseenter', () => clearTimeout(closeTimer));
            panel.addEventListener('mouseleave', scheduleClose);
            document.body.append(panel);

            const rect = item.getBoundingClientRect();
            const top = Math.min(rect.top, window.innerHeight - panel.offsetHeight - 12);
            panel.style.top = `${Math.max(top, 8)}px`;
            panel.style.left = `${rect.right + 10}px`;
        } catch {
            close();
        }
    };

    document.addEventListener('mouseover', (event) => {
        const item = event.target.closest('[data-gs-preview]');

        if (!item || item === current) {
            return;
        }

        clearTimeout(closeTimer);
        clearTimeout(openTimer);
        current = item;
        openTimer = setTimeout(() => open(item), OPEN_DELAY);
    });

    document.addEventListener('mouseout', (event) => {
        const item = event.target.closest('[data-gs-preview]');

        if (item && !item.contains(event.relatedTarget) && !panel?.contains(event.relatedTarget)) {
            scheduleClose();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
}
