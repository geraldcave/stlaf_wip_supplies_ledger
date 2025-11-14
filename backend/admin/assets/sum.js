const labels = stats.map(item => item.item_name ?? item.description ?? "Item");
const data = stats.map(item => parseInt(item.total_qty_out ?? item.qty_out ?? 0));

const chartCanvas = document.getElementById("stockOutChart");

const barThickness = 30; // same as dataset
const barSpacing = 1;   // space between bars
chartCanvas.height = labels.length * (barThickness + barSpacing);

const ctx = chartCanvas.getContext("2d");

Chart.register(ChartDataLabels);

new Chart(ctx, {
    type: "bar",
    data: {
        labels: labels,
        datasets: [
            {
                label: "Total Quantity Out",
                data: data,
                backgroundColor: "rgba(18, 55, 101, 0.7)",
                borderColor: "rgba(18, 55, 101, 1)",
                borderWidth: 1,
                barThickness: barThickness,
                datalabels: {
                    anchor: "end",
                    align: "right",
                    color: "#000",
                    formatter: v => v,
                    offset: 10,
                    font: {
                        size: 14,
                        weight: "bold"
                    }
                }
            }
        ]
    },
    options: {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: { right: 40 }
        },
        plugins: {
            legend: { display: false },
            tooltip: { enabled: true }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: { stepSize: 0 }
            },
            y: {
                ticks: { autoSkip: false }
            }
        }
    }
});
