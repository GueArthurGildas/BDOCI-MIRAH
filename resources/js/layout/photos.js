import { Carousel, Modal } from 'bootstrap';

export function initPhotos() {
    document.querySelectorAll('[data-gs-carousel]').forEach((element) => new Carousel(element, { ride: false, wrap: true }));

    const viewer = document.getElementById('gs-photo-viewer');

    if (!viewer) {
        return;
    }

    const modal = new Modal(viewer);
    const image = viewer.querySelector('.gs-photo-large');
    const caption = viewer.querySelector('[data-gs-photo-caption]');

    document.addEventListener('click', (event) => {
        const photo = event.target.closest('[data-gs-photo]');

        if (!photo) {
            return;
        }

        image.src = photo.src;
        caption.textContent = photo.dataset.gsPhotoLabel ?? '';
        modal.show();
    });
}
