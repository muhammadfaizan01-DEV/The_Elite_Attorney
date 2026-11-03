<?php
// ============================================================
// lawyer_profile.php — Public Lawyer Profile & Appointment Booking
// ============================================================
require_once '../includes/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect(SITE_URL . '/pages/lawyers.php');

// Fetch lawyer
$stmt = $conn->prepare("SELECT * FROM lawyers WHERE id = ? AND is_approved = 1 AND is_active = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$lawyer = $stmt->get_result()->fetch_assoc();
if (!$lawyer) redirect(SITE_URL . '/pages/lawyers.php');

$pageTitle = $lawyer['name'];

// Services
$services = $conn->query("
    SELECT s.name, s.icon FROM services s
    JOIN lawyer_services ls ON ls.service_id = s.id
    WHERE ls.lawyer_id = $id
");

// Reviews
$reviews = $conn->query("
    SELECT r.rating, r.comment, r.created_at, u.name AS customer_name
    FROM reviews r
    JOIN users u ON u.id = r.customer_id
    WHERE r.lawyer_id = $id
    ORDER BY r.created_at DESC LIMIT 10
");

// --- Handle appointment booking ---
$bookingMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    if (!isCustomer()) {
        redirect(SITE_URL . '/auth/login.php', 'Please login as a customer to book an appointment.', 'error');
    }

    $apptDate = clean($conn, $_POST['appt_date']);
    $apptTime = clean($conn, $_POST['appt_time']);
    $message  = clean($conn, $_POST['message']);
    $custId   = $_SESSION['user_id'];

    // Basic validation
    if ($apptDate && $apptTime) {
        $ins = $conn->prepare("INSERT INTO appointments (customer_id, lawyer_id, appt_date, appt_time, message) VALUES (?,?,?,?,?)");
        $ins->bind_param('iisss', $custId, $id, $apptDate, $apptTime, $message);
        if ($ins->execute()) {
            $bookingMsg = 'success';
        } else {
            $bookingMsg = 'error';
        }
    } else {
        $bookingMsg = 'missing';
    }
}

// --- Handle review submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isCustomer()) {
        redirect(SITE_URL . '/auth/login.php', 'Please login to leave a review.', 'error');
    }
    $rating  = (int)$_POST['rating'];
    $comment = clean($conn, $_POST['comment']);
    $custId  = $_SESSION['user_id'];
    if ($rating >= 1 && $rating <= 5) {
        $ins = $conn->prepare("INSERT INTO reviews (customer_id, lawyer_id, rating, comment) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)");
        $ins->bind_param('iiis', $custId, $id, $rating, $comment);
        $ins->execute();
        // Update average rating on lawyer
        $conn->query("UPDATE lawyers SET rating = (SELECT AVG(rating) FROM reviews WHERE lawyer_id = $id) WHERE id = $id");
        redirect(SITE_URL . "/pages/lawyer_profile.php?id=$id", 'Review submitted. Thank you!');
    }
}

include '../includes/header.php';
?>

<!-- Profile Hero -->
<section class="profile-hero">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-auto text-center text-md-start" data-aos="fade-right">
                <img src="<?= SITE_URL ?>/uploads/photos/<?= htmlspecialchars($lawyer['photo']) ?>"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($lawyer['name']) ?>&background=C9A84C&color=000&size=280'"
                     alt="<?= htmlspecialchars($lawyer['name']) ?>"
                     class="profile-photo">
            </div>
            <div class="col-md" data-aos="fade-left">
                <h1 class="section-heading mb-1"><?= htmlspecialchars($lawyer['name']) ?></h1>
                <div class="specialization-badge mb-2"><?= htmlspecialchars($lawyer['specialization'] ?? 'Attorney at Law') ?></div>
                <div class="lawyer-meta d-flex flex-wrap gap-3 mb-3">
                    <span><i class="fas fa-briefcase me-1"></i><?= (int)$lawyer['experience'] ?> Years Exp.</span>
                    <span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($lawyer['city'] ?? 'N/A') ?></span>
                    <span><i class="fas fa-tag me-1"></i>Rs. <?= number_format($lawyer['fee'], 0) ?></span>
                    <?php if ($lawyer['bar_number']): ?>
                    <span><i class="fas fa-id-badge me-1"></i>Bar #<?= htmlspecialchars($lawyer['bar_number']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="stars mb-3">
                    <?php for ($i=1;$i<=5;$i++) echo $i<=round($lawyer['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                    <span class="text-muted ms-2"><?= number_format($lawyer['rating'],1) ?> / 5</span>
                </div>
                <a href="#book" class="btn-gold btn me-2"><i class="fas fa-calendar-plus me-2"></i>Book Appointment</a>
                <a href="tel:<?= htmlspecialchars($lawyer['phone']) ?>" class="btn-outline-gold btn"><i class="fas fa-phone me-2"></i>Call</a>
            </div>
        </div>
    </div>
</section>

<section style="padding: 4rem 0; background: var(--dark-bg);">
    <div class="container">
        <div class="row g-4">

            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Bio -->
                <?php if ($lawyer['bio']): ?>
                <div class="glass-card p-4 mb-4" data-aos="fade-up">
                    <h4 class="mb-3">About <span class="gold-text"><?= explode(' ', $lawyer['name'])[0] ?></span></h4>
                    <p style="color:var(--text-muted); line-height:1.9;"><?= nl2br(htmlspecialchars($lawyer['bio'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Services -->
                <?php if ($services->num_rows > 0): ?>
                <div class="glass-card p-4 mb-4" data-aos="fade-up">
                    <h4 class="mb-3">Practice <span class="gold-text">Areas</span></h4>
                    <div class="d-flex flex-wrap gap-2">
                        <?php while ($svc = $services->fetch_assoc()): ?>
                        <span class="specialization-badge">
                            <i class="fas <?= htmlspecialchars($svc['icon']) ?> me-1"></i>
                            <?= htmlspecialchars($svc['name']) ?>
                        </span>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Reviews -->
                <div class="glass-card p-4 mb-4" data-aos="fade-up">
                    <h4 class="mb-3">Client <span class="gold-text">Reviews</span></h4>
                    <?php if ($reviews->num_rows === 0): ?>
                        <p style="color:var(--text-muted)">No reviews yet. Be the first to review!</p>
                    <?php else: while ($rev = $reviews->fetch_assoc()): ?>
                    <div class="mb-3 pb-3" style="border-bottom:1px solid var(--dark-border)">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong><?= htmlspecialchars($rev['customer_name']) ?></strong>
                            <small style="color:var(--text-muted)"><?= date('d M Y', strtotime($rev['created_at'])) ?></small>
                        </div>
                        <div class="stars mb-1">
                            <?php for ($i=1;$i<=5;$i++) echo $i<=$rev['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                        </div>
                        <p style="color:var(--text-muted); font-size:0.9rem; margin:0"><?= htmlspecialchars($rev['comment']) ?></p>
                    </div>
                    <?php endwhile; endif; ?>

                    <!-- Submit Review -->
                    <?php if (isCustomer()): ?>
                    <h6 class="mt-4 mb-3 gold-text">Leave a Review</h6>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select" required>
                                <option value="">Select Rating</option>
                                <?php for ($i=5;$i>=1;$i--): ?>
                                <option value="<?=$i?>"><?=$i?> Star<?=$i>1?'s':''?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Review</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn-gold btn">Submit Review</button>
                    </form>
                    <?php else: ?>
                    <p style="color:var(--text-muted); font-size:0.88rem; margin-top:1rem">
                        <a href="<?= SITE_URL ?>/auth/login.php" class="gold-text">Login</a> to leave a review.
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column — Booking -->
            <div class="col-lg-4">
                <div class="glass-card p-4 sticky-top" style="top: 90px;" data-aos="fade-left" id="book">
                    <h5 class="mb-3">Book <span class="gold-text">Appointment</span></h5>

                    <?php if ($bookingMsg === 'success'): ?>
                    <div class="alert alert-success">Appointment booked! The lawyer will confirm soon.</div>
                    <?php elseif ($bookingMsg === 'error'): ?>
                    <div class="alert alert-danger">Something went wrong. Please try again.</div>
                    <?php elseif ($bookingMsg === 'missing'): ?>
                    <div class="alert alert-warning">Please fill all required fields.</div>
                    <?php endif; ?>

                    <?php if (isCustomer()): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Preferred Date *</label>
                            <input type="date" name="appt_date" class="form-control" required
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preferred Time *</label>
                            <input type="time" name="appt_time" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Brief about your case</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Describe your legal issue briefly..."></textarea>
                        </div>
                        <button type="submit" name="book_appointment" class="btn-gold btn w-100">
                            <i class="fas fa-calendar-plus me-2"></i>Request Appointment
                        </button>
                    </form>
                    <?php else: ?>
                    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1rem">
                        You must be logged in as a customer to book an appointment.
                    </p>
                    <a href="<?= SITE_URL ?>/auth/login.php" class="btn-gold btn w-100 mb-2">Login to Book</a>
                    <a href="<?= SITE_URL ?>/auth/register.php" class="btn-outline-gold btn w-100">Create Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
