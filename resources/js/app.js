import './vendor/jquery';
import './vendor/template/jquery4-compat';
import './vendor/template/pcoded';
import { initComponents } from './components';
import { initLayout } from './layout';

import.meta.glob('../images/**', { eager: true });

window.jQuery(() => {
    initLayout();
    initComponents();
});
