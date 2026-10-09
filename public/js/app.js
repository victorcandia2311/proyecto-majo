document.addEventListener('DOMContentLoaded', () => {
    const normalizeSelectSearch = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es-PE').trim();
    let customSelectId = 0;

    document.querySelectorAll('select.form-select:not([multiple])').forEach((select) => {
        customSelectId += 1;

        const wrapper = document.createElement('div');
        const trigger = document.createElement('button');
        const value = document.createElement('span');
        const caret = document.createElement('span');
        const menu = document.createElement('div');
        const search = document.createElement('input');
        const optionsList = document.createElement('div');
        const menuId = `custom-select-menu-${customSelectId}`;
        const optionsId = `${menuId}-options`;
        const options = Array.from(select.options).filter((option) => !option.hidden);
        let activeOptionIndex = -1;
        let isOpen = false;

        wrapper.className = 'custom-select';
        trigger.type = 'button';
        trigger.className = 'custom-select-trigger';
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', optionsId);
        value.className = 'custom-select-value';
        caret.className = 'custom-select-caret';
        caret.setAttribute('aria-hidden', 'true');
        trigger.append(value, caret);

        menu.id = menuId;
        menu.className = 'custom-select-menu';
        menu.hidden = true;
        search.type = 'search';
        search.className = 'custom-select-search';
        search.placeholder = 'Escribe para buscar...';
        search.setAttribute('aria-label', 'Buscar opción');
        search.setAttribute('aria-controls', optionsId);
        optionsList.id = optionsId;
        optionsList.className = 'custom-select-options';
        optionsList.setAttribute('role', 'listbox');
        menu.append(search, optionsList);

        const labels = Array.from(select.labels);
        labels.forEach((label, index) => {
            if (!label.id) {
                label.id = `custom-select-label-${customSelectId}-${index + 1}`;
            }
        });
        if (labels.length > 0) {
            trigger.setAttribute('aria-labelledby', labels.map((label) => label.id).join(' '));
        } else {
            trigger.setAttribute('aria-label', select.getAttribute('aria-label') || select.name || 'Seleccionar opción');
        }

        select.parentNode.insertBefore(wrapper, select);
        wrapper.append(trigger, select);
        document.body.append(menu);
        select.classList.add('custom-select-native');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        const visibleOptions = () => Array.from(optionsList.querySelectorAll('[data-option-index]'));
        const positionMenu = () => {
            const bounds = trigger.getBoundingClientRect();
            const padding = 8;
            const gap = 4;
            const menuWidth = Math.min(bounds.width, window.innerWidth - padding * 2);
            const menuHeight = menu.getBoundingClientRect().height;
            const spaceBelow = window.innerHeight - bounds.bottom - padding - gap;
            const spaceAbove = bounds.top - padding - gap;
            const openAbove = spaceBelow < menuHeight && spaceAbove > spaceBelow;

            menu.style.width = `${menuWidth}px`;
            menu.style.left = `${Math.max(padding, Math.min(bounds.left, window.innerWidth - menuWidth - padding))}px`;
            menu.style.top = `${openAbove ? Math.max(padding, bounds.top - menuHeight - gap) : bounds.bottom + gap}px`;
        };
        const updateActiveOption = (optionElement) => {
            visibleOptions().forEach((element) => element.removeAttribute('data-active'));
            if (!optionElement) {
                activeOptionIndex = -1;
                trigger.removeAttribute('aria-activedescendant');
                search.removeAttribute('aria-activedescendant');
                return;
            }

            activeOptionIndex = Number(optionElement.dataset.optionIndex);
            optionElement.setAttribute('data-active', '');
            trigger.setAttribute('aria-activedescendant', optionElement.id);
            search.setAttribute('aria-activedescendant', optionElement.id);
            optionElement.scrollIntoView({ block: 'nearest' });
        };
        const syncSelection = () => {
            const selectedOption = select.selectedOptions[0];
            value.textContent = selectedOption?.label || 'Seleccionar opción';
            trigger.disabled = select.matches(':disabled');
            trigger.setAttribute('aria-required', String(select.required));
            visibleOptions().forEach((element) => {
                const option = options[Number(element.dataset.optionIndex)];
                element.setAttribute('aria-selected', String(option.selected));
                element.setAttribute('aria-disabled', String(option.matches(':disabled')));
            });
            if (select.validity.valid) {
                trigger.removeAttribute('aria-invalid');
            }
        };
        const renderOptions = () => {
            const query = normalizeSelectSearch(search.value);
            const filteredOptions = options
                .map((option, index) => ({ option, index }))
                .filter(({ option }) => normalizeSelectSearch(option.label).includes(query));
            optionsList.replaceChildren();

            filteredOptions.forEach(({ option, index }) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.id = `${menuId}-option-${index}`;
                button.className = 'custom-select-option';
                button.dataset.optionIndex = String(index);
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', String(option.selected));
                button.setAttribute('aria-disabled', String(option.matches(':disabled')));
                button.textContent = option.label;
                button.title = option.label;
                optionsList.append(button);
            });
            if (filteredOptions.length === 0) {
                const empty = document.createElement('p');
                empty.className = 'custom-select-empty';
                empty.textContent = 'No hay opciones que coincidan.';
                optionsList.append(empty);
            }

            const activeElement = filteredOptions.find(({ index }) => index === activeOptionIndex)
                || filteredOptions.find(({ option }) => option.selected && !option.matches(':disabled'))
                || filteredOptions.find(({ option }) => !option.matches(':disabled'));
            updateActiveOption(activeElement ? optionsList.querySelector(`[data-option-index="${activeElement.index}"]`) : null);
            syncSelection();
            positionMenu();
        };
        const closeMenu = (restoreFocus = false) => {
            isOpen = false;
            menu.hidden = true;
            wrapper.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.removeAttribute('aria-activedescendant');
            search.removeAttribute('aria-activedescendant');
            if (restoreFocus) {
                trigger.focus();
            }
        };
        const openMenu = () => {
            if (trigger.disabled || isOpen) {
                return;
            }
            isOpen = true;
            menu.hidden = false;
            wrapper.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            search.value = '';
            renderOptions();
            search.focus();
        };
        const chooseOption = (index) => {
            const option = options[index];
            if (!option || option.matches(':disabled') || trigger.disabled) {
                return;
            }

            const selectionChanged = select.selectedIndex !== index;
            select.selectedIndex = index;
            syncSelection();
            closeMenu(true);
            if (selectionChanged) {
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };

        syncSelection();
        trigger.addEventListener('click', () => {
            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });
        trigger.addEventListener('keydown', (event) => {
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                event.preventDefault();
                openMenu();
            }
        });
        search.addEventListener('input', renderOptions);
        search.addEventListener('keydown', (event) => {
            const renderedOptions = visibleOptions().filter((option) => option.getAttribute('aria-disabled') !== 'true');
            if (event.key === 'Escape') {
                event.preventDefault();
                closeMenu(true);
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const currentPosition = renderedOptions.findIndex((option) => Number(option.dataset.optionIndex) === activeOptionIndex);
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                const nextPosition = (currentPosition + direction + renderedOptions.length) % renderedOptions.length;
                updateActiveOption(renderedOptions[nextPosition] || null);
            } else if (event.key === 'Enter' && activeOptionIndex >= 0) {
                event.preventDefault();
                chooseOption(activeOptionIndex);
            }
        });
        optionsList.addEventListener('pointerdown', (event) => {
            const optionElement = event.target.closest('[data-option-index]');
            if (event.button === 0 && optionElement && optionsList.contains(optionElement)) {
                event.preventDefault();
                chooseOption(Number(optionElement.dataset.optionIndex));
            }
        });
        select.addEventListener('change', () => {
            renderOptions();
            syncSelection();
        });
        select.addEventListener('invalid', (event) => {
            event.preventDefault();
            trigger.setAttribute('aria-invalid', 'true');
            openMenu();
            search.focus();
        });
        select.form?.addEventListener('reset', () => window.setTimeout(() => {
            search.value = '';
            renderOptions();
            syncSelection();
        }));
        labels.forEach((label) => {
            label.addEventListener('click', (event) => {
                event.preventDefault();
                trigger.focus();
            });
        });
        document.addEventListener('pointerdown', (event) => {
            if (isOpen && !wrapper.contains(event.target) && !menu.contains(event.target)) {
                closeMenu();
            }
        });
        document.addEventListener('scroll', () => {
            if (isOpen) {
                positionMenu();
            }
        }, true);
        window.addEventListener('resize', () => {
            if (isOpen) {
                positionMenu();
            }
        });
    });

    const sidebar = document.getElementById('appSidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const mobile = window.matchMedia('(max-width: 991.98px)');
    const content = document.querySelector('.main-content');

    const closeSidebar = (restoreFocus = false) => {
        document.body.classList.remove('sidebar-open');
        toggle?.setAttribute('aria-expanded', 'false');
        if (restoreFocus) toggle?.focus();
        if (sidebar) sidebar.inert = mobile.matches;
        if (content) content.inert = false;
    };

    toggle?.addEventListener('click', () => {
        if (document.body.classList.contains('sidebar-open')) {
            closeSidebar(true);
            return;
        }
        document.body.classList.add('sidebar-open');
        sidebar.inert = false;
        if (content) content.inert = true;
        toggle.setAttribute('aria-expanded', 'true');
        sidebar.querySelector('button, a')?.focus();
    });
    document.querySelectorAll('[data-sidebar-close]').forEach((element) => {
        element.addEventListener('click', () => closeSidebar(true));
    });
    document.addEventListener('keydown', (event) => {
        if (!document.body.classList.contains('sidebar-open')) return;
        if (event.key === 'Escape') closeSidebar(true);
        if (event.key === 'Tab') {
            const controls = sidebar.querySelectorAll('button, a[href]');
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });
    mobile.addEventListener('change', () => closeSidebar());
    closeSidebar();

    const confirmModalElement = document.getElementById('confirm-action-modal');
    const confirmModal = confirmModalElement ? bootstrap.Modal.getOrCreateInstance(confirmModalElement) : null;
    const confirmMessage = confirmModalElement?.querySelector('[data-confirm-message]');
    const confirmButton = confirmModalElement?.querySelector('[data-confirm-submit]');
    let pendingForm = null;
    let pendingSubmitter = null;

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            if (!confirmMessage || !confirmModal) {
                if (!window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
                return;
            }

            event.preventDefault();
            pendingForm = form;
            pendingSubmitter = event.submitter;
            confirmMessage.textContent = form.dataset.confirm;
            confirmModal.show();
        });
    });

    confirmButton?.addEventListener('click', () => {
        if (!pendingForm) {
            return;
        }

        const form = pendingForm;
        const submitter = pendingSubmitter;
        pendingForm = null;
        pendingSubmitter = null;
        form.dataset.confirmed = 'true';
        confirmModal?.hide();

        if (submitter instanceof HTMLElement && submitter.form === form) {
            form.requestSubmit(submitter);
        } else {
            form.requestSubmit();
        }
    });

    confirmModalElement?.addEventListener('hidden.bs.modal', () => {
        pendingForm = null;
        pendingSubmitter = null;
    });
});
