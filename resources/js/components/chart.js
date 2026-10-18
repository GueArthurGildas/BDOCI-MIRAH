import Chart from 'chart.js/auto';

const THEME_FONT = "'Open Sans', sans-serif";

export function initCharts(elements) {
    Chart.defaults.font.family = THEME_FONT;
    Chart.defaults.maintainAspectRatio = false;

    elements.forEach((element) => new Chart(element, JSON.parse(element.dataset.chart)));
}
