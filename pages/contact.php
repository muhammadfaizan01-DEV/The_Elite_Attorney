<?php
// ============================================================
// contact.php — Contact Us Page
// ============================================================
require_once '../includes/config.php';
$pageTitle = 'Contact Us';

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($conn, $_POST['name']);
    $email   = clean($conn, $_POST['email']);
    $subject = clean($conn, $_POST['subject']);
    $message = clean($conn, $_POST['message']);

    if (!$name || !$email || !$message) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $ins = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)");
        $ins->bind_param('ssss', $name, $email, $subject, $message);
        if ($ins->execute()) {
            $success = true;
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}

include '../includes/header.php';
?>

<!-- Page Header -->
<div style="padding: 100px 0 60px; background: linear-gradient(135deg, #0d0d0d, #0a0800); border-bottom: 1px solid var(--dark-border);">
    <div class="container text-center" data-aos="fade-up">
        <h1 class="section-heading">Get In <span class="gold-text">Touch</span></h1>
        <div class="gold-divider"></div>
        <p style="color:var(--text-muted); max-width:500px; margin:0 auto">Have a question or need legal assistance? Reach out to us.</p>
    </div>
</div>

<section style="padding: 5rem 0; background: var(--dark-bg);">
    <div class="container">
        <div class="row g-5">

            <!-- Contact Info -->
            <div class="col-lg-5" data-aos="fade-right">
                <h3 style="font-family:'Playfair Display',serif; margin-bottom:2rem">
                    Contact <span class="gold-text">Information</span>
                </h3>

                <!-- Info Cards -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="glass-card p-4 d-flex gap-3 align-items-center">
                        <div style="width:48px;height:48px;background:rgba(201,168,76,0.1);border:1px solid var(--dark-border);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <div style="font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.2rem">Address</div>
                            <div>123 Justice Avenue, Saddar, Karachi</div>
                        </div>
                    </div>

                    <div class="glass-card p-4 d-flex gap-3 align-items-center">
                        <div style="width:48px;height:48px;background:rgba(201,168,76,0.1);border:1px solid var(--dark-border);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <div style="font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.2rem">Phone</div>
                            <div>+92 300 1234567</div>
                        </div>
                    </div>

                    <div class="glass-card p-4 d-flex gap-3 align-items-center">
                        <div style="width:48px;height:48px;background:rgba(201,168,76,0.1);border:1px solid var(--dark-border);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <div style="font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.2rem">Email</div>
                            <div>info@eliteattorney.com</div>
                        </div>
                    </div>

                    <div class="glass-card p-4 d-flex gap-3 align-items-center">
                        <div style="width:48px;height:48px;background:rgba(201,168,76,0.1);border:1px solid var(--dark-border);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div style="font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.2rem">Office Hours</div>
                            <div>Mon – Sat: 9:00 AM – 6:00 PM</div>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="social-links d-flex gap-3">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-7" data-aos="fade-left">
                <div class="glass-card p-4 p-md-5">
                    <h3 style="font-family:'Playfair Display',serif; margin-bottom:1.5rem">
                        Send a <span class="gold-text">Message</span>
                    </h3>

                    <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        Your message has been sent! We'll get back to you soon.
                    </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if (!$success): ?>
                    <form method="POST" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="name" class="form-control" required
                                       placeholder="Muhammad Ali"
                                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required
                                       placeholder="you@example.com"
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control"
                                       placeholder="How can we help you?"
                                       value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" rows="5" required
                                          placeholder="Describe your legal concern or inquiry..."><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn-gold btn px-5">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
