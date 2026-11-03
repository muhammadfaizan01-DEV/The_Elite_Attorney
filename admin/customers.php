<?php
// ============================================================
// admin/customers.php — Manage Customers
// ============================================================
require_once '../includes/config.php';
if (!isAdmin()) redirect(SITE_URL . '/auth/login.php', 'Admin access required.', 'error');

// Delete customer
if (isset($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $uid AND role = 'customer'");
    redirect(SITE_URL . '/admin/customers.php', 'Customer removed.', 'error');
}

$search = isset($_GET['search']) ? clean($conn, $_GET['search']) : '';
$where  = "WHERE role = 'customer'";
if ($search) $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";

$customers = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM appointments a WHERE a.customer_id = u.id) AS appt_count FROM users u $where ORDER BY u.created_at DESC");

$pageTitle = 'Manage Customers';
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
        <a href="<?= SITE_URL ?>/admin/customers.php" class="active"><i class="fas fa-users"></i>Customers</a>
        <a href="<?= SITE_URL ?>/admin/appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a>
        <a href="<?= SITE_URL ?>/admin/services.php"><i class="fas fa-layer-group"></i>Services</a>
        <a href="<?= SITE_URL ?>/admin/messages.php"><i class="fas fa-envelope"></i>Messages</a>
        <a href="<?= SITE_URL ?>/index.php"><i class="fas fa-globe"></i>View Site</a>
        <a href="<?= SITE_URL ?>/auth/logout.php" style="color:#ff6b6b"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 style="font-family:'Playfair Display',serif; margin:0">Manage <span class="gold-text">Customers</span></h4>
        <span style="color:var(--text-muted); font-size:0.85rem"><?= $customers->num_rows ?> total</span>
    </div>

    <?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type']==='success'?'success':'danger' ?> alert-dismissible fade show">
        <?= $_SESSION['flash_msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_msg'],$_SESSION['flash_type']); endif; ?>

    <!-- Search -->
    <div class="glass-card p-3 mb-4">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-gold btn px-3"><i class="fas fa-search"></i></button>
            <?php if ($search): ?><a href="<?= SITE_URL ?>/admin/customers.php" class="btn btn-sm btn-outline-gold">Clear</a><?php endif; ?>
        </form>
    </div>

    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table dash-table">
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Appointments</th><th>Registered</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if ($customers->num_rows === 0): ?>
                    <tr><td colspan="6" class="text-center" style="color:var(--text-muted); padding:2rem">No customers found.</td></tr>
                    <?php else: $i=1; while ($cu = $customers->fetch_assoc()): ?>
                    <tr>
                        <td style="color:var(--text-muted)"><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:34px;height:34px;border-radius:50%;background:rgba(201,168,76,0.15);display:flex;align-items:center;justify-content:center;color:var(--gold);font-weight:600;font-size:0.9rem;border:1px solid var(--dark-border)">
                                    <?= strtoupper(substr($cu['name'],0,1)) ?>
                                </div>
                                <?= htmlspecialchars($cu['name']) ?>
                            </div>
                        </td>
                        <td style="color:var(--text-muted); font-size:0.88rem"><?= htmlspecialchars($cu['email']) ?></td>
                        <td>
                            <span class="badge-gold"><?= (int)$cu['appt_count'] ?></span>
                        </td>
                        <td style="color:var(--text-muted); font-size:0.82rem"><?= date('d M Y', strtotime($cu['created_at'])) ?></td>
                        <td>
                            <a href="customers.php?delete=<?= $cu['id'] ?>"
                               class="btn btn-sm badge-red border-0" title="Delete"
                               onclick="return confirm('Delete this customer and all their data?')">
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
