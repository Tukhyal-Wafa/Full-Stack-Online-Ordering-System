document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.querySelector(".nav-toggle");
  const nav = document.getElementById("site-nav");
  if (!toggle || !nav) return;

  toggle.addEventListener("click", function () {
    const isOpen = nav.classList.toggle("open");
    toggle.setAttribute("aria-expanded", String(isOpen));
  });

  document.addEventListener("click", function (event) {
    if (
      !nav.contains(event.target) &&
      !toggle.contains(event.target) &&
      nav.classList.contains("open")
    ) {
      nav.classList.remove("open");
      toggle.setAttribute("aria-expanded", "false");
    }
  });
});
