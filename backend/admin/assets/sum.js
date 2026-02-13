// 1. Map labels and data from the filtered stats variable
const labels = stats.map(item => item.item_name ?? "Item");
const data = stats.map(item => parseInt(item.total_qty_out ?? 0));

const chartCanvas = document.getElementById("stockOutChart");
const ctx = chartCanvas.getContext("2d");

// 2. Dynamic Height Calculation (prevents chart from looking squashed)
const barHeight = 25;
const barSpacing = 15;
// Set a minimum height of 400px so it looks good even with 1 item
const calculatedHeight = Math.max(400, labels.length * (barHeight + barSpacing));

chartCanvas.style.height = calculatedHeight + "px";
chartCanvas.height = calculatedHeight;

// 3. Prevent Chart Overlap: Check if a chart already exists and destroy it
let existingChart = Chart.getChart("stockOutChart");
if (existingChart) {
    existingChart.destroy();
}

// 4. Create the New Chart
Chart.register(ChartDataLabels);

new Chart(ctx, {
    type: "bar",
    data: {
        labels: labels,
        datasets: [{
            label: "Total Quantity Out",
            data: data,
            backgroundColor: "rgba(18, 55, 101, 0.7)",
            borderColor: "rgba(18, 55, 101, 1)",
            borderWidth: 1,
            barThickness: barHeight
        }]
    },
    options: {
        indexAxis: "y", 
        responsive: true, // Set to true now that we manually handle canvas height
        maintainAspectRatio: false,
        layout: { padding: { right: 50 } },
        plugins: { 
            legend: { display: false }, 
            tooltip: { enabled: true },
            datalabels: {
                anchor: "end",
                align: "right",
                color: "#333",
                offset: 10,
                font: { size: 12, weight: "bold" },
                formatter: (value) => value
            }
        },
        scales: {
            x: { beginAtZero: true },
            y: { ticks: { autoSkip: false } }
        }
    }
});