const components = [
    { selector: '[data-map]', load: () => import('./map').then((module) => module.initMaps) },
    { selector: '[data-chart]', load: () => import('./chart').then((module) => module.initCharts) },
];

export function initComponents() {
    components.forEach(async ({ selector, load }) => {
        const elements = document.querySelectorAll(selector);

        if (elements.length) {
            (await load())(elements);
        }
    });
}
