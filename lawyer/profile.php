<?php
// ============================================================
// lawyer/profile.php — Lawyer Edit Profile
// ============================================================
require_once '../includes/config.php';
if (!isLawyer()) redirect(SITE_URL . '/lawyer/login.php', 'Please login.', 'error');

$lawyerId = $_SESSION['lawyer_id'];

$stmt = $conn->prepare("SELECT * FROM lawyers WHERE id = ?");
$stmt->bind_param('i', $lawyerId);
$stmt->execute();
$lawyer = $stmt->get_result()->fetch_assoc();

$services = $conn->query("SELECT id, name FROM services WHERE is_active = 1");
$servicesList = [];
while ($s = $services->fetch_assoc()) $servicesList[] = $s;

$myServices = [];
$mySvcResult = $conn->query("SELECT service_id FROM lawyer_services WHERE lawyer_id = $lawyerId");
while ($ms = $mySvcResult->fetch_assoc()) $myServices[] = $ms['service_id'];

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone          = clean($conn, $_POST['phone']);
    $specialization = clean($conn, $_POST['specialization']);
    $experience     = (int)$_POST['experience'];
    $fee            = (float)$_POST['fee'];
    $bar_number     = clean($conn, $_POST['bar_number']);
    $city           = clean($conn, $_POST['city']);
    $bio            = clean($conn, $_POST['bio']);
    $selectedSvcs   = isset($_POST['services']) ? $_POST['services'] : [];

    // Handle photo
    $photoName = $lawyer['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $photoName = uniqid('lawyer_') . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_DIR . $photoName);
        }
    }

    // Handle password change
    $passwordUpdate = '';
    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = 'Passwords do not match.';
        } else {
            $newHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $passwordUpdate = ", password='$newHash'";
        }
    }

    if (!$error) {
        $conn->query("
            UPDATE lawyers SET
                phone='$phone', specialization='$specialization', experience=$experience,
                fee=$fee, bar_number='$bar_number', city='$city', bio='$bio', photo='$photoName'
                $passwordUpdate
            WHERE id=$lawyerId
        ");

        // Update services
        $conn->query("DELETE FROM lawyer_services WHERE lawyer_id = $lawyerId");
        foreach ($selectedSvcs as $svcId) {
            $svcId = (int)$svcId;
            $conn->query("INSERT INTO lawyer_services (lawyer_id, service_id) VALUES ($lawyerId, $svcId)");
        }

        redirect(SITE_URL . '/lawyer/profile.php', 'Profile updated successfully!');
    }
}

$pageTitle = 'Edit Profile';
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
        <i class="fas fa-balance-scale gold-icon me-2"></i>
        <span class="gold-text">Elite</span> Attorney
    </div>
    <nav>
        <a href="<?= SITE_URL ?>/lawyer/dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="<?= SITE_URL ?>/lawyer/appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a>
        <a href="<?= SITE_URL ?>/lawyer/profile.php" class="active"><i class="fas fa-user-edit"></i>Edit Profile</a>
        <a href="<?= SITE_URL ?>/pages/lawyer_profile.php?id=<?= $lawyerId ?>"><i class="fas fa-eye"></i>View My Profile</a>
        <a href="<?= SITE_URL ?>/auth/logout.php" style="color:#ff6b6b"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>

<div class="main-content">
    <h4 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem">
        Edit <span class="gold-text">Profile</span>
    </h4>

    <?php if (!empty($_SESSION['flash_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['flash_msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_msg'],$_SESSION['flash_type']); endif; ?>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="glass-card p-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($lawyer['phone']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($lawyer['city']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Specialization</label>
                    <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($lawyer['specialization']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Experience (years)</label>
                    <input type="number" name="experience" class="form-control" min="0" value="<?= (int)$lawyer['experience'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Consultation Fee (Rs.)</label>
                    <input type="number" name="fee" class="form-control" min="0" value="<?= (float)$lawyer['fee'] ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bar Registration Number</label>
                    <input type="text" name="bar_number" class="form-control" value="<?= htmlspecialchars($lawyer['bar_number']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Update Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($lawyer['bio']) ?></textarea>
                </div>

                <!-- Services -->
                <div class="col-12">
                    <label class="form-label">Practice Areas</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($servicesList as $svc): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="services[]"
                                   value="<?= $svc['id'] ?>" id="esvc<?= $svc['id'] ?>"
                                   <?= in_array($svc['id'], $myServices) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="esvc<?= $svc['id'] ?>" style="color:var(--text-muted); font-size:0.88rem">
                                <?= htmlspecialchars($svc['name']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Password Change -->
                <div class="col-12 mt-2">
                    <h6 class="gold-text mb-3">Change Password (optional)</h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter new password">
                </div>

                <div class="col-12 mt-2">
                    <button type="submit" class="btn-gold btn px-4">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
