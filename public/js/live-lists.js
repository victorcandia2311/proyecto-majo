document.addEventListener('DOMContentLoaded', () => {
    const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es-PE').trim();
    const searchUpdates = new WeakMap();

    const initializeSearch = (region) => {
        const input = region.querySelector('[data-list-search-input]');
        const suggestions = region.querySelector('.list-search-suggestions');
        const emptyRow = region.querySelector('[data-search-empty]');
        const resultCount = region.querySelector('[data-search-count]');
        const dateFrom = region.querySelector('[data-date-filter-start]');
        const dateTo = region.querySelector('[data-date-filter-end]');
        const dateFilterForm = region.querySelector('[data-date-filter-form]');

        if (!input || !suggestions) return;

        let activeSuggestion = -1;
        const hideSuggestions = () => {
            suggestions.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            activeSuggestion = -1;
        };
        const applySuggestion = (button) => {
            input.value = button.dataset.value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.focus();
            hideSuggestions();
        };
        const update = () => {
            const rows = [...region.querySelectorAll('[data-search-row]')];
            const query = normalize(input.value);
            const validDateRange = !dateFrom?.value || !dateTo?.value || dateFrom.value <= dateTo.value;
            const matchedRows = rows.filter((row) => {
                const date = row.dataset.searchDate || '';
                const matchesQuery = !query || normalize(row.dataset.searchValue || row.textContent).includes(query);
                const matchesDate = validDateRange
                    && (!dateFrom?.value || date >= dateFrom.value)
                    && (!dateTo?.value || date <= dateTo.value);

                return matchesQuery && matchesDate;
            });

            rows.forEach((row) => { row.hidden = !matchedRows.includes(row); });
            if (emptyRow) emptyRow.hidden = matchedRows.length !== 0 || rows.length === 0;
            if (resultCount) resultCount.textContent = `${matchedRows.length} ${matchedRows.length === 1 ? 'coincidencia' : 'coincidencias'}`;

            suggestions.replaceChildren();
            if (!query || matchedRows.length === 0) {
                hideSuggestions();
                return;
            }

            matchedRows.slice(0, 6).forEach((row, index) => {
                const item = document.createElement('li');
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'list-search-suggestion';
                button.role = 'option';
                button.dataset.value = row.dataset.suggestionLabel || row.dataset.searchValue || row.textContent.trim();
                button.textContent = button.dataset.value;
                button.setAttribute('aria-selected', String(index === activeSuggestion));
                button.addEventListener('click', () => applySuggestion(button));
                item.append(button);
                suggestions.append(item);
            });

            suggestions.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        };

        searchUpdates.set(region, update);
        input.addEventListener('input', update);
        input.addEventListener('focus', update);
        dateFrom?.addEventListener('input', update);
        dateTo?.addEventListener('input', update);
        dateFilterForm?.querySelectorAll('input[type="date"]').forEach((dateInput) => {
            dateInput.addEventListener('change', () => dateFilterForm.requestSubmit());
        });
        input.addEventListener('keydown', (event) => {
            const options = [...suggestions.querySelectorAll('[role="option"]')];
            if (event.key === 'Escape') {
                hideSuggestions();
            } else if (event.key === 'ArrowDown' && options.length) {
                event.preventDefault();
                activeSuggestion = (activeSuggestion + 1) % options.length;
                options.forEach((option, index) => option.setAttribute('aria-selected', String(index === activeSuggestion)));
            } else if (event.key === 'ArrowUp' && options.length) {
                event.preventDefault();
                activeSuggestion = (activeSuggestion - 1 + options.length) % options.length;
                options.forEach((option, index) => option.setAttribute('aria-selected', String(index === activeSuggestion)));
            } else if (event.key === 'Enter' && activeSuggestion >= 0 && options[activeSuggestion]) {
                event.preventDefault();
                applySuggestion(options[activeSuggestion]);
            }
        });
        input.addEventListener('blur', () => window.setTimeout(hideSuggestions, 120));
        update();
    };

    document.querySelectorAll('[data-live-region]').forEach(initializeSearch);

    if (!document.body.hasAttribute('data-live-lists')) return;

    const paginationRegion = document.querySelector('[data-async-pagination]')?.closest('[data-live-region]');
    if (paginationRegion) {
        history.replaceState(
            { ...history.state, asyncListRegion: paginationRegion.dataset.liveRegion },
            '',
            window.location.href,
        );
    }

    let refreshing = false;
    const paginationRequests = new WeakSet();
    const replacePaginationContent = async (region, url, updateHistory = false) => {
        if (paginationRequests.has(region)) return;

        paginationRequests.add(region);
        region.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok || response.redirected) {
                throw new Error(`La solicitud de paginación falló (${response.status}).`);
            }

            const documentText = await response.text();
            const refreshedDocument = new DOMParser().parseFromString(documentText, 'text/html');
            const regionKey = region.dataset.liveRegion;
            const refreshedRegion = [...refreshedDocument.querySelectorAll('[data-live-region]')]
                .find((candidate) => candidate.dataset.liveRegion === regionKey);
            const currentTable = region.querySelector('.table-responsive');
            const refreshedTable = refreshedRegion?.querySelector('.table-responsive');
            if (!currentTable || !refreshedTable) {
                throw new Error(`No se encontró la tabla "${regionKey}" en la respuesta.`);
            }

            const currentPagination = region.querySelector('.list-pagination');
            const refreshedPagination = refreshedRegion.querySelector('.list-pagination');
            currentTable.replaceWith(document.importNode(refreshedTable, true));
            if (refreshedPagination) {
                const replacementPagination = document.importNode(refreshedPagination, true);
                if (currentPagination) {
                    currentPagination.replaceWith(replacementPagination);
                } else {
                    region.querySelector('.table-responsive').after(replacementPagination);
                }
            } else {
                currentPagination?.remove();
            }

            if (updateHistory) {
                history.pushState({ ...history.state, asyncListRegion: regionKey }, '', url);
            }
            searchUpdates.get(region)?.();
        } catch (error) {
            console.warn('No se pudo cambiar la página de la tabla.', error);
        } finally {
            region.removeAttribute('aria-busy');
            paginationRequests.delete(region);
        }
    };

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;

        const link = event.target.closest('[data-async-pagination] a');
        if (!link || event.defaultPrevented || event.button !== 0
            || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const region = link.closest('[data-live-region]');
        const url = new URL(link.href, window.location.href);
        if (!region || url.origin !== window.location.origin) return;

        event.preventDefault();
        replacePaginationContent(region, url, true);
    });

    window.addEventListener('popstate', (event) => {
        const regionKey = event.state?.asyncListRegion;
        if (!regionKey) return;

        const region = [...document.querySelectorAll('[data-live-region]')]
            .find((candidate) => candidate.dataset.liveRegion === regionKey);
        if (region) {
            replacePaginationContent(region, window.location.href);
        }
    });

    const refreshLists = async () => {
        if (refreshing || document.hidden) return;
        const regions = [...document.querySelectorAll('[data-live-region]')];
        if (!regions.length) return;

        refreshing = true;
        const requestedUrl = window.location.href;
        try {
            const response = await fetch(requestedUrl, {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok || response.redirected || window.location.href !== requestedUrl) return;

            const documentText = await response.text();
            const refreshedDocument = new DOMParser().parseFromString(documentText, 'text/html');
            if (window.location.href !== requestedUrl) return;

            const currentDashboardData = document.querySelector('#dashboard-chart-data');
            const refreshedDashboardData = refreshedDocument.querySelector('#dashboard-chart-data');
            if (currentDashboardData && refreshedDashboardData
                && currentDashboardData.textContent !== refreshedDashboardData.textContent) {
                const data = JSON.parse(refreshedDashboardData.textContent);
                currentDashboardData.textContent = refreshedDashboardData.textContent;
                window.renderMajooDashboard?.(data);
            }

            regions.forEach((region) => {
                if (region.contains(document.activeElement)) return;

                const key = region.dataset.liveRegion;
                const refreshedRegion = [...refreshedDocument.querySelectorAll('[data-live-region]')]
                    .find((candidate) => candidate.dataset.liveRegion === key);
                if (!refreshedRegion) return;

                const currentRows = region.querySelector('tbody');
                const refreshedRows = refreshedRegion.querySelector('tbody');
                if (currentRows && refreshedRows) {
                    if (currentRows.innerHTML === refreshedRows.innerHTML) return;
                } else if (region.innerHTML === refreshedRegion.innerHTML) {
                    return;
                }

                const query = region.querySelector('[data-list-search-input]')?.value ?? '';
                const dateFrom = region.querySelector('[data-date-filter-start]')?.value ?? '';
                const dateTo = region.querySelector('[data-date-filter-end]')?.value ?? '';
                const replacement = document.importNode(refreshedRegion, true);
                const replacementInput = replacement.querySelector('[data-list-search-input]');
                const replacementDateFrom = replacement.querySelector('[data-date-filter-start]');
                const replacementDateTo = replacement.querySelector('[data-date-filter-end]');
                if (replacementInput) replacementInput.value = query;
                if (replacementDateFrom) replacementDateFrom.value = dateFrom;
                if (replacementDateTo) replacementDateTo.value = dateTo;
                region.replaceWith(replacement);
                initializeSearch(replacement);
            });
        } catch (error) {
            console.warn('No se pudieron actualizar las listas automáticamente.', error);
        } finally {
            refreshing = false;
        }
    };

    window.setInterval(refreshLists, 5000);
});