const PADDING = 6;
const GAP = 12;

export function initGuide() {
    const source = document.getElementById('gs-guide-data');
    const triggers = document.querySelectorAll('[data-gs-guide]');

    if (!source || !triggers.length) {
        return;
    }

    const data = JSON.parse(source.textContent);
    let tour = null;

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            start(data);
        });
    });

    function start(guide) {
        stop();

        const steps = [{ title: guide.intro.title, text: guide.intro.text, element: null }]
            .concat(guide.steps
                .map((step) => ({ ...step, element: document.querySelector(step.selector) }))
                .filter((step) => step.element && step.element.getClientRects().length > 0));

        tour = { guide, steps, index: 0, nodes: build(guide.labels) };
        document.addEventListener('keydown', onKey);
        window.addEventListener('resize', place);
        window.addEventListener('scroll', place, true);
        show(0);
    }

    function stop() {
        if (!tour) {
            return;
        }

        tour.nodes.root.remove();
        document.removeEventListener('keydown', onKey);
        window.removeEventListener('resize', place);
        window.removeEventListener('scroll', place, true);
        tour = null;
    }

    function build(labels) {
        const root = document.createElement('div');
        root.className = 'gs-tour';
        root.innerHTML = `
            <div class="gs-tour-spot"></div>
            <div class="gs-tour-pop" role="dialog" aria-live="polite">
                <button type="button" class="gs-tour-close" aria-label="${labels.close}"><i class="feather icon-x"></i></button>
                <h6 class="gs-tour-title"></h6>
                <p class="gs-tour-text"></p>
                <div class="gs-tour-foot">
                    <span class="gs-tour-progress"></span>
                    <span class="gs-tour-buttons">
                        <button type="button" class="btn btn-link btn-sm" data-tour="previous">${labels.previous}</button>
                        <button type="button" class="btn btn-primary btn-sm" data-tour="next"></button>
                    </span>
                </div>
            </div>`;
        document.body.append(root);

        root.addEventListener('click', (event) => {
            const action = event.target.closest('[data-tour]')?.dataset.tour;

            if (action === 'next') {
                tour.index + 1 < tour.steps.length ? show(tour.index + 1) : stop();
            } else if (action === 'previous') {
                show(tour.index - 1);
            } else if (event.target.closest('.gs-tour-close') || event.target === root) {
                stop();
            }
        });

        return {
            root,
            spot: root.querySelector('.gs-tour-spot'),
            pop: root.querySelector('.gs-tour-pop'),
            title: root.querySelector('.gs-tour-title'),
            text: root.querySelector('.gs-tour-text'),
            progress: root.querySelector('.gs-tour-progress'),
            previous: root.querySelector('[data-tour="previous"]'),
            next: root.querySelector('[data-tour="next"]'),
        };
    }

    function show(index) {
        tour.index = Math.max(0, Math.min(index, tour.steps.length - 1));
        const step = tour.steps[tour.index];
        const { nodes, guide, steps } = tour;
        const last = tour.index === steps.length - 1;

        nodes.title.textContent = step.title;
        nodes.text.textContent = step.text;
        nodes.progress.textContent = guide.labels.progress.replace(':current', tour.index + 1).replace(':total', steps.length);
        nodes.previous.hidden = tour.index === 0;
        nodes.next.textContent = last ? guide.labels.done : guide.labels.next;

        if (step.element) {
            step.element.scrollIntoView({ block: 'center', behavior: 'instant' });
        }

        place();
        nodes.next.focus({ preventScroll: true });
    }

    function place() {
        if (!tour) {
            return;
        }

        const { nodes } = tour;
        const step = tour.steps[tour.index];
        const pop = nodes.pop;
        const view = { width: window.innerWidth, height: window.innerHeight };

        if (!step.element) {
            nodes.spot.style.cssText = 'top:50%;left:50%;width:0;height:0';
            pop.style.top = `${Math.max(16, (view.height - pop.offsetHeight) / 2)}px`;
            pop.style.left = `${Math.max(16, (view.width - pop.offsetWidth) / 2)}px`;
            return;
        }

        const rect = step.element.getBoundingClientRect();
        const box = {
            top: Math.max(rect.top - PADDING, 4),
            left: Math.max(rect.left - PADDING, 4),
            width: Math.min(rect.width + PADDING * 2, view.width - 8),
            height: Math.min(rect.height + PADDING * 2, view.height - 8),
        };

        nodes.spot.style.cssText = `top:${box.top}px;left:${box.left}px;width:${box.width}px;height:${box.height}px`;

        const below = view.height - (box.top + box.height);
        const top = below >= pop.offsetHeight + GAP + 8
            ? box.top + box.height + GAP
            : box.top - pop.offsetHeight - GAP >= 8
                ? box.top - pop.offsetHeight - GAP
                : Math.max(8, view.height - pop.offsetHeight - 8);

        pop.style.top = `${top}px`;
        pop.style.left = `${Math.min(Math.max(8, box.left), view.width - pop.offsetWidth - 8)}px`;
    }

    function onKey(event) {
        if (event.key === 'Escape') {
            stop();
        } else if (event.key === 'ArrowRight') {
            tour.index + 1 < tour.steps.length ? show(tour.index + 1) : stop();
        } else if (event.key === 'ArrowLeft') {
            show(tour.index - 1);
        }
    }
}
