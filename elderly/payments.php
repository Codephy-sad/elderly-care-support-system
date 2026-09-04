<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);

// get invoices with payment totals
$invoices = [];
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT i.*,
               COALESCE(SUM(p.amount), 0) as total_paid
        FROM invoices i
        LEFT JOIN payments p ON p.invoice_id = i.id
        WHERE i.elderly_profile_id = ?
        GROUP BY i.id
        ORDER BY i.created_at DESC
    ");
    $stmt->execute([$profile_id]);
    $invoices = $stmt->fetchAll();
}

$pageTitle = 'Payments & Invoices';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>My Payments</h1>
        <p class="text-muted mb-0">View your invoices and payment history</p>
    </div>

    <?php if (empty($invoices)): ?>
        <div class="elderly-empty">
            <p>No invoices found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($invoices as $inv): ?>
            <div class="elderly-card elderly-card--highlight">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong><?php echo sanitize($inv['invoice_number']); ?></strong>
                        <br><small class="text-muted"><?php echo sanitize($inv['description'] ?? ''); ?></small>
                    </div>
                    <span class="status-badge status-<?php echo sanitize($inv['status']); ?>">
                        <?php echo sanitize(ucfirst($inv['status'])); ?>
                    </span>
                </div>
                <div class="row mt-3">
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Amount</small>
                        <strong>৳<?php echo number_format($inv['amount'], 2); ?></strong>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Paid</small>
                        <strong>৳<?php echo number_format($inv['total_paid'], 2); ?></strong>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">Due Date</small>
                        <strong><?php echo $inv['due_date'] ? date('M j, Y', strtotime($inv['due_date'])) : '—'; ?></strong>
                    </div>
                </div>

                <?php
                    // show payment history for this invoice
                    $stmt2 = $pdo->prepare("SELECT * FROM payments WHERE invoice_id = ? ORDER BY paid_at DESC");
                    $stmt2->execute([$inv['id']]);
                    $payments = $stmt2->fetchAll();
                ?>
                <?php if (!empty($payments)): ?>
                    <hr>
                    <small class="text-muted fw-bold">Payment History:</small>
                    <ul class="list-unstyled mt-1 mb-0">
                        <?php foreach ($payments as $pay): ?>
                            <li class="mb-1">
                                <small>
                                    ৳<?php echo number_format($pay['amount'], 2); ?>
                                    — <?php echo sanitize($pay['payment_method'] ?? 'N/A'); ?>
                                    on <?php echo date('M j, Y', strtotime($pay['paid_at'])); ?>
                                    <?php if ($pay['notes']): ?>
                                        (<?php echo sanitize($pay['notes']); ?>)
                                    <?php endif; ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
