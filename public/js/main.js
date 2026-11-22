/**
 * MultiMart - Frontend Interactivity & AJAX Handlers
 * Academic Major Project: B.Tech CSE / Data Science
 */

document.addEventListener('DOMContentLoaded', function () {
  initSearchAutocomplete();
  initCartHandlers();
  initWishlistHandlers();
  initSimulatedPaymentModal();
  initAlertDismissal();
});

/**
 * Live Search Autocomplete with Debounce
 */
function initSearchAutocomplete() {
  const searchInput = document.getElementById('mainSearchInput');
  const suggestionsBox = document.getElementById('searchSuggestionsBox');
  if (!searchInput || !suggestionsBox) return;

  let debounceTimer;

  searchInput.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    const query = this.value.trim();

    if (query.length < 2) {
      suggestionsBox.style.display = 'none';
      suggestionsBox.innerHTML = '';
      return;
    }

    debounceTimer = setTimeout(() => {
      fetchSuggestions(query, suggestionsBox);
    }, 250);
  });

  // Close when clicking outside
  document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
      suggestionsBox.style.display = 'none';
    }
  });
}

function fetchSuggestions(query, suggestionsBox) {
  // Use relative API path adaptable to subdirectories
  const baseUrl = document.body.dataset.baseUrl || '';
  const apiUrl = `${baseUrl}products/search_api.php?q=${encodeURIComponent(query)}`;

  fetch(apiUrl)
    .then(res => res.json())
    .then(data => {
      if (!data || data.length === 0) {
        suggestionsBox.innerHTML = `
          <div class="p-3 text-muted text-center small">
            <i class="bi bi-search me-1"></i> No matching products or services found for "<strong>${escapeHtml(query)}</strong>"
          </div>`;
        suggestionsBox.style.display = 'block';
        return;
      }

      let html = '<div class="py-1">';
      data.forEach(item => {
        const badgeClass = item.type === 'B2B' ? 'badge-b2b' : (item.type === 'C2C' ? 'badge-c2c' : (item.type === 'C2B' ? 'badge-c2b' : 'badge-b2c'));
        html += `
          <a href="${item.url}" class="search-suggestion-item">
            <div class="me-2 text-primary"><i class="${item.icon || 'bi-box-seam'}"></i></div>
            <div class="flex-grow-1">
              <div class="fw-semibold text-truncate small">${escapeHtml(item.title)}</div>
              <div class="d-flex align-items-center gap-2 mt-1">
                <span class="badge ${badgeClass} text-uppercase" style="font-size: 0.65rem;">${item.type}</span>
                <span class="text-muted small">${item.price_formatted}</span>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted small"></i>
          </a>`;
      });
      html += '</div>';
      suggestionsBox.innerHTML = html;
      suggestionsBox.style.display = 'block';
    })
    .catch(() => {
      suggestionsBox.style.display = 'none';
    });
}

/**
 * Add to Cart & Cart Quantity Handlers
 */
function initCartHandlers() {
  document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const productId = this.dataset.productId;
      const qty = this.dataset.quantity || 1;
      const baseUrl = document.body.dataset.baseUrl || '';

      const originalHtml = this.innerHTML;
      this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Adding...';
      this.disabled = true;

      fetch(`${baseUrl}cart/cart_action.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&product_id=${productId}&quantity=${qty}`
      })
        .then(res => res.json())
        .then(data => {
          this.innerHTML = originalHtml;
          this.disabled = false;
          if (data.success) {
            updateCartBadge(data.cart_count);
            showToast('Success', data.message || 'Product added to your cart!', 'success');
          } else {
            showToast('Notice', data.message || 'Could not add to cart.', 'warning');
          }
        })
        .catch(() => {
          this.innerHTML = originalHtml;
          this.disabled = false;
          showToast('Error', 'Network error occurred while updating cart.', 'danger');
        });
    });
  });
}

/**
 * Wishlist Toggle Handler
 */
function initWishlistHandlers() {
  document.querySelectorAll('.btn-toggle-wishlist').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const productId = this.dataset.productId;
      const baseUrl = document.body.dataset.baseUrl || '';

      fetch(`${baseUrl}customer/wishlist_action.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=toggle&product_id=${productId}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            if (data.in_wishlist) {
              this.classList.add('active');
              this.querySelector('i')?.classList.replace('bi-heart', 'bi-heart-fill');
            } else {
              this.classList.remove('active');
              this.querySelector('i')?.classList.replace('bi-heart-fill', 'bi-heart');
            }
            updateWishlistBadge(data.wishlist_count);
            showToast('Wishlist', data.message, 'info');
          } else {
            showToast('Notice', data.message, 'warning');
          }
        })
        .catch(() => {
          showToast('Error', 'Unable to update wishlist.', 'danger');
        });
    });
  });
}

/**
 * Simulated Payment Gateway Modal Interaction
 */
function initSimulatedPaymentModal() {
  const methodRadios = document.querySelectorAll('input[name="payment_method_choice"]');
  const dynamicFormArea = document.getElementById('paymentMethodDynamicFields');
  if (!methodRadios.length || !dynamicFormArea) return;

  const templates = {
    UPI: `
      <div class="mb-3">
        <label class="form-label small fw-semibold">Virtual Payment Address (VPA / UPI ID)</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-qr-code-scan"></i></span>
          <input type="text" class="form-control" name="sim_upi_id" placeholder="yourname@okhdfcbank" value="student.buyer@okaxis">
        </div>
        <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i> Demo Simulator: Pre-filled test identifier for academic evaluation.</div>
      </div>`,
    Card: `
      <div class="row g-2 mb-3">
        <div class="col-12">
          <label class="form-label small fw-semibold">Simulated Card Number</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
            <input type="text" class="form-control" name="sim_card_num" value="4532 •••• •••• 9821" readonly>
          </div>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Valid Thru</label>
          <input type="text" class="form-control" value="12/28" readonly>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">CVV (Demo)</label>
          <input type="text" class="form-control" value="***" readonly>
        </div>
      </div>`,
    NetBanking: `
      <div class="mb-3">
        <label class="form-label small fw-semibold">Select Simulated Bank</label>
        <select class="form-select" name="sim_bank">
          <option value="HDFC">HDFC Bank (Demo Simulation)</option>
          <option value="SBI">State Bank of India (Demo Simulation)</option>
          <option value="ICICI">ICICI Bank (Demo Simulation)</option>
          <option value="AXIS">Axis Bank (Demo Simulation)</option>
        </select>
      </div>`,
    COD: `
      <div class="alert alert-secondary small mb-3">
        <i class="bi bi-cash-coin me-1"></i> <strong>Cash on Delivery (COD) Selected.</strong> You will pay in cash or via handheld card machine upon product delivery.
      </div>`
  };

  methodRadios.forEach(radio => {
    radio.addEventListener('change', function () {
      const type = this.dataset.type || 'Card';
      dynamicFormArea.innerHTML = templates[type] || templates['Card'];
    });
  });
}

function updateCartBadge(count) {
  const badge = document.getElementById('cartCounterBadge');
  if (badge) {
    badge.innerText = count;
    badge.style.display = count > 0 ? 'inline-block' : 'none';
  }
}

function updateWishlistBadge(count) {
  const badge = document.getElementById('wishlistCounterBadge');
  if (badge) {
    badge.innerText = count;
    badge.style.display = count > 0 ? 'inline-block' : 'none';
  }
}

function showToast(title, message, type = 'info') {
  let toastContainer = document.getElementById('toastContainer');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toastContainer';
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.style.zIndex = '9999';
    document.body.appendChild(toastContainer);
  }

  const bgClass = type === 'success' ? 'text-bg-success' : (type === 'danger' ? 'text-bg-danger' : (type === 'warning' ? 'text-bg-warning' : 'text-bg-primary'));
  const toastId = 'toast_' + Date.now();

  const toastEl = document.createElement('div');
  toastEl.id = toastId;
  toastEl.className = `toast align-items-center ${bgClass} border-0 shadow`;
  toastEl.role = 'alert';
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">
        <strong>${escapeHtml(title)}:</strong> ${escapeHtml(message)}
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  `;

  toastContainer.appendChild(toastEl);
  if (window.bootstrap && window.bootstrap.Toast) {
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
  } else {
    setTimeout(() => toastEl.remove(), 4000);
  }
}

function initAlertDismissal() {
  setTimeout(() => {
    document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
      el.style.transition = 'opacity 0.5s ease';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    });
  }, 5000);
}

function escapeHtml(text) {
  if (!text) return '';
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  return text.toString().replace(/[&<>"']/g, m => map[m]);
}
