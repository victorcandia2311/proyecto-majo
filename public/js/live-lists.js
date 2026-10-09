document.addEventListener('DOMContentLoaded', () => {
    const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es-PE').trim();

    const initializeSearch = (region) => {
        const input = region.querySelector('[data-list-search-input]');
        const suggestions = region.querySelector('.list-search-suggestions');
        const rows = [...region.querySelectorAll('[data-search-row]')];
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

    let refreshing = false;
    const refreshLists = async () => {
        if (refreshing || document.hidden) return;
        const regions = [...document.querySelectorAll('[data-live-region]')];
        if (!regions.length) return;

        refreshing = true;
        try {
            const response = await fetch(window.location.href, {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok || response.redirected) return;

            const documentText = await response.text();
            const refreshedDocument = new DOMParser().parseFromString(documentText, 'text/html');

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