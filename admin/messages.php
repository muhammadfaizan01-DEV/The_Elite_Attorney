<?php
// ============================================================
// admin/messages.php — View Contact Messages
// ============================================================
require_once '../includes/config.php';
if (!isAdmin()) redirect(SITE_URL . '/auth/login.php', 'Admin access required.', 'error');

// Mark as read
if (isset($_GET['read'])) {
    $mid = (int)$_GET['read'];
    $conn->query("UPDATE contact_messages SET is_read = 1 WHERE id = $mid");
    redirect(SITE_URL . '/admin/messages.php');
}

// Delete message
if (isset($_GET['delete'])) {
    $mid = (int)$_GET['delete'];
    $conn->query("DELETE FROM contact_messages WHERE id = $mid");
    redirect(SITE_URL . '/admin/messages.php', 'Message deleted.', 'error');
}

// Mark all read
if (isset($_GET['readall'])) {
    $conn->query("UPDATE contact_messages SET is_read = 1");
    redirect(SITE_URL . '/admin/messages.php', 'All messages marked as read.');
}

$filter = isset($_GET['filter']) ? clean($conn, $_GET['filter']) : 'all';
$where  = $filter === 'unread' ? "WHERE is_read = 0" : "";

$messages = $conn->query("SELECT * FROM contact_messages $where ORDER BY created_at DESC");
$unreadCount = $conn->query("SELECT COUNT(*) AS c FROM contact_messages WHERE is_read = 0")->fetch_assoc()['c'];

$pageTitle = 'Contact Messages';
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
        <a href="<?= SITE_URL ?>/admin/appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a>
        <a href="<?= SITE_URL ?>/admin/services.php"><i class="fas fa-layer-group"></i>Services</a>
        <a href="<?= SITE_URL ?>/admin/messages.php" class="active"><i class="fas fa-envelope"></i>Messages
            <?php if ($unreadCount > 0): ?>
            <span class="badge-red ms-1"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/index.php"><i class="fas fa-globe"></i>View Site</a>
        <a href="<?= SITE_URL ?>/auth/logout.php" style="color:#ff6b6b"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 style="font-family:'Playfair Display',serif; margin:0">Contact <span class="gold-text">Messages</span></h4>
        <?php if ($unreadCount > 0): ?>
        <a href="messages.php?readall=1" class="btn-outline-gold btn btn-sm">
            <i class="fas fa-check-double me-1"></i>Mark All Read
        </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type']==='success'?'success':'danger' ?> alert-dismissible fade show">
        <?= $_SESSION['flash_msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_msg'],$_SESSION['flash_type']); endif; ?>

    <!-- Filter -->
    <div class="d-flex gap-2 mb-4">
        <a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-sm <?= $filter==='all'?'btn-gold':'btn-outline-gold' ?>">All Messages</a>
        <a href="messages.php?filter=unread" class="btn btn-sm <?= $filter==='unread'?'btn-gold':'btn-outline-gold' ?>">
            Unread (<?= $unreadCount ?>)
        </a>
    </div>

    <div class="row g-4">
        <?php if ($messages->num_rows === 0): ?>
        <div class="col-12">
            <div class="glass-card p-5 text-center">
                <i class="fas fa-inbox fa-3x mb-3" style="color:var(--text-muted)"></i>
                <p style="color:var(--text-muted)">No messages found.</p>
            </div>
        </div>
        <?php else: while ($msg = $messages->fetch_assoc()): ?>
        <div class="col-lg-6">
            <div class="glass-card p-4 <?= !$msg['is_read'] ? 'border-gold' : '' ?>"
                 style="<?= !$msg['is_read'] ? 'border-color: rgba(201,168,76,0.5)!important' : '' ?>">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong><?= htmlspecialchars($msg['name']) ?></strong>
                        <?php if (!$msg['is_read']): ?>
                        <span class="badge-gold ms-2" style="font-size:0.7rem">NEW</span>
                        <?php endif; ?>
                        <div style="color:var(--text-muted); font-size:0.83rem"><?= htmlspecialchars($msg['email']) ?></div>
                    </div>
                    <small style="color:var(--text-muted); font-size:0.78rem; white-space:nowrap">
                        <?= date('d M Y, h:i A', strtotime($msg['created_at'])) ?>
                    </small>
                </div>

                <?php if ($msg['subject']): ?>
                <div style="color:var(--gold); font-size:0.88rem; font-weight:600; margin-bottom:0.5rem">
                    <i class="fas fa-tag me-1"></i><?= htmlspecialchars($msg['subject']) ?>
                </div>
                <?php endif; ?>

                <p style="color:var(--text-muted); font-size:0.88rem; line-height:1.7; margin-bottom:1rem">
                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                </p>

                <div class="d-flex gap-2">
                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject']) ?>"
                       class="btn-gold btn btn-sm px-3">
                        <i class="fas fa-reply me-1"></i>Reply
                    </a>
                    <?php if (!$msg['is_read']): ?>
                    <a href="messages.php?read=<?= $msg['id'] ?>" class="btn btn-sm badge-muted border-0">
                        <i class="fas fa-check me-1"></i>Mark Read
                    </a>
                    <?php endif; ?>
                    <a href="messages.php?delete=<?= $msg['id'] ?>"
                       class="btn btn-sm badge-red border-0 ms-auto"
                       onclick="return confirm('Delete this message?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
