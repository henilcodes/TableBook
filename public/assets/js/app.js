(function () {
  function showToast(message, type) {
    const level = type || "info";
    const wrapper = document.querySelector(".toast-container") || createToastContainer();
    const toast = document.createElement("div");
    toast.className = "toast show";
    toast.setAttribute("role", "alert");
    const headerClass = level === "success" ? "bg-success" : level === "error" ? "bg-danger" : "bg-primary";
    const title = level === "success" ? "Success" : level === "error" ? "Error" : "Info";
    toast.innerHTML = `
      <div class="toast-header ${headerClass} text-white">
        <strong class="me-auto">${title}</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body">${escapeHtml(message)}</div>
    `;
    wrapper.appendChild(toast);
    setTimeout(function () {
      if (window.bootstrap) {
        const bsToast = new bootstrap.Toast(toast);
        bsToast.hide();
      } else {
        toast.remove();
      }
    }, 4000);
  }

  function createToastContainer() {
    const container = document.createElement("div");
    container.className = "toast-container";
    document.body.appendChild(container);
    return container;
  }

  function setButtonLoading(button, isLoading, loadingText) {
    if (!button) return;
    if (isLoading) {
      button.dataset.originalText = button.innerHTML;
      button.disabled = true;
      const text = loadingText || "Loading";
      button.innerHTML = `<span class=\"spinner-border spinner-border-sm me-2\" role=\"status\" aria-hidden=\"true\"></span>${text}`;
    } else {
      button.disabled = false;
      button.innerHTML = button.dataset.originalText || button.innerHTML;
    }
  }

  function initSessionToasts() {
    if (!window.bootstrap) return;
    document.querySelectorAll(".toast").forEach(function (toastEl) {
      const toast = new bootstrap.Toast(toastEl, { delay: 4200 });
      toast.show();
    });
  }

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  window.TableTapUI = {
    showToast: showToast,
    setButtonLoading: setButtonLoading,
    initSessionToasts: initSessionToasts
  };

  document.addEventListener("DOMContentLoaded", function () {
    initSessionToasts();
  });
})();
