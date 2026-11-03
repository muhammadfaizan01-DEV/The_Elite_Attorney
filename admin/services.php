<?php
// ============================================================
// admin/services.php — Manage Practice Area Services
// ============================================================
require_once '../includes/config.php';
if (!isAdmin()) redirect(SITE_URL . '/auth/login.php', 'Admin access required.', 'error');

// Delete service
if (isset($_GET['delete'])) {
    $sid = (int)$_GET['delete'];
    $conn->query("DELETE FROM services WHERE id = $sid");
    redirect(SITE_URL . '/admin/services.php', 'Service deleted.', 'error');
}

// Toggle active
if (isset($_GET['toggle'])) {
    $sid = (int)$_GET['toggle'];
    $conn->query("UPDATE services SET is_active = 1 - is_active WHERE id = $sid");
    redirect(SITE_URL . '/admin/services.php', 'Service status updated.');
}

$error   = '';
$editSvc = null;

// Load service for editing
if (isset($_GET['edit'])) {
    $sid     = (int)$_GET['edit'];
    $editSvc = $conn->query("SELECT * FROM services WHERE id = $sid")->fetch_assoc();
}

// Add or Update service
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = clean($conn, $_POST['name']);
    $description = clean($conn, $_POST['description']);
    $icon        = clean($conn, $_POST['icon']);

    if (!$name) {
        $error = 'Service name is required.';
    } else {
        if (isset($_POST['service_id']) && (int)$_POST['service_id'] > 0) {
            // Update
            $sid = (int)$_POST['service_id'];
            $conn->query("UPDATE services SET name='$name', description='$description', icon='$icon' WHERE id = $sid");
            redirect(SITE_URL . '/admin/services.php', 'Service updated successfully!');
        } else {
            // Insert
            $conn->query("INSERT INTO services (name, description, icon) VALUES ('$name','$description','$icon')");
            redirect(SITE_URL . '/admin/services.php', 'New service added!');
        }
    }
}

$services = $conn->query("SELECT * FROM services ORDER BY created_at DESC");
$pageTitle = 'Manage Services';
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
        <a href="<?= SITE_URL ?>/admin/services.php" class="active"><i class="fas fa-layer-group"></i>Services</a>
        <a href="<?= SITE_URL ?>/admin/messages.php"><i class="fas fa-envelope"></i>Messages</a>
        <a href="<?= SITE_URL ?>/index.php"><i class="fas fa-globe"></i>View Site</a>
        <a href="<?= SITE_URL ?>/auth/logout.php" style="color:#ff6b6b"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>

<div class="main-content">
    <h4 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem">
        Manage <span class="gold-text">Services</span>
    </h4>

    <?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type']==='success'?'success':'danger' ?> alert-dismissible fade show">
        <?= $_SESSION['flash_msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_msg'],$_SESSION['flash_type']); endif; ?>

    <div class="row g-4">

        <!-- Add / Edit Form -->
        <div class="col-lg-4">
            <div class="glass-card p-4">
                <h5 class="mb-4"><?= $editSvc ? '<span class="gold-text">Edit</span>' : '<span class="gold-text">Add</span>' ?> Service</h5>

                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                <form method="POST">
                    <?php if ($editSvc): ?>
                    <input type="hidden" name="service_id" value="<?= $editSvc['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Service Name *</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= $editSvc ? htmlspecialchars($editSvc['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '') ?>"
                               placeholder="e.g. Corporate Law">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description..."><?= $editSvc ? htmlspecialchars($editSvc['description']) : '' ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">FontAwesome Icon Class</label>
                        <input type="text" name="icon" class="form-control"
                               value="<?= $editSvc ? htmlspecialchars($editSvc['icon']) : 'fa-balance-scale' ?>"
                               placeholder="fa-gavel">
                        <small style="color:var(--text-muted); font-size:0.78rem">
                            e.g. fa-gavel, fa-home, fa-briefcase
                        </small>
                    </div>

                    <button type="submit" class="btn-gold btn w-100">
                        <i class="fas fa-<?= $editSvc ? 'save' : 'plus' ?> me-2"></i>
                        <?= $editSvc ? 'Update Service' : 'Add Service' ?>
                    </button>

                    <?php if ($editSvc): ?>
                    <a href="<?= SITE_URL ?>/admin/services.php" class="btn-outline-gold btn w-100 mt-2">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Services List -->
        <div class="col-lg-8">
            <div class="glass-card p-4">
                <div class="table-responsive">
                    <table class="table dash-table">
                        <thead>
                            <tr><th>#</th><th>Icon</th><th>Name</th><th>Description</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($services->num_rows === 0): ?>
                            <tr><td colspan="6" class="text-center" style="color:var(--text-muted)">No services found.</td></tr>
                            <?php else: $i=1; while ($svc = $services->fetch_assoc()): ?>
                            <tr>
                                <td style="color:var(--text-muted)"><?= $i++ ?></td>
                                <td>
                                    <div style="width:36px;height:36px;background:rgba(201,168,76,0.1);border:1px solid var(--dark-border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--gold)">
                                        <i class="fas <?= htmlspecialchars($svc['icon']) ?>"></i>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($svc['name']) ?></td>
                                <td style="color:var(--text-muted); font-size:0.83rem; max-width:200px">
                                    <?= htmlspecialchars(substr($svc['description'],0,60)) ?><?= strlen($svc['description'])>60?'...':'' ?>
                                </td>
                                <td>
                                    <?= $svc['is_active'] ? '<span class="badge-green">Active</span>' : '<span class="badge-red">Inactive</span>' ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="services.php?edit=<?= $svc['id'] ?>" class="btn btn-sm badge-gold border-0" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="services.php?toggle=<?= $svc['id'] ?>" class="btn btn-sm badge-muted border-0" title="Toggle">
                                            <i class="fas fa-<?= $svc['is_active']?'eye-slash':'eye' ?>"></i>
                                        </a>
                                        <a href="services.php?delete=<?= $svc['id'] ?>" class="btn btn-sm badge-red border-0" title="Delete"
                                           onclick="return confirm('Delete this service?')">
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
