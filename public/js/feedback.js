window.appFeedbackReady = new Promise((resolve) => {
    document.addEventListener('DOMContentLoaded', async () => {
        const feedbackElement = document.getElementById('app-feedback-data');
        const feedback = feedbackElement ? JSON.parse(feedbackElement.textContent) : null;

        document.addEventListener('submit', (event) => {
            if (event.defaultPrevented || !(event.target instanceof HTMLFormElement)) {
                return;
            }

            const form = event.target;
            const submitButton = event.submitter instanceof HTMLButtonElement
                ? event.submitter
                : form.querySelector('button[type="submit"]');

            if (!submitButton || submitButton.disabled) {
                return;
            }

            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            submitButton.innerHTML = submitButton.classList.contains('btn-icon')
                ? '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
                : '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Procesando…';
        });

        if (feedback?.errors?.length) {
            await Swal.fire({
                icon: 'error',
                title: 'Revisa el formulario',
                text: feedback.errors.join(' '),
                confirmButtonText: 'Entendido',
            });
        } else if (feedback?.sale) {
            const { isConfirmed } = await Swal.fire({
                icon: 'success',
                title: '¡Venta registrada!',
                text: `Venta #${feedback.sale.id} registrada correctamente.`,
                showCancelButton: true,
                confirmButtonText: 'Descargar recibo',
                cancelButtonText: 'Continuar',
                reverseButtons: true,
            });

            if (isConfirmed) {
                window.location.assign(feedback.sale.receiptUrl);
            }
        } else if (feedback?.success) {
            await Swal.fire({
                icon: 'success',
                title: feedback.success,
                showConfirmButton: false,
                timer: 2400,
                timerProgressBar: true,
            });
        }

        resolve();
    });
});
