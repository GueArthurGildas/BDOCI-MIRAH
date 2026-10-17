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

export function initVerticalMenu() {
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
