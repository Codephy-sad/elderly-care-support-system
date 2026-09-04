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

$pageTitle = 'Register';
require_once '../includes/header.php';
?>

<div class="card auth-card">
    <div class="card-body p-4">
        <h1 class="h3 mb-3 text-center">Create an account</h1>
        <p class="text-muted text-center">Register as Senior Resident, Family, Donor, or Volunteer.</p>

        <?php if (count($errors) > 0) { ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error) { ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <form method="post" action="register.php">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($formName); ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($formEmail); ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-4">
                <label for="role_id" class="form-label">Role</label>
                <select class="form-select" id="role_id" name="role_id" required>
                    <option value="">Select a role</option>
                    <option value="1" <?php if ($formRoleId == 1) echo 'selected'; ?>>Senior Resident</option>
                    <?php foreach ($allowedRoles as $role) { ?>
                        <option value="<?php echo $role['id']; ?>" <?php if ($formRoleId == $role['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($role['name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit" class="btn btn-brand w-100">Register</button>
        </form>

        <p class="mt-3 mb-0 text-center">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
