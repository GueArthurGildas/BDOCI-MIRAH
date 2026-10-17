export function initFullScreenToggle() {
    document.querySelectorAll('[data-toggle-fullscreen]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();

            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                document.documentElement.requestFullscreen();
            }
        });
    });

    document.addEventListener('fullscreenchange', () => {
        document.querySelectorAll('.full-screen').forEach((icon) => {
            icon.classList.toggle('icon-maximize', !document.fullscreenElement);
            icon.classList.toggle('icon-minimize', Boolean(document.fullscreenElement));
        });
    });
}
