<?php
require_once '../config/database.php';
require_once '../config/auth.php';

checkRole(2);

$user_id = (int)$_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Get current user details
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            $error_msg = 'Name cannot be empty.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            if ($stmt->execute([$name, $user_id])) {
                $_SESSION['name'] = $name; // update session
                $user['name'] = $name;
                $success_msg = 'Profile updated successfully.';
            } else {
                $error_msg = 'Failed to update profile.';
            }
        }
    } elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_msg = 'All password fields are required.';
        } elseif ($new_password !== $confirm_password) {
            $error_msg = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error_msg = 'New password must be at least 6 characters.';
        } else {
            // verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $db_hash = $stmt->fetchColumn();
            
            if (password_verify($current_password, $db_hash)) {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$new_hash, $user_id]);
                $success_msg = 'Password updated successfully.';
            } else {
                $error_msg = 'Current password is incorrect.';
            }
        }
    }
}

$pageTitle = 'My Profile';
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Account Profile</h1>
        <p class="text-muted mb-0">Manage your family portal account settings</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            &larr; Dashboard
        </a>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success border-0 shadow-sm"><?php echo sanitize($success_msg); ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?php echo sanitize($error_msg); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-4 border-bottom pb-2">Profile Details</h2>
                
                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light border-0" id="name" name="name" value="<?php echo sanitize($user['name']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control bg-light border-0" id="email" value="<?php echo sanitize($user['email']); ?>" disabled readonly>
                        <div class="form-text text-muted">Email address cannot be changed directly. Please contact administration if you need to update it.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-brand">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-4 border-bottom pb-2">Change Password</h2>
                
                <form method="POST">
                    <input type="hidden" name="update_password" value="1">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control bg-light border-0" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control bg-light border-0" id="new_password" name="new_password" required minlength="6">
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-bold">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control bg-light border-0" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-brand">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
