<?php
// ============================================================
// lawyer/login.php — Lawyer Login
// ============================================================
require_once '../includes/config.php';
$pageTitle = 'Lawyer Login';

if (isLawyer()) redirect(SITE_URL . '/index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($conn, $_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, email, password, is_approved FROM lawyers WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $lawyer = $stmt->get_result()->fetch_assoc();

    if ($lawyer && password_verify($password, $lawyer['password'])) {
        if (!$lawyer['is_approved']) {
            $error = 'Your account is pending admin approval. Please check back later.';
        } else {
            $_SESSION['lawyer_id']   = $lawyer['id'];
            $_SESSION['lawyer_name'] = $lawyer['name'];
            redirect(SITE_URL . '/lawyer/dashboard.php', 'Welcome back, ' . $lawyer['name'] . '!');
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

include '../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-card glass-card" data-aos="zoom-in">

            <div class="text-center mb-4">
                <i class="fas fa-gavel gold-icon" style="font-size:2.5rem"></i>
                <h3 class="mt-2" style="font-family:'Playfair Display',serif">Attorney Login</h3>
                <p style="color:var(--text-muted); font-size:0.9rem">Access your lawyer dashboard</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-gold btn w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
                <div class="text-center" style="font-size:0.88rem; color:var(--text-muted)">
                    Not registered yet? <a href="<?= SITE_URL ?>/lawyer/register.php" class="gold-text ms-1">Join as Attorney</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
