<?php
ob_start();
?>
<style>
@media print {
    body { background-color: #fff !important; }
    .navbar, footer, .d-print-none, .btn { display: none !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; margin: 0 !important; }
    .container { max-width: 100% !important; padding: 0 !important; }
    .public-section { padding-top: 0 !important; }
    .shadow-lg, .shadow-sm { box-shadow: none !important; }
}
</style>

<div class="container public-section">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-success-subtle">
                <div class="card-header bg-success text-white text-center">
                    <h3 class="mb-0"><i class="bi bi-check-circle-fill"></i> Reservation Confirmed!</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4>Reservation Code</h4>
                        <h2 class="text-primary mb-1"><?= htmlspecialchars($reservation['reservation_code']) ?></h2>
                        <p class="text-muted">Please save this code for your records</p>
                    </div>
                    
                    <hr>
                    
                    <h5 class="text-uppercase text-muted fw-bold d-print-none mb-3">Reservation Details</h5>
                    <table class="table table-borderless table-sm mb-4">
                        <tr>
                            <th>Restaurant:</th>
                            <td><?= htmlspecialchars($reservation['restaurant_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td><?= date('F j, Y', strtotime($reservation['reservation_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Time:</th>
                            <td><?= date('g:i A', strtotime($reservation['reservation_time'])) ?></td>
                        </tr>
                        <tr>
                            <th>Table:</th>
                            <td><?= htmlspecialchars($reservation['table_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Party Size:</th>
                            <td><?= $reservation['party_size'] ?> <?= $reservation['party_size'] === 1 ? 'Guest' : 'Guests' ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-<?= $reservation['status'] === 'confirmed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($reservation['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php if ($guestDetails): ?>
                            <tr>
                                <th>Guest Name:</th>
                                <td><?= htmlspecialchars($guestDetails['guest_name']) ?></td>
                            </tr>
                            <?php if ($guestDetails['guest_email']): ?>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($guestDetails['guest_email']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($guestDetails['guest_phone']): ?>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?= htmlspecialchars($guestDetails['guest_phone']) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($reservation['notes']): ?>
                            <tr>
                                <th>Special Requests:</th>
                                <td><?= nl2br(htmlspecialchars($reservation['notes'])) ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                    
                    <?php if (!empty($cartItems)): ?>
                        <hr>
                        <h5 class="text-uppercase text-muted fw-bold mb-3">Pre-Order Items</h5>
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                foreach ($cartItems as $item): 
                                    $itemTotal = $item['price'] * $item['quantity'];
                                    $total += $itemTotal;
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= currency($item['price']) ?></td>
                                        <td><?= currency($itemTotal) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <hr class="mt-4 mb-4">
                    <h5 class="text-uppercase text-muted fw-bold mb-3 text-end d-print-none">Payment Summary</h5>
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm text-end align-middle">
                                <?php if (!empty($cartItems)): ?>
                                <tr>
                                    <td>Pre-order Subtotal:</td>
                                    <td width="30%"><?= currency($total) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td>Table Booking Fee:</td>
                                    <td width="30%"><?= currency(10) ?></td>
                                </tr>
                                <tr class="border-top">
                                    <td class="fw-bold fs-5 pt-2">Total Amount Paid:</td>
                                    <td class="fw-bold fs-5 text-primary pt-2"><?= currency(($total ?? 0) + 10) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="text-center mt-5 d-print-none">
                        <button onclick="window.print()" class="btn btn-secondary rounded-pill px-4 me-2 shadow-sm">
                            <i class="bi bi-printer me-2"></i> Print Invoice
                        </button>
                        <a href="<?= url('/') ?>" class="btn btn-primary rounded-pill px-4 shadow-sm me-2">Back to Home</a>
                        <?php if (!empty($_SESSION['customer_id'])): ?>
                            <a href="<?= url('/account/reservations') ?>" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">View My Reservations</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Reservation Confirmed - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>
