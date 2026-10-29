import { initAutoSubmit } from './auto-submit';
import { initCardActions } from './card-actions';
import { initConfirmations } from './confirm';
import { initPopup } from './popup';
import { initRemoteModals } from './remote-modal';
import { initFullScreenToggle } from './fullscreen';
import { initNotifications } from './notifications';
import { hidePreloader } from './preloader';
import { initTooltips } from './tooltips';

export function initLayout() {
    initCardActions();
    initAutoSubmit();
    initConfirmations();
    initRemoteModals();
    initPopup();
    initFullScreenToggle();
    initTooltips();
    initNotifications();
    hidePreloader();
}
