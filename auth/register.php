<?php
// ============================================================
// auth/register.php — Customer Registration
// ============================================================
require_once '../includes/config.php';
$pageTitle = 'Register';

if (isCustomer()) redirect(SITE_URL . '/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($conn, $_POST['name']);
    $email   = clean($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (strlen($name) < 3)                          { $error = 'Name must be at least 3 characters.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Please enter a valid email address.'; }
    elseif (strlen($password) < 6)                  { $error = 'Password must be at least 6 characters.'; }
    elseif ($password !== $confirm)                 { $error = 'Passwords do not match.'; }
    else {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param('s', $email); $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,'customer')");
            $ins->bind_param('sss', $name, $email, $hash);
            if ($ins->execute()) redirect(SITE_URL . '/auth/login.php', 'Account created! Please login.');
            else $error = 'Registration failed. Please try again.';
        }
    }
}
include '../includes/header.php';
?>
<section class="auth-section">
    <div class="container">
        <div class="auth-card glass-card" style="max-width:520px" data-aos="zoom-in">
            <div class="text-center mb-4">
                <i class="fas fa-user-plus gold-icon" style="font-size:2.5rem"></i>
                <h3 class="mt-2" style="font-family:'Playfair Display',serif">Create Account</h3>
                <p style="color:var(--text-muted);font-size:0.9rem">Join as a customer to book consultations</p>
            </div>
            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <form method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Muhammad Ali" required
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                </div>
                <button type="submit" class="btn-gold btn w-100 mb-3"><i class="fas fa-user-plus me-2"></i>Create Account</button>
                <div class="text-center" style="font-size:0.88rem;color:var(--text-muted)">
                    Already have an account? <a href="<?= SITE_URL ?>/auth/login.php" class="gold-text ms-1">Login</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
