export function onContent(callback) {
    callback(document);
    document.addEventListener('gs:content', (event) => callback(event.detail.root));
}

export function announceContent(root) {
    document.dispatchEvent(new CustomEvent('gs:content', { detail: { root } }));
}

export function label(name) {
    return document.documentElement.dataset[name] ?? '';
}
