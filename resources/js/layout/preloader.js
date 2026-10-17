const $ = window.jQuery;

export function hidePreloader() {
    $('.theme-loader').fadeOut('slow', function () {
        $(this).remove();
    });
}
