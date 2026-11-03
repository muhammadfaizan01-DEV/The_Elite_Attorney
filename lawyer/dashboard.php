<?php
// ============================================================
// lawyer/dashboard.php — Lawyer Dashboard
// ============================================================
require_once '../includes/config.php';

// Must be logged in as lawyer
if (!isLawyer()) redirect(SITE_URL . '/lawyer/login.php', 'Please login first.', 'error');

$lawyerId = $_SESSION['lawyer_id'];

// Fetch lawyer info
$stmt = $conn->prepare("SELECT * FROM lawyers WHERE id = ?");
$stmt->bind_param('i', $lawyerId);
$stmt->execute();
$lawyer = $stmt->get_result()->fetch_assoc();

// Stats
$totalAppts   = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE lawyer_id = $lawyerId")->fetch_assoc()['c'];
$pendingAppts = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE lawyer_id = $lawyerId AND status = 'pending'")->fetch_assoc()['c'];
$doneAppts    = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE lawyer_id = $lawyerId AND status = 'completed'")->fetch_assoc()['c'];
$reviewCount  = $conn->query("SELECT COUNT(*) AS c FROM reviews WHERE lawyer_id = $lawyerId")->fetch_assoc()['c'];

// Recent appointments
$appointments = $conn->query("
    SELECT a.*, u.name AS customer_name, u.email AS customer_email
    FROM appointments a
    JOIN users u ON u.id = a.customer_id
    WHERE a.lawyer_id = $lawyerId
    ORDER BY a.created_at DESC
    LIMIT 10
");

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $apptId    = (int)$_POST['appt_id'];
    $newStatus = clean($conn, $_POST['status']);
    if (in_array($newStatus, ['confirmed','cancelled','completed'])) {
        $conn->query("UPDATE appointments SET status='$newStatus' WHERE id=$apptId AND lawyer_id=$lawyerId");
        redirect(SITE_URL . '/lawyer/dashboard.php', 'Appointment updated.');
    }
}

$pageTitle = 'Lawyer Dashboard';
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
        <i class="fas fa-balance-scale gold-icon me-2"></i>
        <span class="gold-text">Elite</span> Attorney
    </div>

    <nav>
        <a href="<?= SITE_URL ?>/lawyer/dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="<?= SITE_URL ?>/lawyer/appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a>
        <a href="<?= SITE_URL ?>/lawyer/profile.php"><i class="fas fa-user-edit"></i>Edit Profile</a>
        <a href="<?= SITE_URL ?>/pages/lawyer_profile.php?id=<?= $lawyerId ?>"><i class="fas fa-eye"></i>View My Profile</a>
        <a href="<?= SITE_URL ?>/auth/logout.php" style="color:#ff6b6b"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">

    <!-- Top Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-family:'Playfair Display',serif; margin:0">
                Welcome, <span class="gold-text"><?= htmlspecialchars($lawyer['name']) ?></span>
            </h4>
            <p style="color:var(--text-muted); font-size:0.88rem; margin:0">
                <?= $lawyer['is_approved'] ? '<span style="color:#27ae60"><i class="fas fa-check-circle me-1"></i>Profile Approved</span>' : '<span style="color:#e74c3c"><i class="fas fa-clock me-1"></i>Pending Approval</span>' ?>
            </p>
        </div>
        <img src="<?= SITE_URL ?>/uploads/photos/<?= htmlspecialchars($lawyer['photo']) ?>"
             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($lawyer['name']) ?>&background=C9A84C&color=000'"
             style="width:45px;height:45px;border-radius:50%;border:2px solid var(--gold);object-fit:cover" alt="">
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
        <div class="col-md-3 col-6">
            <div class="glass-card stat-card">
                <div class="stat-value"><?= $totalAppts ?></div>
                <div class="stat-label">Total Appointments</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card stat-card">
                <div class="stat-value" style="color:#f39c12"><?= $pendingAppts ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card stat-card">
                <div class="stat-value" style="color:#27ae60"><?= $doneAppts ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card stat-card">
                <div class="stat-value"><?= number_format($lawyer['rating'],1) ?></div>
                <div class="stat-label">Rating (<?= $reviewCount ?> reviews)</div>
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="glass-card p-4">
        <h5 class="mb-4" style="font-family:'Playfair Display',serif">
            Recent <span class="gold-text">Appointments</span>
        </h5>

        <?php if ($appointments->num_rows === 0): ?>
        <p style="color:var(--text-muted)">No appointments yet. Share your profile to get bookings!</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table dash-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Date & Time</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($apt = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($apt['customer_name']) ?></strong><br>
                            <small style="color:var(--text-muted)"><?= htmlspecialchars($apt['customer_email']) ?></small>
                        </td>
                        <td>
                            <?= date('d M Y', strtotime($apt['appt_date'])) ?><br>
                            <small style="color:var(--text-muted)"><?= date('h:i A', strtotime($apt['appt_time'])) ?></small>
                        </td>
                        <td style="max-width:200px; word-break:break-word; color:var(--text-muted); font-size:0.85rem">
                            <?= htmlspecialchars(substr($apt['message'], 0, 80)) ?><?= strlen($apt['message']) > 80 ? '...' : '' ?>
                        </td>
                        <td>
                            <?php
                            $badges = [
                                'pending'   => 'badge-gold',
                                'confirmed' => 'badge-green',
                                'cancelled' => 'badge-red',
                                'completed' => 'badge-muted',
                            ];
                            echo '<span class="' . ($badges[$apt['status']] ?? 'badge-muted') . '">' . ucfirst($apt['status']) . '</span>';
                            ?>
                        </td>
                        <td>
                            <?php if ($apt['status'] === 'pending'): ?>
                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="appt_id" value="<?= $apt['id'] ?>">
                                <button name="update_status" value="1" class="btn btn-sm badge-green border-0"
                                        onclick="this.form.querySelector('[name=status]').value='confirmed'" type="submit">
                                    <input type="hidden" name="status" value="confirmed">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button name="update_status" value="1" class="btn btn-sm badge-red border-0"
                                        onclick="this.form.querySelector('[name=status]').value='cancelled'" type="submit">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php elseif ($apt['status'] === 'confirmed'): ?>
                            <form method="POST">
                                <input type="hidden" name="appt_id" value="<?= $apt['id'] ?>">
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" name="update_status" class="btn btn-sm badge-muted border-0">
                                    <i class="fas fa-check-double"></i> Done
                                </button>
                            </form>
                            <?php else: ?>
                            <span style="color:var(--text-muted); font-size:0.8rem">—</span>
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
