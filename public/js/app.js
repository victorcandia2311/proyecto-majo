document.addEventListener('DOMContentLoaded', () => {
    let customSelectId = 0;

    document.querySelectorAll('select.form-select:not([multiple])').forEach((select) => {
        customSelectId += 1;

        const wrapper = document.createElement('div');
        const trigger = document.createElement('button');
        const value = document.createElement('span');
        const caret = document.createElement('span');
        const listbox = document.createElement('div');
        const listboxId = `custom-select-options-${customSelectId}`;
        const optionElements = [];
        const labels = Array.from(select.labels);
        let activeIndex = -1;
        let isOpen = false;
        let searchText = '';
        let searchTimeout;

        wrapper.className = 'custom-select';
        trigger.type = 'button';
        trigger.className = 'custom-select-trigger';
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', listboxId);
        trigger.setAttribute('aria-autocomplete', 'none');
        value.className = 'custom-select-value';
        caret.className = 'custom-select-caret';
        caret.setAttribute('aria-hidden', 'true');
        trigger.append(value, caret);

        listbox.id = listboxId;
        listbox.className = 'custom-select-menu';
        listbox.setAttribute('role', 'listbox');
        listbox.hidden = true;

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

        Array.from(select.options).forEach((option, index) => {
            if (option.hidden) {
                return;
            }

            const optionElement = document.createElement('div');
            optionElement.id = `${listboxId}-option-${index}`;
            optionElement.className = 'custom-select-option';
            optionElement.dataset.optionIndex = String(index);
            optionElement.setAttribute('role', 'option');
            optionElement.textContent = option.label;
            optionElement.title = option.label;
            listbox.append(optionElement);
            optionElements.push({ element: optionElement, option, index });
        });

        select.parentNode.insertBefore(wrapper, select);
        wrapper.append(trigger, select);
        document.body.append(listbox);

        select.classList.add('custom-select-native');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        const enabledOptions = () => optionElements.filter(({ option }) => !option.matches(':disabled'));
        const selectedOptionIndex = () => optionElements.findIndex(({ option }) => option.selected && !option.disabled);

        const syncSelection = () => {
            const selectedOption = select.selectedOptions[0];
            value.textContent = selectedOption ? selectedOption.label : 'Seleccionar opción';
            trigger.disabled = select.matches(':disabled');
            trigger.setAttribute('aria-required', String(select.required));

            optionElements.forEach(({ element, option }) => {
                element.setAttribute('aria-selected', String(option.selected));
                element.setAttribute('aria-disabled', String(option.matches(':disabled')));
            });

            if (select.validity.valid) {
                trigger.removeAttribute('aria-invalid');
            }
        };

        const updateActiveOption = (index) => {
            const selectableOptions = enabledOptions();
            const nextOption = selectableOptions.find((item) => item.index === index) || selectableOptions[0];

            if (!nextOption) {
                activeIndex = -1;
                trigger.removeAttribute('aria-activedescendant');
                return;
            }

            activeIndex = nextOption.index;
            trigger.setAttribute('aria-activedescendant', nextOption.element.id);
            optionElements.forEach(({ element, index: optionIndex }) => {
                element.toggleAttribute('data-active', optionIndex === activeIndex);
            });
            nextOption.element.scrollIntoView({ block: 'nearest' });
        };

        const positionListbox = () => {
            const bounds = trigger.getBoundingClientRect();
            const viewportPadding = 8;
            const gap = 4;
            const desiredHeight = Math.min(optionElements.length * 40 + 10, 130);
            const spaceBelow = window.innerHeight - bounds.bottom - viewportPadding - gap;
            const spaceAbove = bounds.top - viewportPadding - gap;
            const openAbove = spaceBelow < desiredHeight && spaceAbove > spaceBelow;
            const availableHeight = Math.max(0, openAbove ? spaceAbove : spaceBelow);
            const menuHeight = Math.min(desiredHeight, availableHeight);
            const menuWidth = Math.min(bounds.width, window.innerWidth - viewportPadding * 2);

            listbox.style.maxHeight = `${menuHeight}px`;
            listbox.style.width = `${menuWidth}px`;
            listbox.style.left = `${Math.max(viewportPadding, Math.min(bounds.left, window.innerWidth - menuWidth - viewportPadding))}px`;
            listbox.style.top = `${openAbove ? Math.max(viewportPadding, bounds.top - menuHeight - gap) : bounds.bottom + gap}px`;
        };

        const closeListbox = () => {
            isOpen = false;
            listbox.hidden = true;
            wrapper.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.removeAttribute('aria-activedescendant');
        };

        const openListbox = () => {
            if (trigger.disabled || isOpen) {
                return;
            }

            isOpen = true;
            listbox.hidden = false;
            wrapper.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            updateActiveOption(selectedOptionIndex());
            positionListbox();
        };

        const chooseOption = (index) => {
            const optionEntry = optionElements.find((entry) => entry.index === index);

            if (!optionEntry || optionEntry.option.matches(':disabled') || trigger.disabled) {
                return;
            }

            const selectionChanged = select.selectedIndex !== index;
            select.selectedIndex = index;
            syncSelection();
            closeListbox();
            trigger.focus();

            if (selectionChanged) {
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };

        syncSelection();

        trigger.addEventListener('click', () => {
            if (isOpen) {
                closeListbox();
            } else {
                openListbox();
            }
        });
        trigger.addEventListener('blur', closeListbox);

        trigger.addEventListener('keydown', (event) => {
            const selectableOptions = enabledOptions();

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();

                if (!isOpen) {
                    openListbox();
                    if (event.key === 'ArrowUp' && selectedOptionIndex() < 0 && selectableOptions.length > 0) {
                        updateActiveOption(selectableOptions[selectableOptions.length - 1].index);
                    }
                    return;
                }

                const currentPosition = selectableOptions.findIndex(({ index }) => index === activeIndex);
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                const nextPosition = (currentPosition + direction + selectableOptions.length) % selectableOptions.length;

                if (selectableOptions.length > 0) {
                    updateActiveOption(selectableOptions[nextPosition].index);
                }
                return;
            }

            if (!isOpen) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closeListbox();
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                chooseOption(activeIndex);
            } else if (event.key === 'Home' || event.key === 'End') {
                event.preventDefault();
                const option = event.key === 'Home' ? selectableOptions[0] : selectableOptions[selectableOptions.length - 1];
                if (option) {
                    updateActiveOption(option.index);
                }
            } else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
                searchText += event.key.toLocaleLowerCase();
                window.clearTimeout(searchTimeout);
                searchTimeout = window.setTimeout(() => {
                    searchText = '';
                }, 500);
                const match = selectableOptions.find(({ option }) => option.label.toLocaleLowerCase().startsWith(searchText));
                if (match) {
                    updateActiveOption(match.index);
                }
            }
        });

        listbox.addEventListener('click', (event) => {
            const optionElement = event.target.closest('[data-option-index]');

            if (optionElement && listbox.contains(optionElement)) {
                chooseOption(Number(optionElement.dataset.optionIndex));
            }
        });

        select.addEventListener('change', syncSelection);
        select.addEventListener('invalid', (event) => {
            event.preventDefault();
            trigger.setAttribute('aria-invalid', 'true');
            openListbox();
            trigger.focus();
        });
        select.form?.addEventListener('reset', () => {
            window.setTimeout(syncSelection);
        });

        labels.forEach((label) => {
            label.addEventListener('click', (event) => {
                event.preventDefault();
                trigger.focus();
            });
        });

        document.addEventListener('pointerdown', (event) => {
            if (isOpen && !wrapper.contains(event.target) && !listbox.contains(event.target)) {
                closeListbox();
            }
        });
        document.addEventListener('scroll', () => {
            if (isOpen) {
                positionListbox();
            }
        }, true);
        window.addEventListener('resize', () => {
            if (isOpen) {
                positionListbox();
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

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
});
