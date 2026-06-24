document.addEventListener("DOMContentLoaded", () => {
  if (!window.Chart) return;

  Chart.defaults.color = "#8f9bb3";
  Chart.defaults.borderColor = "rgba(255,255,255,0.08)";
  Chart.defaults.font.family = "Inter, system-ui, sans-serif";

  const winRateCanvas = document.getElementById("winRateChart");
  if (winRateCanvas) {
    new Chart(winRateCanvas, {
      type: "doughnut",
      data: {
        labels: ["Wins", "Losses"],
        datasets: [{
          data: [68, 32],
          backgroundColor: ["#38bdf8", "#8b5cf6"],
          borderWidth: 0,
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "72%",
        plugins: {
          legend: { position: "bottom", labels: { usePointStyle: true, padding: 18 } }
        }
      }
    });
  }

  const activityCanvas = document.getElementById("activityChart");
  if (activityCanvas) {
    new Chart(activityCanvas, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        datasets: [{
          label: "Tournament Activity",
          data: [8, 13, 10, 18, 22, 27],
          borderColor: "#38bdf8",
          backgroundColor: "rgba(56, 189, 248, 0.16)",
          fill: true,
          tension: 0.42,
          pointBackgroundColor: "#8b5cf6",
          pointBorderWidth: 0,
          pointRadius: 5
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, grid: { color: "rgba(255,255,255,0.07)" } },
          x: { grid: { display: false } }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  }
});
