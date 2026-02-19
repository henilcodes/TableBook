<?php
ob_start();
?>

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
                    
                    <h5>Reservation Details</h5>
                    <table class="table">
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
                        <h5>Pre-Order</h5>
                        <table class="table">
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
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total:</th>
                                    <th><?= currency($total) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="<?= url('/') ?>" class="btn btn-primary">Back to Home</a>
                        <?php if (!empty($_SESSION['customer_id'])): ?>
                            <a href="<?= url('/account') ?>" class="btn btn-outline-primary">View My Reservations</a>
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
