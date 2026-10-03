document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const input = document.getElementById(button.dataset.passwordToggle);
    if (!input) return;

    button.hidden = false;
    button.addEventListener('click', () => {
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        button.querySelector('i').className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
});

document.querySelectorAll('[data-admin-registration]').forEach((form) => {
    const requestStep = form.querySelector('[data-registration-request]');
    const details = form.querySelector('[data-registration-details]');
    const codeStep = form.querySelector('[data-registration-code-step]');
    const code = form.querySelector('#codigo_verificacion');
    const feedback = form.querySelector('[data-registration-feedback]');
    const requestButtons = form.querySelectorAll('[data-request-registration-code]');
    const verifyButton = form.querySelector('[data-verify-registration-code]');
    const detailFields = details.querySelectorAll('input');
    const actionButtons = form.querySelectorAll('[data-request-registration-code], [data-verify-registration-code]');

    const setFeedback = (message, isError = false) => {
        feedback.hidden = false;
        feedback.classList.toggle('auth-error', isError);
        feedback.classList.toggle('auth-success', !isError);
        feedback.setAttribute('role', isError ? 'alert' : 'status');
        feedback.setAttribute('aria-live', isError ? 'assertive' : 'polite');
        feedback.textContent = message;
    };

    const postJson = async (url, values = {}) => {
        const payload = new FormData();
        payload.append('_token', form.querySelector('[name="_token"]').value);
        Object.entries(values).forEach(([name, value]) => payload.append(name, value));

        const response = await fetch(url, {
            method: 'POST',
            body: payload,
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();

        if (!response.ok) {
            throw new Error(Object.values(result.errors ?? {}).flat().join(' ') || 'No se pudo completar la solicitud. Inténtalo de nuevo.');
        }

        return result;
    };

    const setActionsDisabled = (disabled) => {
        actionButtons.forEach((button) => { button.disabled = disabled; });
    };

    requestButtons.forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();
            setActionsDisabled(true);
            setFeedback('Enviando código...');

            try {
                const result = await postJson(form.dataset.codeUrl);
                requestStep.hidden = true;
                codeStep.hidden = false;
                code.disabled = false;
                code.required = true;
                setFeedback(result.status);
                code.focus();
            } catch (error) {
                setFeedback(error.message, true);
            } finally {
                setActionsDisabled(false);
            }
        });
    });

    verifyButton?.addEventListener('click', async (event) => {
        event.preventDefault();
        if (!code.reportValidity()) return;

        setActionsDisabled(true);
        setFeedback('Verificando código...');

        try {
            const result = await postJson(form.dataset.verifyUrl, { codigo_verificacion: code.value });
            codeStep.hidden = true;
            code.disabled = true;
            code.required = false;
            detailFields.forEach((field) => { field.disabled = false; });
            details.hidden = false;
            setFeedback(result.status);
            details.querySelector('input')?.focus();
        } catch (error) {
            setFeedback(error.message, true);
        } finally {
            setActionsDisabled(false);
        }
    });
});
