<?php
ob_start();
?>

<div class="container public-section">
    <div class="row g-4 mb-4 align-items-stretch">
        <div class="col-lg-8">
            <div class="card h-100">
                <img src="<?= htmlspecialchars(image_url($restaurant['image_url'] ?? null)) ?>" class="card-img-top detail-restaurant-image" alt="<?= htmlspecialchars($restaurant['name']) ?>">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <h1 class="mb-2"><?= htmlspecialchars($restaurant['name']) ?></h1>
                            <p class="tt-muted mb-2">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($restaurant['address'] ?? 'Address not available') ?>
                            </p>
                            <p class="tt-muted mb-0">
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($restaurant['phone'] ?? 'Phone not available') ?>
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="badge text-bg-light border"><i class="bi bi-star-fill text-warning"></i> <?= number_format((float)($restaurant['rating'] ?? 0), 1) ?></span>
                            <div class="small tt-muted mt-2"><?= htmlspecialchars($restaurant['cuisine_type'] ?? 'Cuisine') ?></div>
                        </div>
                    </div>
                    <hr>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($restaurant['description'] ?? '')) ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Restaurant Hours</h5>
                    <?php
                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    foreach ($hours as $hour):
                    ?>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span><?= $days[$hour['day_of_week']] ?></span>
                            <span class="tt-muted">
                                <?php if ($hour['is_closed']): ?>
                                    Closed
                                <?php else: ?>
                                    <?= date('g:i A', strtotime($hour['open_time'])) ?> - <?= date('g:i A', strtotime($hour['close_time'])) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="reservation-shell mb-5">
        <div class="reservation-panel p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h4 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Reserve a Table</h4>
                <div class="reservation-steps">
                    <button type="button" class="step-chip active" id="step-chip-1" data-step-nav="1">1. Reservation Details</button>
                    <button type="button" class="step-chip disabled" id="step-chip-2" data-step-nav="2" disabled>2. Select Table</button>
                    <button type="button" class="step-chip disabled" id="step-chip-3" data-step-nav="3" disabled>3. Guest Details</button>
                </div>
            </div>

            <form id="bookingForm" class="needs-validation" novalidate method="POST" action="<?= url('/reservation') ?>">
                <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="restaurant_id" value="<?= $restaurant['id'] ?>">
                <input type="hidden" name="restaurant_slug" value="<?= $restaurant['slug'] ?>">
                <div class="wizard-progress mb-4">
                    <div class="wizard-progress-bar" id="wizardProgressBar" style="width:33.33%"></div>
                </div>

                <section class="wizard-panel border-0 shadow-none p-0" id="wizard-step-1">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Reservation Date</label>
                            <input type="date" name="reservation_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                            <div class="invalid-feedback">Please select a valid date.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Arrival Time</label>
                            <select name="reservation_time" class="form-select" id="timeSlotSelect" required disabled>
                                <option value="">Select Date First...</option>
                            </select>
                            <div class="invalid-feedback">Please select a time.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Number of Guests</label>
                            <select name="party_size" class="form-select" required>
                                <option value="">Select...</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'Guest' : 'Guests' ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="invalid-feedback">Please select party size.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Preferred Seating</label>
                            <select name="preferred_seating" class="form-select">
                                <option value="any">Any Seating</option>
                                <option value="indoor">Indoor</option>
                                <option value="outdoor">Outdoor</option>
                                <option value="window">Window Side</option>
                                <option value="bar">Bar Area</option>
                            </select>
                        </div>
                    </div>
                    <div class="wizard-actions mt-4">
                        <button type="button" id="step1NextBtn" class="btn btn-primary px-5 py-2">
                            <i class="bi bi-search me-2"></i> Find Available Tables
                        </button>
                    </div>
                </section>

                <section class="wizard-panel border-0 shadow-none p-0 tt-hidden" id="wizard-step-2">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0 fw-bold">Select Your Table</h5>
                        <span class="badge badge-premium text-bg-light border text-primary" id="selectedTableBadge">No table selected</span>
                    </div>
                    <div id="availableTables">
                        <div id="tablesList" class="row g-3"></div>
                    </div>
                    <div class="wizard-actions mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="step2BackBtn">
                            <i class="bi bi-chevron-left"></i> Modify Search
                        </button>
                        <button type="button" class="btn btn-primary px-5 py-2" id="step2NextBtn" disabled>
                            Continue to Finalize <i class="bi bi-chevron-right ms-2"></i>
                        </button>
                    </div>
                </section>

                <section class="wizard-panel border-0 shadow-none p-0 tt-hidden" id="wizard-step-3">
                    <h5 class="mb-4 fw-bold">Complete Your Reservation</h5>
                    <?php if (empty($_SESSION['customer_id'])): ?>
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                            <div class="card-body p-4 text-center">
                                <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-person-lock fs-1"></i>
                                </div>
                                <h4 class="fw-bold">Sign In Required</h4>
                                <p class="text-muted mx-auto" style="max-width: 320px;">To ensure a secure booking and process your reservation, please sign in or create an account.</p>
                                <div class="d-grid gap-2 mt-4">
                                    <a href="<?= url('/login?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? ('/restaurants/' . $restaurant['slug']))) ?>" class="btn btn-primary py-3">Login to Book Now</a>
                                    <a href="<?= url('/register') ?>" class="btn btn-link text-decoration-none fw-bold">New here? Create an account</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border border-primary-subtle bg-white rounded-4 mb-4 p-3 d-flex align-items-center shadow-sm">
                            <div class="bg-primary text-white rounded-circle p-3 me-3">
                                <i class="bi bi-person-check-fill fs-4"></i>
                            </div>
                            <div>
                                <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Logged in as</small>
                                <h5 class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['customer_name']) ?></h5>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-uppercase text-muted fw-bold small">Special Requests</label>
                            <textarea name="notes" class="form-control p-3" rows="3" placeholder="Allergies, birthday celebrations, or specific seating preferences..."></textarea>
                            <div class="form-text mt-2 small text-muted">We'll do our best to accommodate your requests.</div>
                        </div>

                        <div class="wizard-actions">
                            <button type="button" class="btn btn-outline-secondary" id="step3BackBtn">
                                <i class="bi bi-chevron-left me-1"></i> Select Another Table
                            </button>
                            <button type="button" class="btn btn-primary px-5 py-2" id="openConfirmModalBtn">
                                <i class="bi bi-check2-circle me-2"></i> Review & Confirm
                            </button>
                        </div>
                    <?php endif; ?>
                </section>
            </form>
        </div>

        <div class="reservation-sidebar">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-list-check me-2"></i>Your Selection</h5>
                    <div class="small tt-muted">Date</div>
                    <div id="summaryDate" class="mb-2">-</div>
                    <div class="small tt-muted">Time</div>
                    <div id="summaryTime" class="mb-2">-</div>
                    <div class="small tt-muted">Party</div>
                    <div id="summaryParty" class="mb-2">-</div>
                    <div class="small tt-muted">Table</div>
                    <div id="summaryTable" class="mb-0">Not selected</div>
                </div>
            </div>

            <div id="preorderCartSummary" class="card d-none">
                <div class="card-body">
                    <h6 class="mb-2"><i class="bi bi-cart3 me-2"></i>Pre-order Cart</h6>
                    <div id="cartItemsList" class="small mb-2"></div>
                    <p class="mb-1"><strong data-cart-count>0</strong> qty total</p>
                    <p class="mb-0 tt-muted">Total: ₹<span data-cart-total>0.00</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><i class="bi bi-menu-button-wide me-2"></i>Menu</h4>
            <small class="tt-muted">Tap + to add to pre-order</small>
        </div>
        <div class="card-body">
            <div id="addedItemsSection" class="added-items-section d-none mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <h5 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Added Items</h5>
                    <small class="tt-muted">Only items currently in your pre-order cart</small>
                </div>
                <div id="addedItemsGrid" class="row g-2"></div>
            </div>

            <?php foreach ($categories as $category): ?>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 mb-3">
                    <div>
                        <h5 class="mb-1"><?= htmlspecialchars($category['name']) ?></h5>
                        <?php if (!empty($category['description'])): ?>
                            <p class="tt-muted mb-0 small"><?= htmlspecialchars($category['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row g-3">
                    <?php foreach ($menuItems[$category['id']] as $item): ?>
                        <?php $qtyInputId = 'qty-item-' . (int)$item['id']; ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="menu-item-card" data-menu-item-id="<?= (int)$item['id'] ?>">
                                <img src="<?= htmlspecialchars(image_url($item['image_url'] ?? null)) ?>" class="menu-item-thumb mb-2" alt="<?= htmlspecialchars($item['name']) ?>">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                        <?php if (!empty($item['description'])): ?>
                                            <p class="text-muted small mb-2"><?= htmlspecialchars($item['description']) ?></p>
                                        <?php endif; ?>
                                        <div class="menu-price"><?= currency($item['price']) ?></div>
                                    </div>
                                    <span class="badge rounded-pill text-bg-success d-none" data-in-cart-badge>In cart: 0</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-3">
                                    <div class="input-group input-group-sm qty-picker">
                                        <button type="button" class="btn btn-outline-secondary" onclick="changeQtyInput('<?= $qtyInputId ?>', -1)" aria-label="Decrease quantity">-</button>
                                        <input id="<?= $qtyInputId ?>" type="number" class="form-control text-center" min="1" max="20" value="1">
                                        <button type="button" class="btn btn-outline-secondary" onclick="changeQtyInput('<?= $qtyInputId ?>', 1)" aria-label="Increase quantity">+</button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary menu-add-btn" data-menu-item-id="<?= (int)$item['id'] ?>" data-default-label="Add to Cart" onclick="addToCart(<?= $item['id'] ?>, <?= $restaurant['id'] ?>, this, '<?= $qtyInputId ?>')">
                                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="reservationConfirmModal" tabindex="-1" aria-labelledby="reservationConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="reservationConfirmModalLabel">
                    <i class="bi bi-calendar-check me-2"></i> Confirm Booking
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3 shadow-sm">
                    <i class="bi bi-clock-fill fs-2"></i>
                </div>
                <h5 class="fw-bold mb-1">Almost There!</h5>
                <p class="text-muted small mb-4">Please review your reservation details below</p>
                
                <div class="card bg-light border-0 rounded-4 mb-4">
                    <div class="card-body p-3">
                        <div class="row g-3 text-start">
                            <div class="col-6">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Date & Time</small>
                                <div class="fw-bold" id="confirmSummaryDateTime">-</div>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Guests</small>
                                <div class="fw-bold" id="confirmSummaryParty">-</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Table</small>
                                <div class="fw-bold" id="confirmSummaryTable">-</div>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Pre-order</small>
                                <div class="fw-bold"><span id="confirmCartQty">0</span> items</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-0 rounded-4 p-3 d-flex align-items-center mb-0">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div class="text-start">
                        <div class="fw-bold small">Instant Confirmation</div>
                        <div class="small" style="font-size: 0.75rem;">Your table will be held immediately upon confirmation.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold" data-bs-dismiss="modal">Go Back</button>
                <button type="button" class="btn btn-primary px-5 py-2 shadow-sm" id="submitReservationBtn">
                    <i class="bi bi-patch-check me-2"></i> Confirm & Book
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
<!-- Proper Error Modal -->
<div class="modal fade" id="serverErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex p-3 mb-3 shadow-sm">
                    <i class="bi bi-x-circle fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Something went wrong</h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
            <div class="modal-footer border-0 p-3 pt-0 justify-content-center">
                <button type="button" class="btn btn-danger px-5 rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const restaurantHours = <?= json_encode($hours) ?>;
let selectedTableId = null;
let selectedTableLabel = 'Not selected';
let currentStep = 1;
let step2Ready = false;
let cartCount = 0;
let cartTotal = 0;
let cartItems = [];

const form = document.getElementById('bookingForm');
const tablesList = document.getElementById('tablesList');
const step1NextBtn = document.getElementById('step1NextBtn');
const step2NextBtn = document.getElementById('step2NextBtn');
const step2BackBtn = document.getElementById('step2BackBtn');
const step3BackBtn = document.getElementById('step3BackBtn');
const wizardProgressBar = document.getElementById('wizardProgressBar');
const openConfirmModalBtn = document.getElementById('openConfirmModalBtn');
const submitReservationBtn = document.getElementById('submitReservationBtn');
const reservationConfirmModalEl = document.getElementById('reservationConfirmModal');
let reservationConfirmModal = null;
window.__TABLETAP_BASE_URL__ = window.__TABLETAP_BASE_URL__ || <?= json_encode(BASE_URL) ?>;

function formatINR(value) {
    return `₹${Number(value || 0).toFixed(2)}`;
}

function resolveImageUrl(path) {
    const baseUrl = String(window.__TABLETAP_BASE_URL__ || '');
    const fallback = `${baseUrl}/public/assets/img/restaurant-placeholder.svg`;
    if (!path) return fallback;
    if (/^(https?:)?\/\//i.test(path) || String(path).startsWith('data:')) return path;
    if (String(path).startsWith('/')) return `${baseUrl}${path}`;
    return `${baseUrl}/${String(path).replace(/^\/+/, '')}`;
}

async function parseApiResponse(response) {
    const contentType = response.headers.get('content-type') || '';
    if (contentType.includes('application/json')) {
        return response.json();
    }
    const text = await response.text();
    return { error: text || 'Unexpected response from server.' };
}

function setStep(step, allowJump = false) {
    if (!allowJump && step === 2 && !step2Ready) {
        return;
    }
    if (!allowJump && step === 3 && !selectedTableId) {
        return;
    }

    currentStep = step;

    [1, 2, 3].forEach(i => {
        const chip = document.getElementById(`step-chip-${i}`);
        const panel = document.getElementById(`wizard-step-${i}`);
        if (panel) panel.classList.toggle('tt-hidden', i !== step);
        if (!chip) return;

        chip.classList.toggle('active', i === step);

        const isDisabled = (i === 2 && !step2Ready) || (i === 3 && !selectedTableId);
        chip.classList.toggle('disabled', isDisabled);
        chip.disabled = isDisabled;
    });

    if (wizardProgressBar) {
        const width = step === 1 ? 33.33 : (step === 2 ? 66.66 : 100);
        wizardProgressBar.style.width = `${width}%`;
    }
}

function updateSummary() {
    const data = new FormData(form);
    document.getElementById('summaryDate').textContent = data.get('reservation_date') || '-';
    document.getElementById('summaryTime').textContent = data.get('reservation_time') || '-';
    document.getElementById('summaryParty').textContent = data.get('party_size') ? `${data.get('party_size')} guests` : '-';
    document.getElementById('summaryTable').textContent = selectedTableLabel;
}

function renderTables(tables) {
    if (!tables.length) {
        tablesList.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">No matching tables available for this slot. Try another time or seating preference.</div></div>';
        selectedTableId = null;
        selectedTableLabel = 'Not selected';
        const tableInput = document.querySelector('input[name="table_id"]');
        if (tableInput) tableInput.value = '';
        step2NextBtn.disabled = true;
        setStep(2, true);
        updateSummary();
        return;
    }

    tablesList.innerHTML = tables.map(table => `
        <div class="col-md-6 col-xl-4">
            <button type="button" class="table-option w-100 text-start" data-table-id="${table.id}" data-table-number="${table.table_number}" data-table-capacity="${table.capacity}" data-table-pref="${table.seating_preference}">
                <div class="d-flex justify-content-between align-items-start">
                    <strong>Table ${table.table_number}</strong>
                    <span class="badge text-bg-light border text-uppercase">${table.seating_preference}</span>
                </div>
                <div class="small tt-muted mt-2"><i class="bi bi-people"></i> Capacity ${table.capacity}</div>
            </button>
        </div>
    `).join('');

    tablesList.querySelectorAll('[data-table-id]').forEach(el => {
        el.addEventListener('click', () => selectTable(el));
    });

    setStep(2, true);
}

function selectTable(buttonEl) {
    selectedTableId = buttonEl.dataset.tableId;
    selectedTableLabel = `Table ${buttonEl.dataset.tableNumber} (${buttonEl.dataset.tableCapacity} seats, ${buttonEl.dataset.tablePref})`;

    tablesList.querySelectorAll('.table-option').forEach(el => el.classList.remove('selected'));
    buttonEl.classList.add('selected');

    let tableInput = document.querySelector('input[name="table_id"]');
    if (!tableInput) {
        tableInput = document.createElement('input');
        tableInput.type = 'hidden';
        tableInput.name = 'table_id';
        form.appendChild(tableInput);
    }
    tableInput.value = selectedTableId;

    step2NextBtn.disabled = false;
    updateSummary();
}

function validateStep1() {
    const data = new FormData(form);
    const date = data.get('reservation_date');
    const time = data.get('reservation_time');
    const partySize = data.get('party_size');

    if (!date || !time || !partySize) {
        TableTapUI.showToast('Please select date, time, and party size.', 'error');
        return false;
    }
    return true;
}

async function checkAvailability() {
    if (!validateStep1()) return;

    const data = new FormData(form);
    const date = data.get('reservation_date');
    const time = data.get('reservation_time');
    const partySize = data.get('party_size');
    const seating = data.get('preferred_seating') || 'any';

    selectedTableId = null;
    selectedTableLabel = 'Not selected';
    step2Ready = false;
    step2NextBtn.disabled = true;
    tablesList.innerHTML = '<div class="col-12"><div class="d-flex align-items-center gap-2"><span class="spinner-border spinner-border-sm"></span> Checking live availability...</div></div>';
    setStep(2, true);
    updateSummary();

    TableTapUI.setButtonLoading(step1NextBtn, true, 'Checking...');

    try {
        const restaurantId = data.get('restaurant_id');
        const response = await fetch(`<?= url('/availability') ?>?restaurant_id=${restaurantId}&date=${date}&time=${time}&party_size=${partySize}`);
        const result = await response.json();

        if (result.error) {
            TableTapUI.showToast(result.error, 'error');
            setStep(1, true);
            return;
        }

        let tables = Array.isArray(result.tables) ? result.tables : [];
        if (seating !== 'any') {
            tables = tables.filter(t => String(t.seating_preference) === seating);
        }

        step2Ready = true;
        renderTables(tables);
    } catch (error) {
        console.error(error);
        TableTapUI.showToast('Failed to check availability. Please try again.', 'error');
        setStep(1, true);
    } finally {
        TableTapUI.setButtonLoading(step1NextBtn, false);
    }
}

async function addToCart(menuItemId, restaurantId, btn, qtyInputId) {
    const qtyInput = document.getElementById(qtyInputId);
    const qty = Math.max(1, parseInt(qtyInput?.value || '1', 10));
    TableTapUI.setButtonLoading(btn, true, 'Adding');
    try {
        const response = await fetch('<?= url('/cart/add') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `menu_item_id=${menuItemId}&restaurant_id=${restaurantId}&quantity=${qty}&_token=<?= $csrf_token ?>`
        });
        const data = await parseApiResponse(response);
        if (!data.success) {
            TableTapUI.showToast(data.error || 'Failed to add item.', 'error');
            return;
        }

        cartCount = data.cart_count;
        cartTotal = data.cart_total;
        cartItems = Array.isArray(data.items) ? data.items : [];
        updateCartSummary();
        TableTapUI.showToast('Added to pre-order cart.', 'success');
    } catch (error) {
        console.error(error);
        TableTapUI.showToast('An error occurred while adding item.', 'error');
    } finally {
        TableTapUI.setButtonLoading(btn, false);
        updateMenuCardStates();
    }
}

function changeQtyInput(inputId, delta) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const current = Math.max(1, parseInt(input.value || '1', 10));
    const next = Math.max(1, Math.min(20, current + delta));
    input.value = String(next);
}

async function updateCartItem(cartItemId, quantity, btn) {
    if (btn) TableTapUI.setButtonLoading(btn, true, '...');
    try {
        const response = await fetch('<?= url('/cart/update') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `cart_item_id=${cartItemId}&quantity=${quantity}&_token=<?= $csrf_token ?>`
        });
        const data = await parseApiResponse(response);
        if (!data.success) {
            TableTapUI.showToast(data.error || 'Failed to update cart.', 'error');
            return;
        }
        cartCount = data.cart_count;
        cartTotal = data.cart_total;
        cartItems = Array.isArray(data.items) ? data.items : [];
        updateCartSummary();
    } catch (error) {
        console.error(error);
        TableTapUI.showToast('Cart update failed.', 'error');
    } finally {
        if (btn) TableTapUI.setButtonLoading(btn, false);
    }
}

function updateCartSummary() {
    const card = document.getElementById('preorderCartSummary');
    const list = document.getElementById('cartItemsList');
    if (!card) return;

    if (cartCount <= 0) {
        card.classList.add('d-none');
        if (list) list.innerHTML = '';
        renderAddedItemsSection();
        updateMenuCardStates();
        return;
    }

    card.classList.remove('d-none');
    card.querySelector('[data-cart-count]').textContent = cartCount;
    card.querySelector('[data-cart-total]').textContent = Number(cartTotal).toFixed(2);

    if (list) {
        list.innerHTML = cartItems.map(item => `
            <div class="cart-item-row">
                <img src="${resolveImageUrl(item.image_url)}" class="cart-item-thumb" alt="${item.item_name}">
                <div class="pe-2 flex-grow-1">
                    <div class="fw-semibold">${item.item_name}</div>
                    <div class="text-muted small">${formatINR(item.price)} each</div>
                </div>
                <div class="d-flex align-items-center gap-1 cart-item-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2" onclick="updateCartItem(${item.id}, ${Math.max(0, item.quantity - 1)}, this)">-</button>
                    <span class="small px-1 fw-bold">${item.quantity}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2" onclick="updateCartItem(${item.id}, ${item.quantity + 1}, this)">+</button>
                </div>
                <div class="small fw-semibold text-end">${formatINR(Number(item.price) * Number(item.quantity))}</div>
            </div>
        `).join('');
    }

    renderAddedItemsSection();
    updateMenuCardStates();
}

function getCartItemMap() {
    const map = {};
    cartItems.forEach(item => {
        map[Number(item.menu_item_id)] = item;
    });
    return map;
}

function updateMenuCardStates() {
    const cartMap = getCartItemMap();

    document.querySelectorAll('.menu-item-card[data-menu-item-id]').forEach(card => {
        const menuItemId = Number(card.dataset.menuItemId);
        const badge = card.querySelector('[data-in-cart-badge]');
        const addBtn = card.querySelector('.menu-add-btn');
        const qtyInput = card.querySelector('input[type="number"]');
        const cartItem = cartMap[menuItemId];

        if (cartItem) {
            card.classList.add('in-cart');
            if (qtyInput) {
                qtyInput.value = String(Math.max(1, Number(cartItem.quantity)));
            }
            if (badge) {
                badge.classList.remove('d-none');
                badge.textContent = `In cart: ${cartItem.quantity}`;
            }
            if (addBtn) {
                addBtn.classList.remove('btn-outline-primary');
                addBtn.classList.add('btn-success');
                addBtn.innerHTML = `<i class="bi bi-check2-circle me-1"></i>Added (${cartItem.quantity})`;
            }
        } else {
            card.classList.remove('in-cart');
            if (qtyInput && (!qtyInput.value || Number(qtyInput.value) < 1)) {
                qtyInput.value = '1';
            }
            if (badge) {
                badge.classList.add('d-none');
                badge.textContent = 'In cart: 0';
            }
            if (addBtn) {
                addBtn.classList.add('btn-outline-primary');
                addBtn.classList.remove('btn-success');
                addBtn.innerHTML = `<i class="bi bi-cart-plus me-1"></i>${addBtn.dataset.defaultLabel || 'Add to Cart'}`;
            }
        }
    });
}

function renderAddedItemsSection() {
    const section = document.getElementById('addedItemsSection');
    const grid = document.getElementById('addedItemsGrid');
    if (!section || !grid) return;

    if (!cartItems.length) {
        section.classList.add('d-none');
        grid.innerHTML = '';
        return;
    }

    section.classList.remove('d-none');
    grid.innerHTML = cartItems.map(item => `
        <div class="col-md-6 col-xl-4">
            <div class="added-item-card">
                <img src="${resolveImageUrl(item.image_url)}" class="added-item-thumb" alt="${item.item_name}">
                <div class="flex-grow-1 pe-2">
                    <div class="fw-semibold">${item.item_name}</div>
                    <div class="small text-muted">${formatINR(item.price)} each</div>
                    <div class="small fw-semibold">${formatINR(Number(item.price) * Number(item.quantity))}</div>
                </div>
                <div class="d-flex align-items-center gap-1 cart-item-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2" onclick="updateCartItem(${item.id}, ${Math.max(0, item.quantity - 1)}, this)">-</button>
                    <span class="small px-1 fw-bold">${item.quantity}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2" onclick="updateCartItem(${item.id}, ${item.quantity + 1}, this)">+</button>
                </div>
            </div>
        </div>
    `).join('');
}

form.addEventListener('change', updateSummary);
form.addEventListener('submit', function(e) {
    if (!selectedTableId) {
        e.preventDefault();
        TableTapUI.showToast('Please select a table before confirming.', 'error');
        setStep(2);
    }
});

const dateInput = document.querySelector('input[name="reservation_date"]');
const timeSelect = document.getElementById('timeSlotSelect');

if (dateInput) {
    dateInput.addEventListener('change', updateTimeSlots);
}

function updateTimeSlots() {
    const dateVal = dateInput.value;
    if (!dateVal) {
        timeSelect.innerHTML = '<option value="">Select Date First...</option>';
        timeSelect.disabled = true;
        return;
    }

    const dateObj = new Date(dateVal);
    const dayOfWeek = dateObj.getDay(); // 0 is Sunday, matches PHP format

    const dayHours = restaurantHours.find(h => parseInt(h.day_of_week) === dayOfWeek);

    timeSelect.innerHTML = '';

    if (!dayHours || dayHours.is_closed) {
        timeSelect.innerHTML = '<option value="">Closed on this date</option>';
        timeSelect.disabled = true;
        return;
    }

    const openTime = dayHours.open_time;
    const closeTime = dayHours.close_time;
    
    if (!openTime || !closeTime) {
        timeSelect.innerHTML = '<option value="">Hours unavailable</option>';
        timeSelect.disabled = true;
        return;
    }

    const [oH, oM] = openTime.split(':').map(Number);
    const [cH, cM] = closeTime.split(':').map(Number);
    let currentMins = oH * 60 + oM;
    const closeMins = cH * 60 + cM;
    const durationMins = 90; // Default buffer duration assumption

    let optionsHtml = '<option value="">Select Time...</option>';
    let hasSlots = false;

    while (currentMins + durationMins <= closeMins) {
        const today = new Date();
        const isToday = today.toISOString().split('T')[0] === dateVal;
        const nowMins = today.getHours() * 60 + today.getMinutes();

        // If today, only show slots that are at least 30 mins in the future
        if (!isToday || currentMins > nowMins + 30) {
            const h = Math.floor(currentMins / 60);
            const m = currentMins % 60;
            const ampm = h >= 12 ? 'PM' : 'AM';
            const displayH = h % 12 || 12;
            const timeStr = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:00`;
            const displayStr = `${displayH}:${m.toString().padStart(2, '0')} ${ampm}`;
            
            optionsHtml += `<option value="${timeStr}">${displayStr}</option>`;
            hasSlots = true;
        }
        currentMins += 30; // 30 minute intervals
    }

    if (!hasSlots) {
        timeSelect.innerHTML = '<option value="">No slots available</option>';
        timeSelect.disabled = true;
    } else {
        timeSelect.innerHTML = optionsHtml;
        timeSelect.disabled = false;
    }
}

step1NextBtn.addEventListener('click', checkAvailability);
step2BackBtn.addEventListener('click', () => setStep(1, true));
step2NextBtn.addEventListener('click', () => {
    if (!selectedTableId) {
        TableTapUI.showToast('Please select a table to continue.', 'error');
        return;
    }
    setStep(3, true);
});
step3BackBtn.addEventListener('click', () => setStep(2, true));
openConfirmModalBtn.addEventListener('click', () => {
    if (!selectedTableId) {
        TableTapUI.showToast('Please select a table before confirming.', 'error');
        setStep(2);
        return;
    }

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        TableTapUI.showToast('Please complete all required fields.', 'error');
        return;
    }

    const data = new FormData(form);
    const dateStr = data.get('reservation_date') || '-';
    const timeStr = data.get('reservation_time') || '-';
    
    document.getElementById('confirmSummaryDateTime').textContent = `${dateStr} @ ${timeStr}`;
    document.getElementById('confirmSummaryParty').textContent = data.get('party_size') ? `${data.get('party_size')} guests` : '-';
    document.getElementById('confirmSummaryTable').textContent = selectedTableLabel;
    document.getElementById('confirmCartQty').textContent = String(cartCount || 0);

    if (!reservationConfirmModal && reservationConfirmModalEl && window.bootstrap) {
        reservationConfirmModal = new bootstrap.Modal(reservationConfirmModalEl);
    }
    if (reservationConfirmModal) {
        reservationConfirmModal.show();
    }
});

submitReservationBtn.addEventListener('click', () => {
    TableTapUI.setButtonLoading(submitReservationBtn, true, 'Confirming Reservation...');
    form.submit();
});

document.querySelectorAll('[data-step-nav]').forEach(chip => {
    chip.addEventListener('click', () => {
        const step = parseInt(chip.dataset.stepNav || '1', 10);
        setStep(step);
    });
});

document.addEventListener('DOMContentLoaded', async () => {
    updateSummary();
    setStep(1, true);
    updateMenuCardStates();
    try {
        const res = await fetch('<?= url('/cart/summary') ?>');
        const data = await res.json();
        if (data.success) {
            cartCount = data.cart_count;
            cartTotal = data.cart_total;
            cartItems = Array.isArray(data.items) ? data.items : [];
            updateCartSummary();
        }
    } catch (_) {}
    
    // Auto trigger time slots if date is pre-filled
    if (dateInput && dateInput.value) {
        updateTimeSlots();
    }
    
    // Show server error modal if present
    const errModalEl = document.getElementById('serverErrorModal');
    if (errModalEl && window.bootstrap) {
        new bootstrap.Modal(errModalEl).show();
    }
});
</script>

<?php
$content = ob_get_clean();
$title = htmlspecialchars($restaurant['name']) . ' - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>
