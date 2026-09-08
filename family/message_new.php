<?php
require_once '../config/database.php';
require_once '../config/auth.php';

checkRole(2);

$user_id = (int)$_SESSION['user_id'];
$error = '';

// Fetch eligible recipients: Admins(1), Caregivers(3), Managers(5), Medical Staff(6)
$stmt = $pdo->prepare("
    SELECT u.id, u.name, r.name as role_name 
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.role_id IN (1, 3, 5, 6) AND u.active = 1
    ORDER BY r.name, u.name
");
$stmt->execute();
$recipients = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = (int)$_POST['receiver_id'];
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    
    if (!$receiver_id || empty($body)) {
        $error = 'Please select a recipient and enter a message.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $receiver_id, $subject, $body])) {
            header('Location: messages.php?success=sent');
            exit;
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}

$pageTitle = 'Compose Message';
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Compose Message</h1>
        <p class="text-muted mb-0">Send a secure message to staff</p>
    </div>
    <div class="mt-3 mt-md-0">
        <a href="messages.php" class="btn btn-outline-secondary btn-sm">
            &larr; Back to Messages
        </a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm col-lg-8">
    <div class="card-body p-4">
        <form method="POST">
            <div class="mb-3">
                <label for="receiver_id" class="form-label fw-bold">To <span class="text-danger">*</span></label>
                <select class="form-select bg-light border-0" id="receiver_id" name="receiver_id" required>
                    <option value="" disabled selected>Select a recipient...</option>
                    <?php 
                    $current_role = '';
                    foreach ($recipients as $rec): 
                        if ($current_role !== $rec['role_name']) {
                            if ($current_role !== '') echo '</optgroup>';
                            echo '<optgroup label="' . sanitize($rec['role_name']) . 's">';
                            $current_role = $rec['role_name'];
                        }
                    ?>
                        <option value="<?php echo $rec['id']; ?>" <?php echo (isset($_GET['to']) && $_GET['to'] == $rec['id']) ? 'selected' : ''; ?>>
                            <?php echo sanitize($rec['name']); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if ($current_role !== '') echo '</optgroup>'; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="subject" class="form-label fw-bold">Subject</label>
                <input type="text" class="form-control bg-light border-0" id="subject" name="subject" maxlength="160"
                       value="<?php echo sanitize($_POST['subject'] ?? (isset($_GET['subject']) ? $_GET['subject'] : '')); ?>"
                       placeholder="Brief summary of your message">
            </div>
            
            <div class="mb-4">
                <label for="body" class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                <textarea class="form-control bg-light border-0" id="body" name="body" rows="6" required
                          placeholder="Type your message here..."><?php echo sanitize($_POST['body'] ?? ''); ?></textarea>
            </div>
            
            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-brand px-4">
                    <i class="bi bi-send me-2"></i> Send Message
                </button>
                <a href="messages.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
