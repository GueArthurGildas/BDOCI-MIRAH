import './vendor/jquery';
import './vendor/adminty/jquery4-compat';
import './vendor/adminty/pcoded';
import { initComponents } from './components';
import { initLayout } from './layout';

import.meta.glob('../images/**', { eager: true });

window.jQuery(() => {
    initLayout();
    initComponents();
});
