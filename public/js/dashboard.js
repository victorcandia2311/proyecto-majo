document.addEventListener('DOMContentLoaded', () => {
    const dataElement = document.getElementById('dashboard-chart-data');
    const data = dataElement ? JSON.parse(dataElement.textContent) : null;
    const timeElement = document.getElementById('dashboard-clock-time');
    const dateElement = document.getElementById('dashboard-clock-date');
    const peruTime = new Intl.DateTimeFormat('es-PE', {
        timeZone: 'America/Lima', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
    });
    const peruDate = new Intl.DateTimeFormat('es-PE', {
        timeZone: 'America/Lima', weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
    const updateClock = () => {
        const now = new Date();
        if (timeElement) timeElement.textContent = peruTime.format(now);
        if (dateElement) {
            dateElement.textContent = peruDate.format(now);
            dateElement.dateTime = now.toISOString();
        }
    };
    updateClock();
    window.setInterval(updateClock, 1000);

    const renderDashboard = (data) => {
        if (!data || typeof Chart === 'undefined') return;

        ['graficoIngresos', 'graficoMetodos', 'graficoProductos'].forEach((id) => {
            const canvas = document.getElementById(id);
            if (canvas) Chart.getChart(canvas)?.destroy();
        });

        const monthLabel = (month) => {
            const [year, number] = month.split('-').map(Number);
            return new Intl.DateTimeFormat('es-PE', { month: 'short', year: 'numeric' })
                .format(new Date(year, number - 1, 1));
        };
        const currency = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' });
        const months = Array.isArray(data.ventas) ? data.ventas : [];
        const payments = Array.isArray(data.metodos) ? data.metodos : [];
        const products = Array.isArray(data.ranking) ? data.ranking : [];
        const sampleIncome = [1820, 2460, 2180, 3340, 2970, 4120];
        const fallbackMonths = sampleIncome.map((total, index) => {
            const date = new Date();
            date.setDate(1);
            date.setMonth(date.getMonth() - sampleIncome.length + index + 1);

            return { mes: `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`, total };
        });
        const fallbackPayments = [
            { metodo_pago: 'Efectivo', total: 48 }, { metodo_pago: 'Tarjeta', total: 31 },
            { metodo_pago: 'Yape', total: 24 }, { metodo_pago: 'Transferencia', total: 12 },
        ];
        const fallbackProducts = [
            { nombre: 'Café molido', cantidad: 42 }, { nombre: 'Leche entera', cantidad: 35 },
            { nombre: 'Chocolate', cantidad: 28 }, { nombre: 'Té verde', cantidad: 22 },
            { nombre: 'Galletas', cantidad: 16 },
        ];
        const demoSources = { income: months, payments, products };
        document.querySelectorAll('[data-demo-note]').forEach((note) => {
            note.hidden = demoSources[note.dataset.demoNote].length > 0;
        });
        const incomeData = months.length ? months : fallbackMonths;
        const paymentData = payments.length ? payments : fallbackPayments;
        const productData = products.length ? products : fallbackProducts;
        const palette = ['#4f46e5', '#059669', '#0ea5e9', '#f59e0b', '#e11d48', '#14b8a6'];
        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 650 },
            plugins: {
                tooltip: { padding: 11, cornerRadius: 8, displayColors: false },
            },
        };

        const incomeCanvas = document.getElementById('graficoIngresos');
        if (incomeCanvas) {
            const context = incomeCanvas.getContext('2d');
            const gradient = context.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.22)');
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0.015)');
            new Chart(incomeCanvas, {
                type: 'line',
                data: {
                    labels: incomeData.map((item) => monthLabel(item.mes)),
                    datasets: [{
                        label: 'Ingresos', data: incomeData.map((item) => Number(item.total)),
                        borderColor: '#4f46e5', backgroundColor: gradient, fill: true,
                        tension: 0.4, borderWidth: 2.5, pointRadius: 3, pointHoverRadius: 5,
                        pointBackgroundColor: '#fff', pointBorderColor: '#4f46e5', pointBorderWidth: 2,
                    }],
                },
                options: {
                    ...baseOptions,
                    plugins: { ...baseOptions.plugins, legend: { display: false }, tooltip: {
                        ...baseOptions.plugins.tooltip, callbacks: { label: (context) => currency.format(context.parsed.y) },
                    } },
                    scales: {
                        x: { grid: { display: false }, border: { display: false }, ticks: { color: '#8792a2', maxRotation: 0 } },
                        y: { beginAtZero: true, border: { display: false, dash: [4, 4] }, grid: { color: '#edf0f5' }, ticks: {
                            color: '#8792a2', callback: (value) => `S/ ${Number(value).toLocaleString('es-PE')}`,
                        } },
                    },
                },
            });
        }

        const paymentCanvas = document.getElementById('graficoMetodos');
        if (paymentCanvas) {
            new Chart(paymentCanvas, {
                type: 'doughnut',
                data: {
                    labels: paymentData.map((item) => item.metodo_pago),
                    datasets: [{
                        data: paymentData.map((item) => Number(item.total)),
                        backgroundColor: palette,
                        borderColor: '#fff', borderWidth: 4, hoverOffset: 5,
                    }],
                },
                options: {
                    ...baseOptions,
                    cutout: '70%',
                    plugins: {
                        ...baseOptions.plugins,
                        legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', padding: 18, color: '#536174' } },
                        tooltip: { ...baseOptions.plugins.tooltip, callbacks: { label: (context) => `${context.label}: ${currency.format(context.parsed)}` } },
                    },
                },
            });
        }

        const productsCanvas = document.getElementById('graficoProductos');
        if (productsCanvas) {
            new Chart(productsCanvas, {
                type: 'bar',
                data: {
                    labels: productData.map((item) => item.nombre),
                    datasets: [{
                        label: 'Unidades', data: productData.map((item) => Number(item.cantidad)),
                        backgroundColor: '#4f46e5', borderRadius: 5, borderSkipped: false, barThickness: 18,
                    }],
                },
                options: {
                    ...baseOptions,
                    indexAxis: 'y',
                    plugins: { ...baseOptions.plugins, legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, border: { display: false }, grid: { color: '#edf0f5' }, ticks: { precision: 0, color: '#8792a2' } },
                        y: { border: { display: false }, grid: { display: false }, ticks: { color: '#536174', autoSkip: false } },
                    },
                },
            });
        }
    };

    window.renderMajooDashboard = renderDashboard;
    window.renderMajooDashboard(data);
});
