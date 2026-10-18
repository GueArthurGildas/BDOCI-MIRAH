import { initAutoSubmit } from './auto-submit';
import { initCardActions } from './card-actions';
import { initFullScreenToggle } from './fullscreen';
import { hidePreloader } from './preloader';
import { initTooltips } from './tooltips';
import { initVerticalMenu } from './vertical-menu';

export function initLayout() {
    initVerticalMenu();
    initCardActions();
    initAutoSubmit();
    initFullScreenToggle();
    initTooltips();
    hidePreloader();
}
