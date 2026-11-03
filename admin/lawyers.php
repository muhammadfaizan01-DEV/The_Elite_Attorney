<?php
// ============================================================
// admin/lawyers.php — Manage All Lawyers
// ============================================================
require_once '../includes/config.php';
if (!isAdmin()) redirect(SITE_URL . '/auth/login.php', 'Admin access required.', 'error');

// --- Handle Actions ---
// Approve lawyer
if (isset($_GET['approve'])) {
    $lid = (int)$_GET['approve'];
    $conn->query("UPDATE lawyers SET is_approved = 1 WHERE id = $lid");
    redirect(SITE_URL . '/admin/lawyers.php', 'Lawyer approved successfully!');
}

// Reject / Delete lawyer
if (isset($_GET['delete'])) {
    $lid = (int)$_GET['delete'];
    // Also delete their photo if not default
    $lw = $conn->query("SELECT photo FROM lawyers WHERE id = $lid")->fetch_assoc();
    if ($lw && $lw['photo'] !== 'default.jpg') {
        @unlink(UPLOAD_DIR . $lw['photo']);
    }
    $conn->query("DELETE FROM lawyers WHERE id = $lid");
    redirect(SITE_URL . '/admin/lawyers.php', 'Lawyer removed.', 'error');
}

// Toggle active/inactive
if (isset($_GET['toggle'])) {
    $lid = (int)$_GET['toggle'];
    $conn->query("UPDATE lawyers SET is_active = 1 - is_active WHERE id = $lid");
    redirect(SITE_URL . '/admin/lawyers.php', 'Lawyer status updated.');
}

// --- Filter ---
$filter = isset($_GET['filter']) ? clean($conn, $_GET['filter']) : 'all';
$search = isset($_GET['search']) ? clean($conn, $_GET['search']) : '';

$where = "WHERE 1=1";
if ($filter === 'pending')  $where .= " AND is_approved = 0";
if ($filter === 'approved') $where .= " AND is_approved = 1";
if ($filter === 'inactive') $where .= " AND is_active = 0";
if ($search) $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR specialization LIKE '%$search%')";

$lawyers = $conn->query("SELECT * FROM lawyers $where ORDER BY created_at DESC");

// Counts for tabs
$allCount      = $conn->query("SELECT COUNT(*) AS c FROM lawyers")->fetch_assoc()['c'];
$pendingCount  = $conn->query("SELECT COUNT(*) AS c FROM lawyers WHERE is_approved = 0")->fetch_assoc()['c'];
$approvedCount = $conn->query("SELECT COUNT(*) AS c FROM lawyers WHERE is_approved = 1")->fetch_assoc()['c'];

$pageTitle = 'Manage Lawyers';
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

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-cog gold-icon me-2"></i>
        <span class="gold-text">Admin</span> Panel
    </div>
    <nav>
        <a href="<?= SITE_URL ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="<?= SITE_URL ?>/admin/lawyers.php" class="active"><i class="fas fa-gavel"></i>Manage Lawyers</a>
        <a href="<?= SITE_URL ?>/admin/customers.php"><i class="fas fa-users"></i>Customers</a>
        <a href="<?= SITE_URL ?>/admin/appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a>
        <a href="<?= SITE_URL ?>/admin/services.php"><i class="fas fa-layer-group"></i>Services</a>
        <a href="<?= SITE_URL ?>/admin/messages.php"><i class="fas fa-envelope"></i>Messages</a>
        <a href="<?= SITE_URL ?>/index.php"><i class="fas fa-globe"></i>View Site</a>
        <a href="<?= SITE_URL ?>/auth/logout.php" style="color:#ff6b6b"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>

<div class="main-content">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 style="font-family:'Playfair Display',serif; margin:0">
            Manage <span class="gold-text">Lawyers</span>
        </h4>
    </div>

    <!-- Flash -->
    <?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type']==='success'?'success':'danger' ?> alert-dismissible fade show">
        <?= $_SESSION['flash_msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_msg'],$_SESSION['flash_type']); endif; ?>

    <!-- Filter Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="<?= SITE_URL ?>/admin/lawyers.php" class="btn btn-sm <?= $filter==='all'?'btn-gold':'btn-outline-gold' ?>">All (<?= $allCount ?>)</a>
        <a href="<?= SITE_URL ?>/admin/lawyers.php?filter=pending"  class="btn btn-sm <?= $filter==='pending'?'btn-gold':'btn-outline-gold' ?>">Pending (<?= $pendingCount ?>)</a>
        <a href="lawyers.php?filter=approved" class="btn btn-sm <?= $filter==='approved'?'btn-gold':'btn-outline-gold' ?>">Approved (<?= $approvedCount ?>)</a>
    </div>

    <!-- Search -->
    <div class="glass-card p-3 mb-4">
        <form method="GET" class="d-flex gap-2">
            <?php if ($filter): ?><input type="hidden" name="filter" value="<?= $filter ?>"> <?php endif; ?>
            <input type="text" name="search" class="form-control" placeholder="Search by name, email or specialization..."
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-gold btn px-3"><i class="fas fa-search"></i></button>
            <?php if ($search): ?>
            <a href="lawyers.php<?= $filter?'?filter='.$filter:'' ?>" class="btn btn-sm btn-outline-gold">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Lawyers Table -->
    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lawyer</th>
                        <th>Specialization</th>
                        <th>City</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($lawyers->num_rows === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="color:var(--text-muted); padding:2rem">
                            No lawyers found.
                        </td>
                    </tr>
                    <?php else: $i=1; while ($lw = $lawyers->fetch_assoc()): ?>
                    <tr>
                        <td style="color:var(--text-muted)"><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= SITE_URL ?>/uploads/photos/<?= htmlspecialchars($lw['photo']) ?>"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($lw['name']) ?>&background=C9A84C&color=000&size=80'"
                                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid var(--gold)" alt="">
                                <div>
                                    <div><?= htmlspecialchars($lw['name']) ?></div>
                                    <small style="color:var(--text-muted)"><?= htmlspecialchars($lw['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td style="color:var(--text-muted); font-size:0.85rem"><?= htmlspecialchars($lw['specialization'] ?? '—') ?></td>
                        <td style="color:var(--text-muted); font-size:0.85rem"><?= htmlspecialchars($lw['city'] ?? '—') ?></td>
                        <td style="color:var(--text-muted); font-size:0.85rem"><?= (int)$lw['experience'] ?> yrs</td>
                        <td>
                            <?php if (!$lw['is_approved']): ?>
                                <span class="badge-gold">Pending</span>
                            <?php elseif (!$lw['is_active']): ?>
                                <span class="badge-red">Inactive</span>
                            <?php else: ?>
                                <span class="badge-green">Active</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--text-muted); font-size:0.82rem"><?= date('d M Y', strtotime($lw['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <!-- View profile -->
                                <a href="<?= SITE_URL ?>/pages/lawyer_profile.php?id=<?= $lw['id'] ?>" target="_blank"
                                   class="btn btn-sm badge-muted border-0" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Approve (if pending) -->
                                <?php if (!$lw['is_approved']): ?>
                                <a href="<?= SITE_URL ?>/admin/lawyers.php?approve=<?= $lw['id'] ?>"
                                   class="btn btn-sm badge-green border-0" title="Approve"
                                   onclick="return confirm('Approve this lawyer?')">
                                    <i class="fas fa-check"></i>
                                </a>
                                <?php endif; ?>

                                <!-- Toggle active -->
                                <a href="lawyers.php?toggle=<?= $lw['id'] ?>"
                                   class="btn btn-sm <?= $lw['is_active'] ? 'badge-gold' : 'badge-muted' ?> border-0"
                                   title="<?= $lw['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                    <i class="fas fa-<?= $lw['is_active'] ? 'ban' : 'check-circle' ?>"></i>
                                </a>

                                <!-- Delete -->
                                <a href="lawyers.php?delete=<?= $lw['id'] ?>"
                                   class="btn btn-sm badge-red border-0" title="Delete"
                                   onclick="return confirm('Delete this lawyer permanently? This cannot be undone.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
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
