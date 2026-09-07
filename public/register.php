<?php
require_once '../config/database.php';
require_once '../config/auth.php';

$errors = [];
$formName   = '';
$formEmail  = '';
$formRoleId = '';

$stmt = $pdo->query("SELECT id, name FROM roles WHERE name IN ('Family', 'Donor', 'Volunteer') ORDER BY id ASC");
$allowedRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['role_id'])) {
        $formName   = trim($_POST['name']);
        $formEmail  = trim($_POST['email']);
        $password   = $_POST['password'];
        $formRoleId = $_POST['role_id'];

        if (empty($formName) || strlen($formName) < 2) {
            $errors[] = "Name must be at least 2 characters.";
        }

        if (empty($formEmail) || !filter_var($formEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "That doesn't look like a valid email.";
        }

        if (strlen($password) < 8) {
            $errors[] = "Password needs to be at least 8 characters.";
        }

        if (empty($formRoleId)) {
            $errors[] = "Pick a role.";
        }

        // check if email is already taken
        if (count($errors) == 0) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$formEmail]);
            if ($stmt->fetch()) {
                $errors[] = "That email is already registered. Try logging in instead.";
            }
        }

        if (count($errors) == 0) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (role_id, name, email, password_hash, active_status) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$formRoleId, $formName, $formEmail, $passwordHash]);

            header("Location: login.php");
            exit();
        }
    }
}

$pageTitle = 'Register — Elderly Care';
$isLandingPage = true;
require_once '../includes/header.php';
?>

<div class="page-hero" style="background: linear-gradient(135deg, var(--navy-dark), #2a4470); padding-bottom: 6rem;">
    <div class="page-inner">
        <h1>Create an account</h1>
        <p class="page-hero-sub">Register as Senior Resident, Family, Donor, or Volunteer.</p>
    </div>
</div>

<section class="page-section section-cream" style="margin-top: -4rem; padding-top: 0; position: relative; z-index: 10;">
    <div class="page-inner" style="max-width: 520px;">
        <div class="overview-card auth-card" style="padding: 2.5rem 2rem; text-align: left;">
            <h2 style="font-family: 'Merriweather', serif; font-size: 1.6rem; margin-bottom: 1.5rem; text-align: center;">Register</h2>

            <?php if (count($errors) > 0) { ?>
                <div class="form-error show" style="margin-bottom: 1rem; color: #dc3545; background: #f8d7da; padding: 0.8rem; border-radius: 8px;">
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        <?php foreach ($errors as $error) { ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <form method="post" action="register.php" style="display: flex; flex-direction: column; gap: 1.2rem;">
                <div>
                    <label for="name" style="display: block; margin-bottom: 0.4rem; font-weight: 600;">Full Name</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($formName); ?>"
                           style="width: 100%; padding: 0.8rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>

                <div>
                    <label for="email" style="display: block; margin-bottom: 0.4rem; font-weight: 600;">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($formEmail); ?>"
                           style="width: 100%; padding: 0.8rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>

                <div>
                    <label for="password" style="display: block; margin-bottom: 0.4rem; font-weight: 600;">Password</label>
                    <input type="password" id="password" name="password" required
                           style="width: 100%; padding: 0.8rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>

                <div>
                    <label for="role_id" style="display: block; margin-bottom: 0.4rem; font-weight: 600;">Role</label>
                    <select id="role_id" name="role_id" required
                            style="width: 100%; padding: 0.8rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; background: var(--white);">
                        <option value="">Select a role</option>
                        <option value="1" <?php if ($formRoleId == 1) echo 'selected'; ?>>Senior Resident</option>
                        <?php foreach ($allowedRoles as $role) { ?>
                            <option value="<?php echo $role['id']; ?>" <?php if ($formRoleId == $role['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn-navy" style="width: 100%; margin-top: 0.5rem;">Create Account</button>
            </form>

            <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--coral); font-weight: 600; text-decoration: none;">Log in</a>
            </p>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
