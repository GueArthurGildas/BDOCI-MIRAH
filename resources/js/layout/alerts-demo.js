// ============================================================
//  Alertes SIMULÉES (démo) : un message en haut à droite toutes les 30 s.
//  Pour brancher de vraies alertes : remplacer ALERTS par un appel API
//  (fetch) dans showNext(), le reste (toast + balise) ne change pas.
// ============================================================
const INTERVAL_MS = 30_000;   // fréquence : toutes les 30 secondes
const FIRST_DELAY_MS = 4_000; // première alerte 4 s après le chargement (démo)
const DISPLAY_MS = 8_000;     // durée d'affichage d'un message

const ALERTS = [
    { level: 'danger',  icon: 'icon-radio',          title: 'Balise en rupture',      text: 'Le navire « AMINA-02 » n’émet plus depuis 20 min.' },
    { level: 'warning', icon: 'icon-file-text',      title: 'Licence à échéance',     text: '3 licences expirent dans moins de 15 jours.' },
    { level: 'info',    icon: 'icon-map-pin',        title: 'Entrée en zone',         text: 'Un navire est entré en zone portuaire d’Abidjan.' },
    { level: 'success', icon: 'icon-check-circle',   title: 'Contrôle validé',        text: 'Le contrôle n° CT-2026-0148 a été validé.' },
    { level: 'danger',  icon: 'icon-alert-triangle', title: 'Zone interdite',         text: 'Franchissement de zone interdite détecté.' },
];

let index = 0;
let unread = 0;

function container() {
    let box = document.querySelector('.gs-toasts');

    if (!box) {
        box = document.createElement('div');
        box.className = 'gs-toasts';
        document.body.appendChild(box);
    }

    return box;
}

function updateBeacon() {
    document.querySelectorAll('[data-alert-count]').forEach((el) => {
        el.textContent = unread > 99 ? '99+' : String(unread);
    });
    document.querySelectorAll('[data-alert-beacon]').forEach((el) => {
        el.classList.toggle('has-alerts', unread > 0);
    });
}

function dismiss(toast) {
    toast.classList.add('is-leaving');
    setTimeout(() => toast.remove(), 300);
}

function showToast({ level, icon, title, text }) {
    const toast = document.createElement('div');
    toast.className = `gs-toast is-${level}`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <span class="gs-toast-icon"><i class="feather ${icon}"></i></span>
        <div class="gs-toast-body"><strong></strong><span></span></div>
        <button type="button" class="gs-toast-close" aria-label="Fermer"><i class="feather icon-x"></i></button>`;
    toast.querySelector('strong').textContent = title;          // textContent : pas d'injection HTML
    toast.querySelector('.gs-toast-body span').textContent = text;
    toast.querySelector('.gs-toast-close').addEventListener('click', () => dismiss(toast));

    container().prepend(toast);
    setTimeout(() => dismiss(toast), DISPLAY_MS);
}

function showNext() {
    showToast(ALERTS[index % ALERTS.length]);
    index += 1;
    unread += 1;
    updateBeacon();
}

export function initAlertsDemo() {
    const beacon = document.querySelector('[data-alert-beacon]');

    if (!beacon) {
        return;
    }

    beacon.addEventListener('click', (event) => {
        event.preventDefault();
        unread = 0;           // un clic sur la balise remet le compteur à zéro
        updateBeacon();
    });

    setTimeout(() => {
        showNext();
        setInterval(showNext, INTERVAL_MS);
    }, FIRST_DELAY_MS);
}
