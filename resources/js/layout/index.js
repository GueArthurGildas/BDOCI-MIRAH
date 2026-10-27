import { initAutoSubmit } from './auto-submit';
import { initCardActions } from './card-actions';
import { initFullScreenToggle } from './fullscreen';
import { initNotifications } from './notifications';
import { hidePreloader } from './preloader';
import { initTooltips } from './tooltips';

export function initLayout() {
    initCardActions();
    initAutoSubmit();
    initFullScreenToggle();
    initTooltips();
    initNotifications();
    hidePreloader();
}
