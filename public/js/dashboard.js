document.addEventListener('DOMContentLoaded', () => {
    const data = window.majooDashboard;
    if (!data || typeof Chart === 'undefined') {
        document.querySelectorAll('details').forEach((details) => { details.open = true; });
        return;
    }

    const monthLabel = (month) => {
        const [year, number] = month.split('-').map(Number);
        return new Intl.DateTimeFormat('es-PE', { month: 'short', year: 'numeric' })
            .format(new Date(year, number - 1, 1));
    };

    const chart = (id, type, labels, values, label, color) => {
        const canvas = document.getElementById(id);
        if (!canvas || !values.length) return;

        new Chart(canvas, {
            type,
            data: { labels, datasets: [{ label, data: values, backgroundColor: color, borderColor: color, tension: 0.3 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: type === 'doughnut' } },
                ...(type === 'doughnut' ? {} : {
                    scales: { y: { beginAtZero: true, ...(id === 'graficoProductosVendidos' ? { ticks: { precision: 0 } } : {}) } }
                })
            }
        });
    };

    chart('graficoVentas', 'bar', data.ventas.map((item) => monthLabel(item.mes)),
        data.ventas.map((item) => Number(item.total)), 'Ventas (S/)', '#2563eb');
    chart('graficoProductosVendidos', 'line', data.productos.map((item) => monthLabel(item.mes)),
        data.productos.map((item) => Number(item.unidades_vendidas)), 'Unidades vendidas', '#16a34a');
    chart('graficoMetodos', 'doughnut', data.metodos.map((item) => item.metodo_pago),
        data.metodos.map((item) => Number(item.total)), 'Ventas (S/)', ['#2563eb', '#16a34a', '#f59e0b', '#7c3aed']);
});
