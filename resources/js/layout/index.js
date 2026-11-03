import { initAutoSubmit } from './auto-submit';
import { initCardActions } from './card-actions';
import { initConfirmations } from './confirm';
import { initPopup } from './popup';
import { initRemoteModals } from './remote-modal';
import { initFullScreenToggle } from './fullscreen';
import { initGuide } from './guide';
import { initLiveLists } from './live-list';
import { initMenuFolders } from './menu';
import { initNotifications } from './notifications';
import { initSelects } from './selects';
import { initTablePagination } from './table-pagination';
import { initPhotos } from './photos';
import { hidePreloader } from './preloader';
import { initTooltips } from './tooltips';
import { initTrees } from './tree';

export function initLayout() {
    initCardActions();
    initSelects();
    initTablePagination();
    initAutoSubmit();
    initLiveLists();
    initConfirmations();
    initRemoteModals();
    initPopup();
    initGuide();
    initFullScreenToggle();
    initTooltips();
    initMenuFolders();
    initPhotos();
    initTrees();
    initNotifications();
    hidePreloader();
}
