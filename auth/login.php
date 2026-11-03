<?php
// ============================================================
// auth/login.php — Customer / Admin Login
// ============================================================
require_once '../includes/config.php';
$pageTitle = 'Login';

if (isCustomer() || isAdmin()) redirect(SITE_URL . '/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($conn, $_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        if ($user['role'] === 'admin') {
            redirect(SITE_URL . '/admin/dashboard.php', 'Welcome back, Admin!');
        } else {
            redirect(SITE_URL . '/index.php', 'Welcome back, ' . $user['name'] . '!');
        }
    } else {
        $error = 'Invalid email or password. Please try again.';
    }
}
include '../includes/header.php';
?>
<section class="auth-section">
    <div class="container">
        <div class="auth-card glass-card" data-aos="zoom-in">
            <div class="text-center mb-4">
                <i class="fas fa-balance-scale gold-icon" style="font-size:2.5rem"></i>
                <h3 class="mt-2" style="font-family:'Playfair Display',serif">Welcome Back</h3>
                <p style="color:var(--text-muted);font-size:0.9rem">Sign in to your account</p>
            </div>
            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
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
                <button type="submit" class="btn-gold btn w-100 mb-3"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
                <div class="text-center" style="font-size:0.88rem;color:var(--text-muted)">
                    Don't have an account? <a href="<?= SITE_URL ?>/auth/register.php" class="gold-text ms-1">Register</a>
                </div>
                <div class="text-center mt-2" style="font-size:0.88rem;color:var(--text-muted)">
                    Are you a lawyer? <a href="<?= SITE_URL ?>/lawyer/login.php" class="gold-text ms-1">Lawyer Login</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
