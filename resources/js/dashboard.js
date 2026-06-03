document.addEventListener('DOMContentLoaded', function () {
    // --- Grafik Penjualan ---
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: window.salesChartLabels || [],
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: window.salesChartData || [],
                    backgroundColor: 'rgba(99, 102, 241, 0.6)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val => 'Rp ' + val.toLocaleString('id-ID')
                        }
                    }
                }
            }
        });
    }

    // --- Toggle custom date range ---
    const btnCustom  = document.getElementById('btn-custom');
    const customForm = document.getElementById('custom-form');
    if (btnCustom && customForm) {
        btnCustom.addEventListener('click', function () {
            const isHidden = customForm.classList.contains('hidden');
            customForm.classList.toggle('hidden', !isHidden);
            customForm.classList.toggle('flex', isHidden);
        });
    }
});
