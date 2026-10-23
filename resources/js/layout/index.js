import { initAlertsDemo } from './alerts-demo';
import { initAutoSubmit } from './auto-submit';
import { initCardActions } from './card-actions';
import { initFullScreenToggle } from './fullscreen';
import { initHeaderSearch } from './header-search';
import { hidePreloader } from './preloader';
import { initTooltips } from './tooltips';
import { initVerticalMenu } from './vertical-menu';

export function initLayout() {
    initVerticalMenu();
    initCardActions();
    initAutoSubmit();
    initFullScreenToggle();
    initHeaderSearch();
    initTooltips();
    initAlertsDemo();
    hidePreloader();
}
