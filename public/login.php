<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// map role_id to its folder
$role_folders = [
    1 => 'elderly',
    2 => 'family',
    3 => 'caregiver',
    4 => 'kitchen',
    5 => 'manager',
    6 => 'admin',
    7 => 'donor',
    8 => 'volunteer',
];

// helper: work out where to send the user based on their role
function get_redirect_url($role_id, $role_folders) {
    $redirect = "index.php"; // safe fallback
    if (array_key_exists($role_id, $role_folders)) {
        $folder = $role_folders[$role_id];
        $path   = __DIR__ . "/../{$folder}/index.php";
        if (file_exists($path)) {
            $redirect = "../{$folder}/index.php";
        }
    }
    return $redirect;
}

// already logged in? send them straight to their dashboard
if (isLoggedIn()) {
    $role_id = isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0;
    header("Location: " . get_redirect_url($role_id, $role_folders));
    exit();
}

$error = '';
$formEmail = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $formEmail = trim($_POST['email']);
        $password  = $_POST['password'];

        if (empty($formEmail) || empty($password)) {
            $error = "Please fill in both fields.";
        } else {
            $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role_id, active_status FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$formEmail]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if ($user['active_status'] == 1) {
                    if (password_verify($password, $user['password_hash'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name']    = $user['name'];
                        $_SESSION['role_id'] = $user['role_id'];

                        $role_id = (int)$user['role_id'];
                        header("Location: " . get_redirect_url($role_id, $role_folders));
                        exit();
                    } else {
                        $error = "Wrong email or password.";
                    }
                } else {
                    $error = "Wrong email or password.";
                }
            } else {
                $error = "Wrong email or password.";
            }
        }
    }
}

$pageTitle = 'Login';
require_once '../includes/header.php';
?>

<div class="card auth-card">
    <div class="card-body p-4">
        <h1 class="h3 mb-3 text-center">Log in</h1>
        <p class="text-muted text-center">Sign in to the Elderly Care platform.</p>

        <?php if ($error != '') { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php } ?>

        <form method="post" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($formEmail); ?>">
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-brand w-100">Log in</button>
        </form>

        <p class="mt-3 mb-0 text-center">
            Need an account? <a href="register.php">Register</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
