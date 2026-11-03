<?php
// ============================================================
// includes/header.php — Shared Navbar & HTML Head
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Raleway:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= SITE_URL ?>/index.php">
            <i class="fas fa-balance-scale gold-icon"></i>
            <span class="brand-name">The Elite <span class="gold-text">Attorney</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/pages/lawyers.php">Find Lawyers</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/pages/services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/pages/contact.php">Contact</a></li>

                <?php if (isCustomer()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/pages/my_appointments.php"><i class="fas fa-calendar-check me-1"></i>My Appointments</a></li>
                    <li class="nav-item"><a class="nav-link btn-logout" href="<?= SITE_URL ?>/auth/logout.php">Logout</a></li>
                <?php elseif (isLawyer()): ?>
                    <li class="nav-item"><a class="nav-link gold-text" href="<?= SITE_URL ?>/lawyer/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link btn-logout" href="<?= SITE_URL ?>/auth/logout.php">Logout</a></li>
                <?php elseif (isAdmin()): ?>
                    <li class="nav-item"><a class="nav-link gold-text" href="<?= SITE_URL ?>/admin/dashboard.php"><i class="fas fa-cog me-1"></i>Admin Panel</a></li>
                    <li class="nav-item"><a class="nav-link btn-logout" href="<?= SITE_URL ?>/auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/auth/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link btn-gold-outline" href="<?= SITE_URL ?>/lawyer/register.php">Join as Lawyer</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (!empty($_SESSION['flash_msg'])): ?>
<div class="flash-alert alert alert-<?= $_SESSION['flash_type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
    <?= $_SESSION['flash_msg'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); endif; ?>
