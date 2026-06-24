document.addEventListener("DOMContentLoaded", () => {
  const toastEl = document.querySelector(".toast");
  if (toastEl && window.bootstrap) {
    const toast = new bootstrap.Toast(toastEl, { delay: 3200 });
    setTimeout(() => toast.show(), 600);
  }

  document.querySelectorAll("[data-counter]").forEach((item) => {
    const target = Number(item.dataset.counter || "0");
    const suffix = item.dataset.suffix || "";
    const duration = 900;
    const start = performance.now();

    function tick(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      item.textContent = `${Math.round(target * eased).toLocaleString()}${suffix}`;
      if (progress < 1) requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
  });
});
