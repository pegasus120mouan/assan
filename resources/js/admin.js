import Chart from 'chart.js/auto';

const accent = '#22d3ee';
const night = '#0b1220';

function lineChart(elementId, labels, values) {
    const canvas = document.getElementById(elementId);

    if (! canvas) {
        return;
    }

    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'CA (FCFA)',
                    data: values,
                    borderColor: accent,
                    backgroundColor: 'rgba(34, 211, 238, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                },
            ],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => new Intl.NumberFormat('fr-FR').format(value),
                    },
                },
            },
        },
    });
}

function doughnutChart(elementId, labels, values) {
    const canvas = document.getElementById(elementId);

    if (! canvas) {
        return;
    }

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [
                {
                    data: values,
                    backgroundColor: ['#22d3ee', '#22c55e', '#6366f1', '#f59e0b', '#f43f5e', '#94a3b8', '#0ea5e9'],
                    borderWidth: 0,
                },
            ],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
        },
    });
}

function barChart(elementId, labels, values) {
    const canvas = document.getElementById(elementId);

    if (! canvas) {
        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Ventes',
                    data: values,
                    backgroundColor: night,
                    borderRadius: 8,
                },
            ],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } },
        },
    });
}

window.createAdminCharts = function (payload) {
    lineChart('revenue-chart', payload.daily.labels, payload.daily.values);
    doughnutChart('status-chart', payload.statuses.labels, payload.statuses.values);
    barChart('category-chart', payload.categories.labels, payload.categories.values);
};
