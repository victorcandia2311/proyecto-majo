document.addEventListener('DOMContentLoaded', () => {
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
