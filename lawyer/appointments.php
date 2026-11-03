<?php
// ============================================================
// lawyer/appointments.php — Lawyer Full Appointments List
// ============================================================
require_once '../includes/config.php';
if (!isLawyer()) redirect(SITE_URL . '/lawyer/login.php', 'Please login.', 'error');

$lawyerId = $_SESSION['lawyer_id'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $apptId    = (int)$_POST['appt_id'];
    $newStatus = clean($conn, $_POST['status']);
    if (in_array($newStatus, ['confirmed','cancelled','completed'])) {
        $conn->query("UPDATE appointments SET status='$newStatus' WHERE id=$apptId AND lawyer_id=$lawyerId");
        redirect(SITE_URL . '/lawyer/appointments.php', 'Appointment status updated.');
    }
}

$status = isset($_GET['status']) ? clean($conn, $_GET['status']) : '';
$where  = "WHERE a.lawyer_id = $lawyerId";
if ($status && in_array($status, ['pending','confirmed','cancelled','completed'])) {
    $where .= " AND a.status = '$status'";
}

$appointments = $conn->query("
    SELECT a.*, u.name AS customer_name, u.email AS customer_email
    FROM appointments a
    JOIN users u ON u.id = a.customer_id
    $where
    ORDER BY a.appt_date DESC, a.appt_time DESC
");

$counts = [];
foreach (['pending','confirmed','completed','cancelled'] as $st) {
    $counts[$st] = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE lawyer_id=$lawyerId AND status='$st'")->fetch_assoc()['c'];
}

$pageTitle = 'My Appointments';
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
    <div class="sidebar-brand">
        <i class="fas fa-balance-scale gold-icon me-2"></i>
        <span class="gold-text">Elite</span> Attorney
    </div>
    <nav>
        <a href="<?= SITE_URL ?>/lawyer/dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="<?= SITE_URL ?>/lawyer/appointments.php" class="active"><i class="fas fa-calendar-check"></i>Appointments</a>
        <a href="<?= SITE_URL ?>/lawyer/profile.php"><i class="fas fa-user-edit"></i>Edit Profile</a>
        <a href="<?= SITE_URL ?>/pages/lawyer_profile.php?id=<?= $lawyerId ?>"><i class="fas fa-eye"></i>View My Profile</a>
        <a href="<?= SITE_URL ?>/auth/logout.php" style="color:#ff6b6b"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>

<div class="main-content">
    <h4 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem">
        My <span class="gold-text">Appointments</span>
    </h4>

    <?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type']==='success'?'success':'danger' ?> alert-dismissible fade show">
        <?= $_SESSION['flash_msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_msg'],$_SESSION['flash_type']); endif; ?>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <?php foreach ($counts as $st => $cnt):
            $colors = ['pending'=>'#f39c12','confirmed'=>'#27ae60','completed'=>'#888','cancelled'=>'#e74c3c'];
        ?>
        <div class="col-md-3 col-6">
            <div class="glass-card stat-card text-center">
                <div class="stat-value" style="color:<?= $colors[$st] ?>"><?= $cnt ?></div>
                <div class="stat-label"><?= ucfirst($st) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filter Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?= SITE_URL ?>/lawyer/appointments.php" class="btn btn-sm <?= !$status?'btn-gold':'btn-outline-gold' ?>">All</a>
        <?php foreach ($counts as $st => $cnt): ?>
        <a href="appointments.php?status=<?= $st ?>" class="btn btn-sm <?= $status===$st?'btn-gold':'btn-outline-gold' ?>">
            <?= ucfirst($st) ?> (<?= $cnt ?>)
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="glass-card p-4">
        <?php if ($appointments->num_rows === 0): ?>
        <div class="text-center py-4">
            <i class="fas fa-calendar-times fa-3x mb-3" style="color:var(--text-muted)"></i>
            <p style="color:var(--text-muted)">No appointments found.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table dash-table">
                <thead>
                    <tr><th>Customer</th><th>Date</th><th>Time</th><th>Message</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php while ($apt = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div><?= htmlspecialchars($apt['customer_name']) ?></div>
                            <small style="color:var(--text-muted)"><?= htmlspecialchars($apt['customer_email']) ?></small>
                        </td>
                        <td style="font-size:0.88rem"><?= date('d M Y', strtotime($apt['appt_date'])) ?></td>
                        <td style="font-size:0.88rem;color:var(--text-muted)"><?= date('h:i A', strtotime($apt['appt_time'])) ?></td>
                        <td style="max-width:180px;font-size:0.82rem;color:var(--text-muted)">
                            <?= htmlspecialchars(substr($apt['message'],0,70)) ?><?= strlen($apt['message'])>70?'...':'' ?>
                        </td>
                        <td>
                            <?php
                            $bMap = ['pending'=>'badge-gold','confirmed'=>'badge-green','cancelled'=>'badge-red','completed'=>'badge-muted'];
                            echo '<span class="'.($bMap[$apt['status']]??'badge-muted').'">'.ucfirst($apt['status']).'</span>';
                            ?>
                        </td>
                        <td>
                            <?php if ($apt['status'] === 'pending'): ?>
                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="appt_id" value="<?= $apt['id'] ?>">
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" name="update_status" class="btn btn-sm badge-green border-0" title="Confirm">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" class="d-flex gap-1 mt-1">
                                <input type="hidden" name="appt_id" value="<?= $apt['id'] ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" name="update_status" class="btn btn-sm badge-red border-0" title="Cancel">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php elseif ($apt['status'] === 'confirmed'): ?>
                            <form method="POST">
                                <input type="hidden" name="appt_id" value="<?= $apt['id'] ?>">
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" name="update_status" class="btn btn-sm badge-muted border-0">
                                    <i class="fas fa-check-double me-1"></i>Done
                                </button>
                            </form>
                            <?php else: ?>
                            <span style="color:var(--text-muted);font-size:0.8rem">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
