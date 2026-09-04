<?php
$pageTitle = $pageTitle ?? (defined('APP_NAME') ? APP_NAME : 'Elderly Care');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo sanitize(app_base_url() . '/assets/css/style.css'); ?>">
</head>
<body class="app-body">
<?php require_once __DIR__ . '/navbar.php'; ?>
<main class="container py-4">
