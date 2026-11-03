import { label, onContent } from './content';

const WINDOW_LIMIT = 7;

export function initTablePagination() {
    onContent((root) => root.querySelectorAll('table[data-gs-paginate]').forEach(paginate));
}

function paginate(table) {
    const size = Number(table.dataset.gsPaginate) || 5;
    const rows = table.tBodies.length ? [...table.tBodies[0].rows] : [];

    if (table.dataset.gsPaginated || rows.length <= size) {
        return;
    }

    table.dataset.gsPaginated = '1';

    const pages = Math.ceil(rows.length / size);
    const pager = document.createElement('div');
    pager.className = 'card-footer gs-table-pager';
    (table.closest('.table-responsive') ?? table).insertAdjacentElement('afterend', pager);

    let page = 1;

    const render = () => {
        rows.forEach((row, index) => {
            row.style.display = Math.floor(index / size) + 1 === page ? '' : 'none';
        });

        const first = (page - 1) * size + 1;
        const last = Math.min(page * size, rows.length);

        pager.innerHTML = '';

        const info = document.createElement('span');
        info.className = 'gs-table-pager-info';
        info.textContent = label('gsPagerRange').replace('{from}', first).replace('{to}', last).replace('{total}', rows.length);
        pager.append(info);

        const list = document.createElement('ul');
        list.className = 'pagination pagination-sm mb-0';
        list.append(item('‹', page - 1, page === 1, label('gsPagerPrevious')));

        if (pages <= WINDOW_LIMIT) {
            for (let number = 1; number <= pages; number += 1) {
                list.append(item(String(number), number, false, null, number === page));
            }
        } else {
            const current = document.createElement('li');
            current.className = 'page-item disabled';
            current.innerHTML = `<span class="page-link">${page} / ${pages}</span>`;
            list.append(current);
        }

        list.append(item('›', page + 1, page === pages, label('gsPagerNext')));
        pager.append(list);
    };

    const item = (text, target, disabled, title, active = false) => {
        const li = document.createElement('li');
        li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'page-link';
        button.textContent = text;

        if (title) {
            button.title = title;
            button.setAttribute('aria-label', title);
        }

        button.addEventListener('click', () => {
            page = target;
            render();
        });

        li.append(button);

        return li;
    };

    render();
}
