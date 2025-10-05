

import RequestHelper from "../../helpers/request_helper";
import AbstractComponent from "/../abstract_component.js";
import __i from "../../vendor/repeat-toolkit/i18n.js";

import {route} from "../../helpers/router-provider.js";


class DataTable extends AbstractComponent {
    constructor() {
        super();
        Object.assign(this.state, {
            apiUrl: this.getAttribute('src'),
            user_id: this.getAttribute('user_id') ?? "",
            table_name: this.getAttribute('table_name') ?? "",
            columns: [],
            data: [],
            total: 0,
            page: 1,
            perPage: parseInt(this.getAttribute('per-page')) || 5,
            search: '',
            sortKey: '',
            sortDir: 'asc',
            maxVisibleCols: 0,
            init: 1,
        });
        this.resizeObserver = null;
        this._dropdownInitDone = false; // guard da ne dupliramo globalne osluškivače
    }

    connectedCallback() {
        if (!this.state.apiUrl) {
            this.innerHTML = `<p style="color:red">Missing src attribute</p>`;
            return;
        }

        this.innerHTML = `
      <style>
        @media (max-width: 768px) {
          data-table table thead th:not(:first-child),
          data-table table tbody td:not(:first-child):not(.toggle-cell),
          data-table table tbody td.hidden-col {
            display: none !important;
          }

          data-table table tbody tr.expanded + tr.details-row {
            display: table-row;
          }
        }

        .details-row td {
          padding: 0;
          background: transparent !important;
          border-top: none;
        }

        .details-content {
          padding: 10px;
          display: flex;
          flex-direction: column;
          font-size: 0.9em;
        }

        .details-content div {
          margin-bottom: 5px;
        }

        /* Framework-agnostic dropdown */
        .dropdown { position: relative; display: inline-block; }
        .dropdown-toggle { display: inline-flex; align-items: center; gap: .35rem; }
        .dropdown-menu {
          position: absolute; inset: auto auto auto 0;
          min-width: 10rem; margin-top: .25rem; padding: .25rem 0;
          background: #fff; border: 1px solid rgba(0,0,0,.1); border-radius: .5rem;
          box-shadow: 0 10px 25px rgba(0,0,0,.08); display: none; z-index: 1000;
        }
        .dropdown-menu.show { display: block; }
        .dropdown-item {
          display: block; width: 100%; padding: .5rem .75rem; text-decoration: none;
          color: inherit; background: transparent; border: 0; cursor: pointer;
        }
        .dropdown-item:hover, .dropdown-item:focus { background: #f5f5f5; outline: none; }
      </style>
      <div class="toolbar mb-2">
        <div class="row">
            <div class="col-6">
              <input type="text" class="form-control search" placeholder="${__i("Pretraga")}" />
            </div>
            <div class="col-6 d-none">
              <span class="page-info "></span>
            </div>
            <div class="col-6 text-end">
              <a href="${route(this.state.table_name + ".create_partial", {'slug': "basic"})}" class="btn btn-primary">
                <i class="fa fa-plus"></i> ${__i("Dodaj")}
              </a>
            </div>
        </div>
      </div>
      <div class="table-container">
        <div class="loading">Loading...</div>
      </div>
      <div class="pagination"></div>
      <div class="block mt-2 total_count"></div>
    `;

        this.container = this.querySelector('.table-container');
        this.pageInfo = this.querySelector('.page-info');
        this.querySelector('input').addEventListener('input', e => {
            this.state.search = e.target.value;
            this.state.page = 1;
            this.fetchData();
        });

        this.fetchData();

        this.resizeObserver = new ResizeObserver(() => this.renderTable());
        this.resizeObserver.observe(this);
    }

    disconnectedCallback() {
        if (this.resizeObserver) this.resizeObserver.disconnect();
    }

    fetchData() {
        let data = {'filters': {}, 'order_by': {}, 'limit': this.state.perPage, 'offset': this.state.page};

        if (['auctions'].includes(this.state.table_name)) {
            data['filters']['filter__user_id__equal__motorcycle'] = this.state.user_id;
        } else {
            if (this.state.user_id != "")
                data['filters']['filter__user_id__equal'] = this.state.user_id;
        }

        if (this.state.search !== "")
            data['autocomplete'] = this.state.search;

        const prevHeight = this.container.offsetHeight;
        this.container.style.minHeight = prevHeight + 'px';
        this.container.innerHTML = `<div class="loading">Loading...</div>`;

        RequestHelper.makeRequest("POST", this.state.apiUrl + '?page=' + this.state.page, data).then(response => {
            try {
                this.state.columns = response.columns || [];
                this.state.data = response.data || [];
                this.state.total = response.meta.total || 0;
                this.state.init = 0;
                this.renderTable();
                this.renderPagination();
                this.attachListeners();
                this.querySelector('.block').textContent = `${__i("Ukupno")}: ${this.state.total}`;
            } catch (e) {
                this.container.innerHTML = `<p style="color:red">Failed to load data.</p>`;
            }
        });
    }

    renderActionsCell(actions) {
        const dropdownId = `dd-${Math.random().toString(36).slice(2, 9)}`;

        const itemsHtml = actions.map(action => {
            const method = (action.method || 'GET').toUpperCase();
            const needsConfirm = action.confirm ? 'true' : 'false';
            return `
            <li role="none">
              <a href="${action.url}"
                 class="dropdown-item"
                 role="menuitem"
                 tabindex="-1"
                 data-method="${method}"
                 data-confirm="${needsConfirm}">
                ${action.label}
              </a>
            </li>`;
        }).join('');

        return `
        <div class="dropdown" data-dropdown>
          <button
            type="button"
            class="btn btn-primary dropdown-toggle"
            aria-haspopup="true"
            aria-expanded="false"
            aria-controls="${dropdownId}"
            data-dropdown-toggle
          >
            ${__i("Akcije")}
            <span aria-hidden="true">▾</span>
          </button>
          <ul class="dropdown-menu" id="${dropdownId}" role="menu">
            ${itemsHtml}
          </ul>
        </div>
      `;
    }

    attachListeners() {
        const pagination = this.querySelector('.pagination');
        if (!pagination) return;

        pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();

                const text = link.textContent.trim();
                const totalPages = Math.ceil(this.state.total / this.state.perPage);

                if (text === __i("Prethodna")) {
                    if (this.state.page > 1) {
                        this.state.page--;
                        this.fetchData();
                    }
                } else if (text === __i("Sledeća")) {
                    if (this.state.page < totalPages) {
                        this.state.page++;
                        this.fetchData();
                    }
                } else if (!isNaN(parseInt(text))) {
                    const page = parseInt(text);
                    if (page !== this.state.page) {
                        this.state.page = page;
                        this.fetchData();
                    }
                }
            });
        });
    }

    renderTable() {
        const colWidth = 150;
        const maxCols = Math.max(1, Math.floor(this.offsetWidth / colWidth));
        this.state.maxVisibleCols = maxCols;

        const visibleCols = this.state.columns.slice(0, maxCols - 1);
        const hiddenCols = this.state.columns.slice(maxCols - 1);

        const table = document.createElement('table');
        table.classList.add('table', "border", "mb-3");

        const thead = document.createElement('thead');
        const trHead = document.createElement('tr');

        visibleCols.forEach(col => {
            const th = document.createElement('th');
            th.textContent = col.label;
            th.style.cursor = 'pointer';
            th.classList.add('bg-body-secondary');

            if(col.key == 'actions')
                th.classList.add('text-end');

            th.addEventListener('click', () => this.sort(col.key));
            trHead.appendChild(th);
        });

        const toggleTh = document.createElement('th');
        toggleTh.textContent = '';
        toggleTh.className = 'd-sm-none';
        trHead.appendChild(toggleTh);
        thead.appendChild(trHead);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');

        this.state.data.forEach(row => {
            const tr = document.createElement('tr');

            visibleCols.forEach(col => {
                const td = document.createElement('td');

                if (col.key === 'actions') {
                    td.classList.add('text-end');
                    td.innerHTML = this.renderActionsCell(row[col.key]);
                } else {
                    td.innerHTML = row[col.key] ?? '';
                }
                tr.appendChild(td);
            });

            const toggle = document.createElement('td');
            toggle.textContent = '+';
            toggle.className = 'toggle-cell fs-2 d-sm-none';
            toggle.style.cursor = 'pointer';
            tr.appendChild(toggle);

            tbody.appendChild(tr);

            const detailsRow = document.createElement('tr');
            detailsRow.className = 'details-row';
            detailsRow.style.display = 'none';

            const detailsTd = document.createElement('td');
            detailsTd.colSpan = visibleCols.length + 1;

            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'details-content';

            hiddenCols.forEach(col => {
                if (col.key === 'actions') {
                    const item = document.createElement('div');
                    item.innerHTML = `${this.renderActionsCell(row[col.key])}`;
                    detailsDiv.appendChild(item);
                } else {
                    const item = document.createElement('div');
                    item.innerHTML = `<strong>${col.label}:</strong> ${row[col.key] ?? ''}`;
                    detailsDiv.appendChild(item);
                }
            });

            detailsTd.appendChild(detailsDiv);
            detailsRow.appendChild(detailsTd);
            tbody.appendChild(detailsRow);

            toggle.addEventListener('click', () => {
                const expanded = tr.classList.toggle('expanded');
                toggle.textContent = expanded ? '−' : '+';
                detailsRow.style.display = expanded ? 'table-row' : 'none';
            });
        });

        if (this.state.data.length == 0 && this.state.init == 0) {
            table.innerHTML += `<tr><td colspan="${this.state.columns.length}" class="py-3">${__i("Nema rezultata. Dodajte novi unos klikom na dugme iznad tabele.")}</td></tr>`;
        }

        table.appendChild(tbody);
        this.container.innerHTML = '';
        this.container.appendChild(table);

        // Inicijalizuj vanilla dropdown-e posle rendera
        this.initDropdowns(this.container);

        this.pageInfo.textContent = `Page ${this.state.page} of ${Math.ceil(this.state.total / this.state.perPage)}`;
        this.container.style.minHeight = 'auto';
    }

    renderPagination() {
        const totalPages = Math.ceil(this.state.total / this.state.perPage);
        const paginationContainer = this.querySelector('.pagination');
        paginationContainer.innerHTML = '';

        const ul = document.createElement('ul');
        ul.className = 'pagination';

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${this.state.page === 1 ? 'disabled' : ''}`;
        const prevLink = document.createElement('button');
        prevLink.className = 'page-link';
        prevLink.textContent = __i("Prethodna");
        prevLi.appendChild(prevLink);
        ul.appendChild(prevLi);

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === this.state.page ? 'active' : ''}`;
            const a = document.createElement('button');
            a.className = 'page-link';
            a.textContent = i;
            li.appendChild(a);
            ul.appendChild(li);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${this.state.page === totalPages ? 'disabled' : ''}`;
        const nextLink = document.createElement('button');
        nextLink.className = 'page-link';
        nextLink.textContent = __i("Sledeća");
        nextLi.appendChild(nextLink);
        ul.appendChild(nextLi);

        paginationContainer.appendChild(ul);
    }

    sort(key) {
        if (this.state.sortKey === key) {
            this.state.sortDir = this.state.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.state.sortKey = key;
            this.state.sortDir = 'asc';
        }
        this.fetchData();
    }

    // ===== Vanilla dropdown =====
    initDropdowns(rootEl) {
        // Zatvori sva otvorena
        const closeAll = () => {
            rootEl.querySelectorAll('.dropdown-menu.show').forEach(m => {
                m.classList.remove('show');
                const btn = m.closest('[data-dropdown]')?.querySelector('[data-dropdown-toggle]');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
        };

        // Delegirani klikovi unutar container-a
        if (!rootEl._dropdownDelegated) {
            rootEl.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-dropdown-toggle]');
                if (btn) {
                    const dd = btn.closest('[data-dropdown]');
                    const menu = dd?.querySelector('.dropdown-menu');
                    if (!menu) return;

                    const isOpen = menu.classList.contains('show');
                    closeAll();
                    if (!isOpen) {
                        menu.classList.add('show');
                        btn.setAttribute('aria-expanded', 'true');
                        const first = menu.querySelector('.dropdown-item');
                        if (first) first.focus();
                    }
                    e.stopPropagation();
                    return;
                }

                const item = e.target.closest('.dropdown-item');
                if (item) {
                    const needsConfirm = item.getAttribute('data-confirm') === 'true';
                    if (needsConfirm && !confirm(__i('Da li ste sigurni?'))) {
                        e.preventDefault();
                        return;
                    }

                    const method = (item.getAttribute('data-method') || 'GET').toUpperCase();
                    if (method !== 'GET') {
                        e.preventDefault();
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = item.getAttribute('href');
                        form.style.display = 'none';

                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (csrf) {
                            const t = document.createElement('input');
                            t.type = 'hidden';
                            t.name = '_token';
                            t.value = csrf;
                            form.appendChild(t);
                        }
                        const m = document.createElement('input');
                        m.type = 'hidden';
                        m.name = '_method';
                        m.value = method;
                        form.appendChild(m);

                        document.body.appendChild(form);
                        form.submit();
                    }
                    closeAll();
                    return;
                }
            });
            rootEl._dropdownDelegated = true;
        }

        // Globalni osluškivači (dodaj jednom po komponenti)
        if (!this._dropdownInitDone) {
            document.addEventListener('click', (e) => {
                if (!this.contains(e.target)) {
                    // Klik van komponente – zatvori sve
                    this.querySelectorAll('.dropdown-menu.show').forEach(m => {
                        m.classList.remove('show');
                        const btn = m.closest('[data-dropdown]')?.querySelector('[data-dropdown-toggle]');
                        if (btn) btn.setAttribute('aria-expanded', 'false');
                    });
                }
            });

            this.addEventListener('keydown', (e) => {
                const openMenu = this.querySelector('.dropdown-menu.show');
                if (!openMenu) return;

                const items = Array.from(openMenu.querySelectorAll('.dropdown-item'));
                const idx = items.indexOf(document.activeElement);

                if (e.key === 'Escape') {
                    openMenu.classList.remove('show');
                    const btn = openMenu.closest('[data-dropdown]')?.querySelector('[data-dropdown-toggle]');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = items[Math.min(idx + 1, items.length - 1)] || items[0];
                    next?.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = items[Math.max(idx - 1, 0)] || items[items.length - 1];
                    prev?.focus();
                }
            });

            const closeOnViewportChange = () => {
                this.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
                this.querySelectorAll('[data-dropdown-toggle][aria-expanded="true"]').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
            };
            window.addEventListener('scroll', closeOnViewportChange, true);
            window.addEventListener('resize', closeOnViewportChange);

            this._dropdownInitDone = true;
        }
    }
}

customElements.define('data-table', DataTable);

