const $ = window.jQuery;

export function initCardActions() {
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
