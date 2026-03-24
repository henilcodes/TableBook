(function () {
  /**
   * Toast Notification System
   */
  function showToast(message, type = "info") {
    const wrapper = document.querySelector(".toast-container") || createToastContainer();
    const toast = document.createElement("div");
    toast.className = `toast animate-fade-up`;
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");

    const headerClass = type === "success" ? "bg-success" : type === "error" ? "bg-danger" : "bg-dark";
    const icon = type === "success" ? "bi-check-circle-fill" : type === "error" ? "bi-exclamation-triangle-fill" : "bi-info-circle-fill";
    
    toast.innerHTML = `
      <div class="toast-header ${headerClass} border-0 py-2">
        <i class="bi ${icon} me-2"></i>
        <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body p-3">
        ${escapeHtml(message)}
      </div>
    `;
    
    wrapper.appendChild(toast);
    
    if (window.bootstrap) {
      const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 5000 });
      bsToast.show();
      toast.addEventListener('hidden.bs.toast', () => toast.remove());
    } else {
      setTimeout(() => toast.remove(), 5000);
    }
  }

  function createToastContainer() {
    const container = document.createElement("div");
    container.className = "toast-container position-fixed top-0 end-0 p-3";
    container.style.zIndex = "2000";
    document.body.appendChild(container);
    return container;
  }

  /**
   * Button Loading States
   */
  function setButtonLoading(button, isLoading, loadingText = "Processing...") {
    if (!button) return;
    if (isLoading) {
      button.dataset.originalContent = button.innerHTML;
      button.disabled = true;
      button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${loadingText}`;
    } else {
      button.disabled = false;
      button.innerHTML = button.dataset.originalContent || button.innerHTML;
    }
  }

  /**
   * Form Validation
   */
  function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          showToast("Please fix the highlighted errors in the form.", "error");
        }
        form.classList.add('was-validated');
      }, false);
    });
  }

  /**
   * Entrance Animations
   */
  function initAnimations() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px"
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-fade-up');
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    document.querySelectorAll('.card, .hero-section, .section-title').forEach(el => {
      el.style.opacity = "0";
      observer.observe(el);
    });
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  window.TableTapUI = {
    showToast,
    setButtonLoading,
    initFormValidation,
    initAnimations
  };

  document.addEventListener("DOMContentLoaded", function () {
    // Initialize toasts from session if any
    if (window.bootstrap) {
      document.querySelectorAll(".toast").forEach(el => {
        new bootstrap.Toast(el, { delay: 5000 }).show();
      });
    }
    
    initFormValidation();
    initAnimations();
  });
})();
