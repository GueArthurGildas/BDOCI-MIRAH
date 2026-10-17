const $ = window.jQuery;

const verticalMenuOptions = {
    themelayout: 'vertical',
    verticalMenuplacement: 'left',
    verticalMenulayout: 'wide',
    MenuTrigger: 'click',
    SubMenuTrigger: 'click',
    activeMenuClass: 'active',
    ThemeBackgroundPattern: 'pattern4',
    HeaderBackground: 'theme1',
    LHeaderBackground: 'theme1',
    NavbarBackground: 'theme1',
    ActiveItemBackground: 'theme1',
    SubItemBackground: 'theme2',
    ActiveItemStyle: 'style0',
    ItemBorder: true,
    ItemBorderStyle: 'none',
    SubItemBorder: true,
    DropDownIconStyle: 'style1',
    menutype: 'st6',
    freamtype: 'theme1',
    layouttype: 'light',
    FixedNavbarPosition: true,
    FixedHeaderPosition: true,
    collapseVerticalLeftHeader: true,
    VerticalSubMenuItemIconStyle: 'style1',
    VerticalNavigationView: 'view1',
    verticalMenueffect: {
        desktop: 'shrink',
        tablet: 'overlay',
        phone: 'overlay',
    },
    defaultVerticalMenu: {
        desktop: 'expanded',
        tablet: 'offcanvas',
        phone: 'offcanvas',
    },
    onToggleVerticalMenu: {
        desktop: 'offcanvas',
        tablet: 'expanded',
        phone: 'expanded',
    },
};

function initVerticalMenu() {
    const $pcoded = $('#pcoded');

    if (!$pcoded.length) {
        return;
    }

    $pcoded.pcodedmenu(verticalMenuOptions);

    $('#mobile-collapse i').addClass('icon-toggle-right');
    $('#mobile-collapse').on('click', function () {
        $(this).find('i').toggleClass('icon-toggle-right icon-toggle-left');
    });

    $('.mobile-options').on('click', () => {
        $('.navbar-container .nav-right').slideToggle('slow');
    });
}

function initCardActions() {
    $('.card-header-right .close-card').on('click', function () {
        const $card = $(this).parents('.card');

        $card.animate({ opacity: 0 });
        setTimeout(() => $card.remove(), 800);
    });

    $('.card-header-right .minimize-card').on('click', function () {
        $(this).parents('.card').children('.card-body').slideToggle();
        $(this).toggleClass('icon-minus icon-plus');
    });

    $('.card-header-right .full-card').on('click', function () {
        $(this).parents('.card').toggleClass('full-card');
        $(this).toggleClass('icon-maximize icon-minimize');
    });
}

function initFullScreenToggle() {
    $('[data-toggle-fullscreen]').on('click', (event) => {
        event.preventDefault();

        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            document.documentElement.requestFullscreen();
        }
    });

    $(document).on('fullscreenchange', () => {
        $('.full-screen').toggleClass('icon-maximize icon-minimize');
    });
}

function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        new window.bootstrap.Tooltip(element);
    });
}

function hidePreloader() {
    $('.theme-loader').fadeOut('slow', function () {
        $(this).remove();
    });
}

$(() => {
    initVerticalMenu();
    initCardActions();
    initFullScreenToggle();
    initTooltips();
    hidePreloader();
});
