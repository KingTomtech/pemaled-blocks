// Show loading state during AJAX calls
document.addEventListener("DOMContentLoaded", () => {
  const loadingOverlay = document.querySelector(".loading-overlay");

  // Show/hide loading state
  document.addEventListener(
    "ajaxStart",
    () => (loadingOverlay.style.display = "grid")
  );
  document.addEventListener(
    "ajaxEnd",
    () => (loadingOverlay.style.display = "none")
  );

  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map((t) => new bootstrap.Tooltip(t));
});
