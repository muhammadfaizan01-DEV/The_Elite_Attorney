<?php
// ============================================================
// lawyer/register.php — Lawyer Registration
// ============================================================
require_once '../includes/config.php';
$pageTitle = 'Join as Attorney';

if (isLawyer()) redirect('../index.php');

$error = '';

// Load services for checkboxes
$services = $conn->query("SELECT id, name FROM services WHERE is_active = 1");
$servicesList = [];
while ($s = $services->fetch_assoc()) $servicesList[] = $s;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = clean($conn, $_POST['name']);
    $email          = clean($conn, $_POST['email']);
    $password       = $_POST['password'];
    $confirm        = $_POST['confirm_password'];
    $phone          = clean($conn, $_POST['phone']);
    $specialization = clean($conn, $_POST['specialization']);
    $experience     = (int)$_POST['experience'];
    $fee            = (float)$_POST['fee'];
    $bar_number     = clean($conn, $_POST['bar_number']);
    $city           = clean($conn, $_POST['city']);
    $bio            = clean($conn, $_POST['bio']);
    $selectedSvcs   = isset($_POST['services']) ? $_POST['services'] : [];

    if (!$name || !$email || !$password) {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check duplicate
        $chk = $conn->prepare("SELECT id FROM lawyers WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'This email is already registered.';
        } else {
            // Handle photo upload
            $photoName = 'default.jpg';
            if (!empty($_FILES['photo']['name'])) {
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $photoName = uniqid('lawyer_') . '.' . $ext;
                    move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_DIR . $photoName);
                }
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $conn->prepare("
                INSERT INTO lawyers (name, email, password, phone, specialization, experience, fee, bar_number, city, bio, photo)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)
            ");
            $ins->bind_param('sssssidssss', $name, $email, $hash, $phone, $specialization, $experience, $fee, $bar_number, $city, $bio, $photoName);

            if ($ins->execute()) {
                $lawyerId = $conn->insert_id;
                // Insert services
                foreach ($selectedSvcs as $svcId) {
                    $svcId = (int)$svcId;
                    $conn->query("INSERT INTO lawyer_services (lawyer_id, service_id) VALUES ($lawyerId, $svcId)");
                }
                redirect(SITE_URL . '/auth/login.php', 'Registration submitted! Your profile is pending admin approval.');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include '../includes/header.php';
?>

<section class="auth-section" style="padding-top: 110px; padding-bottom: 60px;">
    <div class="container">
        <div class="glass-card" style="max-width:700px; margin:auto; padding:2.5rem" data-aos="zoom-in">

            <div class="text-center mb-4">
                <i class="fas fa-gavel gold-icon" style="font-size:2.5rem"></i>
                <h3 class="mt-2" style="font-family:'Playfair Display',serif">Join as Attorney</h3>
                <p style="color:var(--text-muted); font-size:0.9rem">Your profile will be reviewed and approved by our admin</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" novalidate>
                <div class="row g-3">

                    <!-- Basic Info -->
                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="Adv. Muhammad Ali"
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required placeholder="lawyer@example.com"
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required placeholder="Min. 6 characters">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter password">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+92 300 1234567"
                               value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" placeholder="Karachi"
                               value="<?= isset($_POST['city']) ? htmlspecialchars($_POST['city']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Primary Specialization</label>
                        <input type="text" name="specialization" class="form-control" placeholder="e.g. Criminal Law"
                               value="<?= isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Years of Experience</label>
                        <input type="number" name="experience" class="form-control" min="0" max="60" placeholder="5"
                               value="<?= isset($_POST['experience']) ? (int)$_POST['experience'] : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Consultation Fee (Rs.)</label>
                        <input type="number" name="fee" class="form-control" min="0" placeholder="2500"
                               value="<?= isset($_POST['fee']) ? (float)$_POST['fee'] : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bar Registration Number</label>
                        <input type="text" name="bar_number" class="form-control" placeholder="KHC-12345"
                               value="<?= isset($_POST['bar_number']) ? htmlspecialchars($_POST['bar_number']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Professional Bio</label>
                        <textarea name="bio" class="form-control" rows="4" placeholder="Write a short professional biography..."><?= isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : '' ?></textarea>
                    </div>

                    <!-- Services -->
                    <div class="col-12">
                        <label class="form-label">Practice Areas (select all that apply)</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($servicesList as $svc): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="services[]"
                                       value="<?= $svc['id'] ?>" id="svc<?= $svc['id'] ?>"
                                       <?= (isset($_POST['services']) && in_array($svc['id'], $_POST['services'])) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="svc<?= $svc['id'] ?>" style="color:var(--text-muted); font-size:0.88rem">
                                    <?= htmlspecialchars($svc['name']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <button type="submit" class="btn-gold btn w-100">
                            <i class="fas fa-paper-plane me-2"></i>Submit Application
                        </button>
                        <p class="text-center mt-3" style="color:var(--text-muted); font-size:0.85rem">
                            Already registered? <a href="<?= SITE_URL ?>/lawyer/login.php" class="gold-text">Lawyer Login</a>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
