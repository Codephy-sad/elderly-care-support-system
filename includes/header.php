<?php
$pageTitle = $pageTitle ?? (defined('APP_NAME') ? APP_NAME : 'Elderly Care');
$isLandingPage = $isLandingPage ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php $styleCssPath = __DIR__ . '/../assets/css/style.css'; ?>
    <link rel="stylesheet" href="<?php echo sanitize(app_base_url() . '/assets/css/style.css?v=' . (file_exists($styleCssPath) ? filemtime($styleCssPath) : time())); ?>">
    <?php if ($isLandingPage): ?>
    <?php $landingCssPath = __DIR__ . '/../assets/css/landing.css'; ?>
    <link rel="stylesheet" href="<?php echo sanitize(app_base_url() . '/assets/css/landing.css?v=' . (file_exists($landingCssPath) ? filemtime($landingCssPath) : time())); ?>">
    <?php endif; ?>
</head>
<body class="<?php echo $isLandingPage ? 'public-page' : 'app-body'; ?>">
<?php require_once __DIR__ . '/navbar.php'; ?>
<?php if (!$isLandingPage): ?>
<main class="container py-4">
<?php else: ?>
<main>
<?php endif; ?>