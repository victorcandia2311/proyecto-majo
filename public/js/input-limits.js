document.querySelectorAll('[data-digits-only]').forEach((input) => {
    input.addEventListener('input', () => {
        const maxLength = input.maxLength > 0 ? input.maxLength : undefined;
        const digits = input.value.replace(/\D/g, '').slice(0, maxLength);
        if (input.value !== digits) input.value = digits;
    });
});