<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);
$success = '';
$error = '';

// auto-create profile if it doesn't exist
if (!$profile_id) {
    $stmt = $pdo->prepare("INSERT INTO elderly_profiles (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $profile_id = (int)$pdo->lastInsertId();
}

// get current profile
$stmt = $pdo->prepare("SELECT * FROM elderly_profiles WHERE id = ?");
$stmt->execute([$profile_id]);
$profile = $stmt->fetch();

// handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $medical_conditions = trim($_POST['medical_conditions'] ?? '');
    $allergies = trim($_POST['allergies'] ?? '');
    $dietary_requirements = trim($_POST['dietary_requirements'] ?? '');
    $mobility_notes = trim($_POST['mobility_notes'] ?? '');
    $emergency_contact_name = trim($_POST['emergency_contact_name'] ?? '');
    $emergency_contact_phone = trim($_POST['emergency_contact_phone'] ?? '');

    $stmt = $pdo->prepare("
        UPDATE elderly_profiles SET
            date_of_birth = ?,
            gender = ?,
            blood_group = ?,
            medical_conditions = ?,
            allergies = ?,
            dietary_requirements = ?,
            mobility_notes = ?,
            emergency_contact_name = ?,
            emergency_contact_phone = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([
        $date_of_birth ?: null,
        $gender ?: null,
        $blood_group ?: null,
        $medical_conditions ?: null,
        $allergies ?: null,
        $dietary_requirements ?: null,
        $mobility_notes ?: null,
        $emergency_contact_name ?: null,
        $emergency_contact_phone ?: null,
        $profile_id,
        $user_id
    ]);

    $success = 'Profile updated successfully.';

    // refresh
    $stmt = $pdo->prepare("SELECT * FROM elderly_profiles WHERE id = ?");
    $stmt->execute([$profile_id]);
    $profile = $stmt->fetch();
}

// get user info
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$pageTitle = 'My Profile';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>My Profile</h1>
        <p class="text-muted mb-0">View and update your personal information</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo sanitize($success); ?></div>
    <?php endif; ?>

    <!-- Account info (read-only) -->
    <div class="elderly-card elderly-card--highlight mb-4">
        <h2 class="h5 mb-2">Account Information</h2>
        <div class="row">
            <div class="col-sm-6">
                <small class="text-muted d-block">Name</small>
                <strong><?php echo sanitize($user['name']); ?></strong>
            </div>
            <div class="col-sm-6">
                <small class="text-muted d-block">Email</small>
                <strong><?php echo sanitize($user['email']); ?></strong>
            </div>
        </div>
        <small class="text-muted mt-2 d-block">Contact an administrator to change your name or email.</small>
    </div>

    <!-- Editable profile -->
    <div class="elderly-card">
        <h2 class="elderly-section-title">Personal Details</h2>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                           value="<?php echo sanitize($profile['date_of_birth'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="">Select...</option>
                        <option value="Male" <?php echo ($profile['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($profile['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($profile['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="blood_group" class="form-label">Blood Group</label>
                    <select class="form-select" id="blood_group" name="blood_group">
                        <option value="">Select...</option>
                        <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                            <option value="<?php echo $bg; ?>" <?php echo ($profile['blood_group'] ?? '') === $bg ? 'selected' : ''; ?>>
                                <?php echo $bg; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label for="medical_conditions" class="form-label">Medical Conditions</label>
                    <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="2"><?php echo sanitize($profile['medical_conditions'] ?? ''); ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="allergies" class="form-label">Allergies</label>
                    <textarea class="form-control" id="allergies" name="allergies" rows="2"><?php echo sanitize($profile['allergies'] ?? ''); ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="dietary_requirements" class="form-label">Dietary Requirements</label>
                    <textarea class="form-control" id="dietary_requirements" name="dietary_requirements" rows="2"><?php echo sanitize($profile['dietary_requirements'] ?? ''); ?></textarea>
                </div>

                <div class="col-12">
                    <label for="mobility_notes" class="form-label">Mobility Notes</label>
                    <input type="text" class="form-control" id="mobility_notes" name="mobility_notes"
                           value="<?php echo sanitize($profile['mobility_notes'] ?? ''); ?>"
                           placeholder="e.g. Uses a wheelchair">
                </div>

                <div class="col-md-6">
                    <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                    <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name"
                           value="<?php echo sanitize($profile['emergency_contact_name'] ?? ''); ?>">
                </div>

                <div class="col-md-6">
                    <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                    <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone"
                           value="<?php echo sanitize($profile['emergency_contact_phone'] ?? ''); ?>">
                </div>

                <div class="col-12">
                    <button type="submit" name="update_profile" class="btn btn-brand btn-elderly">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
