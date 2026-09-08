<?php
require_once '../config/database.php';
require_once '../config/auth.php';

checkRole(2);

$user_id = (int)$_SESSION['user_id'];
$connections = getFamilyConnections($pdo, $user_id);

if (empty($connections)) {
    header('Location: index.php');
    exit;
}

$selected_elderly_id = isset($_GET['elderly_id']) ? (int)$_GET['elderly_id'] : (int)$connections[0]['elderly_profile_id'];
$active_connection = null;
foreach ($connections as $conn) {
    if ((int)$conn['elderly_profile_id'] === $selected_elderly_id) {
        $active_connection = $conn;
        break;
    }
}
if (!$active_connection) {
    $active_connection = $connections[0];
    $selected_elderly_id = (int)$active_connection['elderly_profile_id'];
}
$elderly_name = $active_connection['elderly_name'];

$success_msg = '';
$error_msg = '';

// Handle Simulated Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay_invoice') {
    $invoice_id = (int)$_POST['invoice_id'];
    
    // Verify invoice belongs to the selected elderly and is unpaid
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ? AND elderly_profile_id = ? AND status IN ('unpaid', 'partial')");
    $stmt->execute([$invoice_id, $selected_elderly_id]);
    $invoice = $stmt->fetch();
    
    if ($invoice) {
        $amount_to_pay = (float)$invoice['amount'];
        
        try {
            $pdo->beginTransaction();
            
            // Insert payment record
            $stmt = $pdo->prepare("INSERT INTO payments (invoice_id, amount, payment_method, notes) VALUES (?, ?, 'credit_card', 'Online Family Portal Payment')");
            $stmt->execute([$invoice_id, $amount_to_pay]);
            
            // Update invoice status
            $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
            $stmt->execute([$invoice_id]);
            
            $pdo->commit();
            $success_msg = "Payment of $" . number_format($amount_to_pay, 2) . " successful. Thank you!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Payment failed. Please try again.";
        }
    } else {
        $error_msg = "Invalid invoice or already paid.";
    }
}

// Fetch Invoices
$stmt = $pdo->prepare("
    SELECT * FROM invoices 
    WHERE elderly_profile_id = ?
    ORDER BY FIELD(status, 'unpaid', 'partial', 'paid', 'cancelled'), due_date ASC
");
$stmt->execute([$selected_elderly_id]);
$invoices = $stmt->fetchAll();

// Calculate total due
$total_due = 0;
foreach ($invoices as $inv) {
    if (in_array($inv['status'], ['unpaid', 'partial'])) {
        $total_due += (float)$inv['amount'];
    }
}

$pageTitle = 'Payments - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Billing & Payments</h1>
        <p class="text-muted mb-0">Account for <?php echo sanitize($elderly_name); ?></p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="index.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary btn-sm">
            &larr; Dashboard
        </a>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success border-0 shadow-sm">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo sanitize($success_msg); ?>
    </div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-danger border-0 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo sanitize($error_msg); ?>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-brand text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50 text-uppercase fw-bold mb-2">Total Balance Due</h6>
                <h2 class="display-5 fw-bold mb-0">$<?php echo number_format($total_due, 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Invoice #</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($invoices) > 0): ?>
                        <?php foreach ($invoices as $inv): ?>
                            <?php
                                $status = $inv['status'];
                                $badge_class = 'bg-secondary';
                                if ($status === 'paid') $badge_class = 'bg-success';
                                elseif ($status === 'unpaid') $badge_class = 'bg-danger';
                                elseif ($status === 'partial') $badge_class = 'bg-warning text-dark';
                                
                                $is_overdue = (in_array($status, ['unpaid', 'partial']) && strtotime($inv['due_date']) < time());
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?php echo sanitize($inv['invoice_number']); ?></td>
                                <td><?php echo sanitize($inv['description'] ?? 'Care Services'); ?></td>
                                <td>
                                    <?php echo date('M j, Y', strtotime($inv['due_date'])); ?>
                                    <?php if ($is_overdue): ?>
                                        <br><span class="badge bg-danger">Overdue</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold">$<?php echo number_format($inv['amount'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?> rounded-pill">
                                        <?php echo ucfirst(sanitize($status)); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if (in_array($status, ['unpaid', 'partial'])): ?>
                                        <button type="button" class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#payModal<?php echo $inv['id']; ?>">
                                            Pay Now
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="alert('Receipt download not implemented in this demo.')">
                                            Receipt
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Payment Modal -->
                            <div class="modal fade" id="payModal<?php echo $inv['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <form method="POST">
                                            <div class="modal-header bg-light border-bottom-0">
                                                <h5 class="modal-title h6 fw-bold">Pay Invoice <?php echo sanitize($inv['invoice_number']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="pay_invoice">
                                                <input type="hidden" name="invoice_id" value="<?php echo $inv['id']; ?>">
                                                
                                                <div class="text-center mb-4">
                                                    <p class="text-muted mb-1">Amount to Pay</p>
                                                    <h3 class="fw-bold">$<?php echo number_format($inv['amount'], 2); ?></h3>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Card Number</label>
                                                    <input type="text" class="form-control bg-light border-0" placeholder="**** **** **** 4242" required>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">Expiry</label>
                                                        <input type="text" class="form-control bg-light border-0" placeholder="MM/YY" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">CVC</label>
                                                        <input type="text" class="form-control bg-light border-0" placeholder="***" required>
                                                    </div>
                                                </div>
                                                <p class="small text-muted mb-0"><i class="bi bi-lock-fill"></i> This is a secure, simulated payment portal.</p>
                                            </div>
                                            <div class="modal-footer border-top-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-brand">Confirm Payment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No invoices found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
