<?php
require_once __DIR__ . '/constants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
}

// redirect to login if user doesn't have the right role
function checkRole($role_id) {
    if (!isLoggedIn() || (int)$_SESSION['role_id'] !== $role_id) {
        header('Location: ' . public_url('login.php'));
        exit;
    }
}

function logout() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool)($params['secure'] ?? false),
            (bool)($params['httponly'] ?? true)
        );
    }

    session_destroy();
}

function sanitize($input) {
    return htmlspecialchars((string)$input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_base_url() {
    $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = dirname($scriptName);
    $roleFolders = [
        '/public',
        '/elderly',
        '/family',
        '/caregiver',
        '/kitchen',
        '/manager',
        '/admin',
        '/donor',
        '/volunteer',
    ];

    foreach ($roleFolders as $folder) {
        $pos = strrpos($dir, $folder);
        if ($pos !== false) {
            return rtrim(substr($dir, 0, $pos), '/');
        }
    }

    return rtrim($dir, '/');
}

function public_url($page) {
    return app_base_url() . '/public/' . ltrim($page, '/');
}

function elderly_url($page) {
    return app_base_url() . '/elderly/' . ltrim($page, '/');
}

function getElderlyProfileId($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT id FROM elderly_profiles WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    return $row ? (int)$row['id'] : null;
}
