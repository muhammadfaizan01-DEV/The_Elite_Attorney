<?php
// ============================================================
// admin/appointments.php — View & Manage All Appointments
// ============================================================
require_once '../includes/config.php';
if (!isAdmin()) redirect(SITE_URL . '/auth/login.php', 'Admin access required.', 'error');

// Delete appointment
if (isset($_GET['delete'])) {
    $aid = (int)$_GET['delete'];
    $conn->query("DELETE FROM appointments WHERE id = $aid");
    redirect(SITE_URL . '/admin/appointments.php', 'Appointment deleted.', 'error');
}

// Filter by status
$status = isset($_GET['status']) ? clean($conn, $_GET['status']) : '';
$search = isset($_GET['search']) ? clean($conn, $_GET['search']) : '';

$where = "WHERE 1=1";
if ($status && in_array($status, ['pending','confirmed','cancelled','completed'])) {
    $where .= " AND a.status = '$status'";
}
if ($search) {
    $where .= " AND (u.name LIKE '%$search%' OR l.name LIKE '%$search%')";
}

$appointments = $conn->query("
    SELECT a.*, u.name AS customer_name, u.email AS customer_email,
           l.name AS lawyer_name, l.specialization
    FROM appointments a
    JOIN users u ON u.id = a.customer_id
    JOIN lawyers l ON l.id = a.lawyer_id
    $where
    ORDER BY a.appt_date DESC, a.appt_time DESC
");

// Status counts
$counts = [];
foreach (['pending','confirmed','completed','cancelled'] as $st) {
    $counts[$st] = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status='$st'")->fetch_assoc()['c'];
}

$pageTitle = 'Manage Appointments';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Raleway:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">

<div class="sidebar">
    <div class="sidebar-brand"><i class="fas fa-cog gold-icon me-2"></i><span class="gold-text">Admin</span> Panel</div>
    <nav>
        <a href="<?= SITE_URL ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="<?= SITE_URL ?>/admin/lawyers.php"><i class="fas fa-gavel"></i>Manage Lawyers</a>
        <a href="<?= SITE_URL ?>/admin/customers.php"><i class="fas fa-users"></i>Customers</a>
        <a href="<?= SITE_URL ?>/admin/appointments.php" class="active"><i class="fas fa-calendar-check"></i>Appointments</a>
        <a href="<?= SITE_URL ?>/admin/services.php"><i class="fas fa-layer-group"></i>Services</a>
        <a href="<?= SITE_URL ?>/admin/messages.php"><i class="fas fa-envelope"></i>Messages</a>
        <a href="<?= SITE_URL ?>/index.php"><i class="fas fa-globe"></i>View Site</a>
        <a href="<?= SITE_URL ?>/auth/logout.php" style="color:#ff6b6b"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 style="font-family:'Playfair Display',serif; margin:0">All <span class="gold-text">Appointments</span></h4>
    </div>

    <?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type']==='success'?'success':'danger' ?> alert-dismissible fade show">
        <?= $_SESSION['flash_msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_msg'],$_SESSION['flash_type']); endif; ?>

    <!-- Status Filter Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="<?= SITE_URL ?>/admin/appointments.php" class="btn btn-sm <?= !$status?'btn-gold':'btn-outline-gold' ?>">
            All (<?= array_sum($counts) ?>)
        </a>
        <?php
        $tabColors = ['pending'=>'badge-gold','confirmed'=>'badge-green','completed'=>'badge-muted','cancelled'=>'badge-red'];
        foreach ($counts as $st => $cnt):
        ?>
        <a href="appointments.php?status=<?= $st ?>" class="btn btn-sm <?= $status===$st?'btn-gold':'btn-outline-gold' ?>">
            <?= ucfirst($st) ?> (<?= $cnt ?>)
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div class="glass-card p-3 mb-4">
        <form method="GET" class="d-flex gap-2">
            <?php if ($status): ?><input type="hidden" name="status" value="<?= $status ?>"><?php endif; ?>
            <input type="text" name="search" class="form-control" placeholder="Search by customer or lawyer name..."
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-gold btn px-3"><i class="fas fa-search"></i></button>
            <?php if ($search): ?>
            <a href="appointments.php<?= $status?'?status='.$status:'' ?>" class="btn btn-sm btn-outline-gold">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table dash-table">
                <thead>
                    <tr>
                        <th>#</th><th>Customer</th><th>Lawyer</th>
                        <th>Date & Time</th><th>Message</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($appointments->num_rows === 0): ?>
                    <tr><td colspan="7" class="text-center" style="color:var(--text-muted); padding:2rem">No appointments found.</td></tr>
                    <?php else: $i=1; while ($apt = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td style="color:var(--text-muted)"><?= $i++ ?></td>
                        <td>
                            <div><?= htmlspecialchars($apt['customer_name']) ?></div>
                            <small style="color:var(--text-muted)"><?= htmlspecialchars($apt['customer_email']) ?></small>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($apt['lawyer_name']) ?></div>
                            <small style="color:var(--text-muted)"><?= htmlspecialchars($apt['specialization'] ?? '') ?></small>
                        </td>
                        <td style="font-size:0.85rem">
                            <div><?= date('d M Y', strtotime($apt['appt_date'])) ?></div>
                            <small style="color:var(--text-muted)"><?= date('h:i A', strtotime($apt['appt_time'])) ?></small>
                        </td>
                        <td style="max-width:160px; color:var(--text-muted); font-size:0.82rem; word-break:break-word">
                            <?= htmlspecialchars(substr($apt['message'],0,70)) ?><?= strlen($apt['message'])>70?'...':'' ?>
                        </td>
                        <td>
                            <?php
                            $badgeMap = ['pending'=>'badge-gold','confirmed'=>'badge-green','cancelled'=>'badge-red','completed'=>'badge-muted'];
                            echo '<span class="'.($badgeMap[$apt['status']]??'badge-muted').'">'.ucfirst($apt['status']).'</span>';
                            ?>
                        </td>
                        <td>
                            <a href="appointments.php?delete=<?= $apt['id'] ?>"
                               class="btn btn-sm badge-red border-0"
                               onclick="return confirm('Delete this appointment?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
