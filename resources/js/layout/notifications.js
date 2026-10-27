const TOASTED_STORAGE_KEY = 'gs.toasted-notifications';
const COUNT_CAP = 99;

function readToastedIds() {
    try {
        return new Set(JSON.parse(sessionStorage.getItem(TOASTED_STORAGE_KEY) ?? '[]'));
    } catch {
        return new Set();
    }
}

function writeToastedIds(ids) {
    try {
        sessionStorage.setItem(TOASTED_STORAGE_KEY, JSON.stringify([...ids]));
    } catch {
        return;
    }
}

function toastContainer() {
    let container = document.querySelector('.gs-toasts');

    if (!container) {
        container = document.createElement('div');
        container.className = 'gs-toasts';
        document.body.appendChild(container);
    }

    return container;
}

function element(tag, className, text) {
    const node = document.createElement(tag);
    node.className = className ?? '';

    if (text !== undefined) {
        node.textContent = text;
    }

    return node;
}

function buildToast(item, closeLabel, durationMs) {
    const toast = element('div', `gs-toast is-${item.level}`);
    toast.setAttribute('role', 'status');

    const icon = element('span', 'gs-toast-icon');
    icon.appendChild(element('i', `feather ${item.icon}`));

    const body = element('a', 'gs-toast-body');
    body.href = item.url;
    body.append(element('strong', '', item.title), element('span', '', item.message));

    const close = element('button', 'gs-toast-close');
    close.type = 'button';
    close.setAttribute('aria-label', closeLabel);
    close.appendChild(element('i', 'feather icon-x'));

    const dismiss = () => toast.remove();
    close.addEventListener('click', dismiss);
    setTimeout(dismiss, durationMs);

    toast.append(icon, body, close);

    return toast;
}

function updateBeacon(beacon, unread) {
    beacon.querySelector('[data-notification-count]').textContent = unread > COUNT_CAP ? `${COUNT_CAP}+` : String(unread);
    beacon.classList.toggle('has-alerts', unread > 0);
}

function showNewToasts(items, { closeLabel, maxToasts, durationMs }) {
    const toasted = readToastedIds();
    const fresh = items.filter((item) => !toasted.has(item.id));

    fresh.slice(0, maxToasts).forEach((item) => toastContainer().appendChild(buildToast(item, closeLabel, durationMs)));
    fresh.forEach((item) => toasted.add(item.id));
    writeToastedIds(toasted);
}

export function initNotifications() {
    const beacon = document.querySelector('[data-notification-beacon]');

    if (!beacon) {
        return;
    }

    const { feedUrl, interval, closeLabel } = beacon.dataset;
    const toastOptions = {
        closeLabel,
        maxToasts: Number(beacon.dataset.maxToasts),
        durationMs: Number(beacon.dataset.toastSeconds) * 1000,
    };

    async function poll() {
        if (document.hidden) {
            return;
        }

        try {
            const response = await fetch(feedUrl, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                return;
            }

            const feed = await response.json();
            updateBeacon(beacon, feed.unread);
            showNewToasts(feed.items, toastOptions);
        } catch {
            return;
        }
    }

    poll();
    setInterval(poll, Number(interval));
    document.addEventListener('visibilitychange', poll);
}
