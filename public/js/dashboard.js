document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.querySelector('[data-dashboard-endpoint]');
    if (!dashboard) return;

    const timeElement = document.getElementById('dashboard-clock-time');
    const dateElement = document.getElementById('dashboard-clock-date');
    const errorElement = document.getElementById('dashboard-error');
    const metricsElement = document.querySelector('[data-dashboard-metrics]');
    const currency = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' });
    const number = new Intl.NumberFormat('es-PE');
    const peruTime = new Intl.DateTimeFormat('es-PE', {
        timeZone: 'America/Lima', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
    });
    const peruDate = new Intl.DateTimeFormat('es-PE', {
        timeZone: 'America/Lima', weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
    let loading = false;

    const updateClock = () => {
        const now = new Date();
        timeElement.textContent = peruTime.format(now);
        dateElement.textContent = peruDate.format(now);
        dateElement.dateTime = now.toISOString();
    };
    updateClock();
    window.setInterval(updateClock, 1000);

    const setValue = (key, value) => {
        const element = document.querySelector(`[data-dashboard-value="${key}"]`);
        element.textContent = value;
    };

    const renderSparkline = (key, values, color) => {
        const svg = document.querySelector(`[data-dashboard-sparkline="${key}"]`);
        svg.replaceChildren();
        if (values.length === 0) return;

        const maximum = Math.max(...values, 1);
        const minimum = Math.min(...values, 0);
        const range = maximum - minimum || 1;
        const points = values.map((value, index) => {
            const x = values.length === 1 ? 60 : 2 + (index * 116 / (values.length - 1));
            const y = 28 - ((value - minimum) / range) * 24;
            return `${x},${y}`;
        }).join(' ');
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
        line.setAttribute('points', points);
        line.setAttribute('fill', 'none');
        line.setAttribute('stroke', color);
        line.setAttribute('stroke-width', '2.5');
        line.setAttribute('stroke-linecap', 'round');
        line.setAttribute('stroke-linejoin', 'round');
        svg.append(line);
    };

    const renderMetrics = (data) => {
        setValue('ingresos', currency.format(data.resumen.ingresos));
        setValue('ventas', number.format(data.resumen.cantidad_ventas));
        setValue('productos', number.format(data.resumen.productos_vendidos));
        setValue('stock', number.format(data.cantidad_stock_bajo));

        const variation = document.querySelector('[data-dashboard-variation]');
        const variationValue = Number(data.variacion.porcentaje);
        variation.className = `metric-foot ${variationValue < 0 ? 'is-negative' : 'is-positive'}`;
        variation.textContent = `${variationValue > 0 ? '+' : ''}${variationValue.toFixed(1)}% vs. mes anterior`;

        const stockLabel = document.querySelector('[data-dashboard-stock-label]');
        const stockCard = document.querySelector('.metric-stock');
        const stockIcon = document.querySelector('[data-stock-icon] i');
        const hasAlerts = data.cantidad_stock_bajo > 0;
        stockLabel.textContent = hasAlerts ? 'Productos por reponer' : 'Inventario al día';
        stockCard.classList.toggle('has-alerts', hasAlerts);
        stockCard.classList.toggle('no-alerts', !hasAlerts);
        stockIcon.className = `bi ${hasAlerts ? 'bi-exclamation-triangle' : 'bi-check-circle'}`;

        const months = data.meses;
        renderSparkline('ingresos', months.map((item) => Number(item.total)), '#4f46e5');
        renderSparkline('ventas', months.map((item) => Number(item.cantidad_ventas)), '#2563eb');
        renderSparkline('productos', months.map((item) => Number(item.unidades_vendidas)), '#059669');
        metricsElement.setAttribute('aria-busy', 'false');
    };

    const renderStock = (items) => {
        const list = document.getElementById('dashboard-stock-list');
        list.replaceChildren();
        if (items.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'text-muted py-3';
            empty.textContent = 'No hay productos con alertas de stock.';
            list.append(empty);
            return;
        }

        const editPattern = list.dataset.productEditPattern;
        items.forEach((item) => {
            const row = document.createElement('li');
            const link = document.createElement('a');
            link.href = editPattern.replace('PRODUCT_ID', encodeURIComponent(item.id));
            const name = document.createElement('span');
            name.textContent = item.nombre;
            const stock = document.createElement('strong');
            stock.textContent = `${number.format(item.stock)} `;
            const unit = document.createElement('small');
            unit.textContent = 'en stock';
            stock.append(unit);
            link.append(name, stock);
            row.append(link);
            list.append(row);
        });
    };

    const renderCharts = (data) => {
        document.querySelectorAll('[data-dashboard-chart]').forEach((container) => {
            container.classList.remove('is-loading');
        });

        if (typeof Chart === 'undefined') {
            document.querySelectorAll('[data-dashboard-empty]').forEach((note) => {
                note.hidden = false;
                note.textContent = 'No se pudo cargar la biblioteca de gráficos.';
            });
            return;
        }

        ['graficoIngresos', 'graficoMetodos', 'graficoProductos'].forEach((id) => {
            const canvas = document.getElementById(id);
            if (canvas) Chart.getChart(canvas)?.destroy();
        });

        const colors = ['#4f46e5', '#059669', '#0ea5e9', '#f59e0b', '#e11d48', '#14b8a6'];
        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 650 },
            plugins: { tooltip: { padding: 11, cornerRadius: 8, displayColors: false } },
        };
        const income = data.meses.filter((item) => Number(item.total) > 0);
        const payments = data.metodos.filter((item) => Number(item.total) > 0);
        const products = data.ranking.filter((item) => Number(item.cantidad) > 0);

        const emptyNotes = [
            ['income', income.length === 0],
            ['payments', payments.length === 0],
            ['products', products.length === 0],
        ];
        emptyNotes.forEach(([key, isEmpty]) => {
            const note = document.querySelector(`[data-dashboard-empty="${key}"]`);
            note.hidden = !isEmpty;
        });

        const incomeCanvas = document.getElementById('graficoIngresos');
        if (income.length > 0) {
            const monthLabel = (month) => {
                const [year, monthNumber] = month.split('-').map(Number);
                return new Intl.DateTimeFormat('es-PE', { month: 'short', year: 'numeric' })
                    .format(new Date(year, monthNumber - 1, 1));
            };
            const context = incomeCanvas.getContext('2d');
            const gradient = context.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.22)');
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0.015)');
            new Chart(incomeCanvas, {
                type: 'line',
                data: {
                    labels: income.map((item) => monthLabel(item.mes)),
                    datasets: [{
                        label: 'Ingresos', data: income.map((item) => Number(item.total)),
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
        if (payments.length > 0) {
            new Chart(paymentCanvas, {
                type: 'doughnut',
                data: {
                    labels: payments.map((item) => item.metodo_pago),
                    datasets: [{
                        data: payments.map((item) => Number(item.total)),
                        backgroundColor: colors, borderColor: '#fff', borderWidth: 4, hoverOffset: 5,
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
        if (products.length > 0) {
            new Chart(productsCanvas, {
                type: 'bar',
                data: {
                    labels: products.map((item) => item.nombre),
                    datasets: [{
                        label: 'Unidades', data: products.map((item) => Number(item.cantidad)),
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

    const loadDashboard = async () => {
        if (loading) return;
        loading = true;
        errorElement.hidden = true;
        metricsElement.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(dashboard.dataset.dashboardEndpoint, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error(`La solicitud respondió ${response.status}.`);

            const data = await response.json();
            renderMetrics(data);
            renderStock(data.stock_bajo);
            renderCharts(data);
        } catch (error) {
            errorElement.hidden = false;
            errorElement.setAttribute('aria-label', error instanceof Error ? error.message : 'Error al cargar los indicadores.');
            metricsElement.setAttribute('aria-busy', 'false');
            ['ingresos', 'ventas', 'productos', 'stock'].forEach((key) => setValue(key, '—'));
            document.querySelectorAll('[data-dashboard-chart]').forEach((container) => container.classList.remove('is-loading'));
            document.querySelectorAll('[data-dashboard-empty]').forEach((note) => {
                note.hidden = false;
                note.textContent = 'No se pudieron cargar los datos del gráfico.';
            });
        } finally {
            loading = false;
        }
    };

    document.querySelector('[data-dashboard-retry]').addEventListener('click', loadDashboard);
    loadDashboard();
    window.setInterval(() => {
        if (!document.hidden) loadDashboard();
    }, 60000);
});
