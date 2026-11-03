<?php
// ============================================================
// admin/dashboard.php — Admin Control Panel
// ============================================================
require_once '../includes/config.php';
if (!isAdmin()) redirect(SITE_URL . '/auth/login.php', 'Admin access required.', 'error');

// Stats
$totalLawyers   = $conn->query("SELECT COUNT(*) AS c FROM lawyers")->fetch_assoc()['c'];
$pendingLawyers = $conn->query("SELECT COUNT(*) AS c FROM lawyers WHERE is_approved = 0")->fetch_assoc()['c'];
$totalCustomers = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'customer'")->fetch_assoc()['c'];
$totalAppts     = $conn->query("SELECT COUNT(*) AS c FROM appointments")->fetch_assoc()['c'];
$unreadMsgs     = $conn->query("SELECT COUNT(*) AS c FROM contact_messages WHERE is_read = 0")->fetch_assoc()['c'];

// Recent pending lawyers
$pendingList = $conn->query("
    SELECT id, name, email, specialization, city, created_at
    FROM lawyers WHERE is_approved = 0 ORDER BY created_at DESC LIMIT 5
");

// Recent appointments
$recentAppts = $conn->query("
    SELECT a.id, a.appt_date, a.appt_time, a.status,
           u.name AS customer, l.name AS lawyer
    FROM appointments a
    JOIN users u ON u.id = a.customer_id
    JOIN lawyers l ON l.id = a.lawyer_id
    ORDER BY a.created_at DESC LIMIT 8
");

$pageTitle = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Raleway:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-cog gold-icon me-2"></i>
        <span class="gold-text">Admin</span> Panel
    </div>
    <nav>
        <a href="<?= SITE_URL ?>/admin/dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="<?= SITE_URL ?>/admin/lawyers.php"><i class="fas fa-gavel"></i>Manage Lawyers</a>
        <a href="<?= SITE_URL ?>/admin/customers.php"><i class="fas fa-users"></i>Customers</a>
        <a href="<?= SITE_URL ?>/admin/appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a>
        <a href="<?= SITE_URL ?>/admin/services.php"><i class="fas fa-layer-group"></i>Services</a>
        <a href="<?= SITE_URL ?>/admin/messages.php"><i class="fas fa-envelope"></i>Messages
            <?php if ($unreadMsgs > 0): ?>
            <span class="badge-red ms-1"><?= $unreadMsgs ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/index.php"><i class="fas fa-globe"></i>View Site</a>
        <a href="<?= SITE_URL ?>/auth/logout.php" style="color:#ff6b6b"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>

<!-- Main -->
<div class="main-content">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-family:'Playfair Display',serif; margin:0">Admin <span class="gold-text">Dashboard</span></h4>
            <p style="color:var(--text-muted); font-size:0.85rem; margin:0"><?= date('l, d F Y') ?></p>
        </div>
        <span style="color:var(--text-muted); font-size:0.85rem">
            <i class="fas fa-user-shield gold-icon me-1"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
        </span>
    </div>

    <!-- Flash -->
    <?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type']==='success'?'success':'danger' ?> alert-dismissible fade show">
        <?= $_SESSION['flash_msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_msg'],$_SESSION['flash_type']); endif; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="glass-card stat-card text-center">
                <div class="stat-value"><?= $totalLawyers ?></div>
                <div class="stat-label">Total Lawyers</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="glass-card stat-card text-center">
                <div class="stat-value" style="color:#f39c12"><?= $pendingLawyers ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="glass-card stat-card text-center">
                <div class="stat-value" style="color:#27ae60"><?= $totalCustomers ?></div>
                <div class="stat-label">Customers</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="glass-card stat-card text-center">
                <div class="stat-value"><?= $totalAppts ?></div>
                <div class="stat-label">Appointments</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="glass-card stat-card text-center">
                <div class="stat-value" style="color:#e74c3c"><?= $unreadMsgs ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- Pending Lawyers -->
        <div class="col-lg-6">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="font-family:'Playfair Display',serif; margin:0">Pending <span class="gold-text">Approvals</span></h5>
                    <a href="<?= SITE_URL ?>/admin/lawyers.php?filter=pending" class="gold-text" style="font-size:0.85rem">View All →</a>
                </div>
                <?php if ($pendingList->num_rows === 0): ?>
                <p style="color:var(--text-muted); font-size:0.88rem">No pending lawyer approvals.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table dash-table">
                        <thead>
                            <tr><th>Name</th><th>Specialization</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($pl = $pendingList->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($pl['name']) ?><br>
                                    <small style="color:var(--text-muted)"><?= htmlspecialchars($pl['email']) ?></small>
                                </td>
                                <td style="color:var(--text-muted); font-size:0.85rem"><?= htmlspecialchars($pl['specialization'] ?? '—') ?></td>
                                <td>
                                    <a href="<?= SITE_URL ?>/admin/lawyers.php?approve=<?= $pl['id'] ?>" class="badge-green" style="text-decoration:none; padding:3px 8px; border-radius:4px; font-size:0.78rem">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Appointments -->
        <div class="col-lg-6">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="font-family:'Playfair Display',serif; margin:0">Recent <span class="gold-text">Appointments</span></h5>
                    <a href="<?= SITE_URL ?>/admin/appointments.php" class="gold-text" style="font-size:0.85rem">View All →</a>
                </div>
                <div class="table-responsive">
                    <table class="table dash-table">
                        <thead>
                            <tr><th>Customer</th><th>Lawyer</th><th>Date</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($recentAppts->num_rows === 0): ?>
                            <tr><td colspan="4" style="color:var(--text-muted)">No appointments yet.</td></tr>
                            <?php else: while ($apt = $recentAppts->fetch_assoc()): ?>
                            <tr>
                                <td style="font-size:0.85rem"><?= htmlspecialchars($apt['customer']) ?></td>
                                <td style="font-size:0.85rem"><?= htmlspecialchars($apt['lawyer']) ?></td>
                                <td style="font-size:0.82rem; color:var(--text-muted)"><?= date('d M', strtotime($apt['appt_date'])) ?></td>
                                <td>
                                    <?php
                                    $badges = ['pending'=>'badge-gold','confirmed'=>'badge-green','cancelled'=>'badge-red','completed'=>'badge-muted'];
                                    echo '<span class="'.($badges[$apt['status']]??'badge-muted').'">'.ucfirst($apt['status']).'</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
