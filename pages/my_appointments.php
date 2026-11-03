<?php
// ============================================================
// my_appointments.php — Customer's Booking History
// ============================================================
require_once '../includes/config.php';
if (!isCustomer()) redirect(SITE_URL . '/auth/login.php', 'Please login to view appointments.', 'error');

$custId = $_SESSION['user_id'];

// Cancel appointment (only if pending)
if (isset($_GET['cancel'])) {
    $aid = (int)$_GET['cancel'];
    $conn->query("UPDATE appointments SET status = 'cancelled' WHERE id = $aid AND customer_id = $custId AND status = 'pending'");
    redirect('my_appointments.php', 'Appointment cancelled.');
}

// Filter
$status = isset($_GET['status']) ? clean($conn, $_GET['status']) : '';
$where  = "WHERE a.customer_id = $custId";
if ($status && in_array($status, ['pending','confirmed','cancelled','completed'])) {
    $where .= " AND a.status = '$status'";
}

$appointments = $conn->query("
    SELECT a.*, l.name AS lawyer_name, l.photo AS lawyer_photo,
           l.specialization, l.phone AS lawyer_phone
    FROM appointments a
    JOIN lawyers l ON l.id = a.lawyer_id
    $where
    ORDER BY a.appt_date DESC, a.appt_time DESC
");

// Status counts
$counts = [];
foreach (['pending','confirmed','completed','cancelled'] as $st) {
    $counts[$st] = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE customer_id=$custId AND status='$st'")->fetch_assoc()['c'];
}

$pageTitle = 'My Appointments';
include '../includes/header.php';
?>

<div style="padding: 100px 0 20px; background: linear-gradient(135deg, #0d0d0d, #0a0800); border-bottom: 1px solid var(--dark-border);">
    <div class="container" data-aos="fade-up">
        <h1 class="section-heading">My <span class="gold-text">Appointments</span></h1>
        <p style="color:var(--text-muted)">Track and manage your legal consultations</p>
    </div>
</div>

<section style="padding: 3rem 0; background: var(--dark-bg); min-height: 70vh;">
    <div class="container">

        <!-- Status Tabs -->
        <div class="d-flex flex-wrap gap-2 mb-4" data-aos="fade-up">
            <a href="<?= SITE_URL ?>/pages/my_appointments.php" class="btn btn-sm <?= !$status?'btn-gold':'btn-outline-gold' ?>">
                All (<?= array_sum($counts) ?>)
            </a>
            <?php foreach ($counts as $st => $cnt): ?>
            <a href="<?= SITE_URL ?>/pages/my_appointments.php?status=<?= $st ?>" class="btn btn-sm <?= $status===$st?'btn-gold':'btn-outline-gold' ?>">
                <?= ucfirst($st) ?> (<?= $cnt ?>)
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($appointments->num_rows === 0): ?>
        <div class="glass-card p-5 text-center" data-aos="fade-up">
            <i class="fas fa-calendar-times fa-3x mb-3" style="color:var(--text-muted)"></i>
            <h5 style="color:var(--text-muted)">No appointments found</h5>
            <p style="color:var(--text-muted); font-size:0.9rem">Browse lawyers and book a consultation.</p>
            <a href="<?= SITE_URL ?>/pages/lawyers.php" class="btn-gold btn mt-2">Find a Lawyer</a>
        </div>

        <?php else: $delay = 0; while ($apt = $appointments->fetch_assoc()): $delay += 100; ?>
        <div class="glass-card p-4 mb-3" data-aos="fade-up" data-aos-delay="<?= $delay % 400 ?>">
            <div class="row align-items-center g-3">

                <!-- Lawyer Info -->
                <div class="col-md-5 d-flex align-items-center gap-3">
                    <img src="<?= SITE_URL ?>/uploads/photos/<?= htmlspecialchars($apt['lawyer_photo']) ?>"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($apt['lawyer_name']) ?>&background=C9A84C&color=000&size=120'"
                         style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--gold)" alt="">
                    <div>
                        <div style="font-weight:600"><?= htmlspecialchars($apt['lawyer_name']) ?></div>
                        <div class="specialization-badge" style="margin:0.2rem 0"><?= htmlspecialchars($apt['specialization'] ?? 'Attorney') ?></div>
                        <?php if ($apt['lawyer_phone']): ?>
                        <small style="color:var(--text-muted)"><i class="fas fa-phone me-1" style="color:var(--gold)"></i><?= htmlspecialchars($apt['lawyer_phone']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Appointment Details -->
                <div class="col-md-4">
                    <div class="d-flex flex-column gap-1">
                        <span style="font-size:0.88rem">
                            <i class="fas fa-calendar-alt me-2" style="color:var(--gold)"></i>
                            <?= date('l, d F Y', strtotime($apt['appt_date'])) ?>
                        </span>
                        <span style="font-size:0.88rem">
                            <i class="fas fa-clock me-2" style="color:var(--gold)"></i>
                            <?= date('h:i A', strtotime($apt['appt_time'])) ?>
                        </span>
                        <?php if ($apt['message']): ?>
                        <span style="font-size:0.82rem; color:var(--text-muted)">
                            <i class="fas fa-comment me-2"></i>
                            <?= htmlspecialchars(substr($apt['message'],0,60)) ?><?= strlen($apt['message'])>60?'...':'' ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status & Actions -->
                <div class="col-md-3 text-md-end d-flex flex-column gap-2 align-items-md-end">
                    <?php
                    $badgeMap = ['pending'=>'badge-gold','confirmed'=>'badge-green','cancelled'=>'badge-red','completed'=>'badge-muted'];
                    echo '<span class="'.($badgeMap[$apt['status']]??'badge-muted').'">'.ucfirst($apt['status']).'</span>';
                    ?>

                    <div class="d-flex gap-2 justify-content-md-end">
                        <a href="<?= SITE_URL ?>/pages/lawyer_profile.php?id=<?= $apt['lawyer_id'] ?>" class="btn btn-sm btn-outline-gold">
                            <i class="fas fa-user me-1"></i>Profile
                        </a>
                        <?php if ($apt['status'] === 'pending'): ?>
                        <a href="<?= SITE_URL ?>/pages/my_appointments.php?cancel=<?= $apt['id'] ?>"
                           class="btn btn-sm badge-red border-0"
                           onclick="return confirm('Cancel this appointment?')" style="cursor:pointer">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; endif; ?>

    </div>
</section>

<?php include '../includes/footer.php'; ?>
