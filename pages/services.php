<?php
// ============================================================
// services.php — Public Services / Practice Areas Page
// ============================================================
require_once '../includes/config.php';
$pageTitle = 'Our Services';

$services = $conn->query("
    SELECT s.*,
           (SELECT COUNT(*) FROM lawyer_services ls WHERE ls.service_id = s.id) AS lawyer_count
    FROM services s
    WHERE s.is_active = 1
    ORDER BY lawyer_count DESC
");

include '../includes/header.php';
?>

<!-- Page Header -->
<div style="padding: 100px 0 60px; background: linear-gradient(135deg, #0d0d0d, #0a0800); border-bottom: 1px solid var(--dark-border);">
    <div class="container text-center" data-aos="fade-up">
        <h1 class="section-heading">Legal <span class="gold-text">Services</span></h1>
        <div class="gold-divider"></div>
        <p style="color:var(--text-muted); max-width:560px; margin: 0 auto">
            Our network of verified attorneys covers every major area of law. Find the expertise you need.
        </p>
    </div>
</div>

<section style="padding: 5rem 0; background: var(--dark-bg);">
    <div class="container">
        <div class="row g-4">
            <?php if ($services->num_rows === 0): ?>
            <div class="col-12 text-center" style="color:var(--text-muted)">No services available.</div>
            <?php else: $delay = 0; while ($svc = $services->fetch_assoc()): $delay += 100; ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $delay % 400 ?>">
                <div class="glass-card p-4 h-100">
                    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.2rem">
                        <div style="width:54px;height:54px;background:linear-gradient(135deg,rgba(201,168,76,0.12),rgba(201,168,76,0.05));border:1px solid var(--dark-border);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:var(--gold);flex-shrink:0">
                            <i class="fas <?= htmlspecialchars($svc['icon']) ?>"></i>
                        </div>
                        <div>
                            <h5 style="margin:0"><?= htmlspecialchars($svc['name']) ?></h5>
                            <small style="color:var(--gold); font-size:0.78rem">
                                <?= (int)$svc['lawyer_count'] ?> attorney<?= $svc['lawyer_count'] != 1 ? 's' : '' ?>
                            </small>
                        </div>
                    </div>

                    <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem">
                        <?= htmlspecialchars($svc['description']) ?>
                    </p>

                    <a href="<?= SITE_URL ?>/pages/lawyers.php?service=<?= $svc['id'] ?>" class="btn-gold btn btn-sm px-3">
                        <i class="fas fa-search me-1"></i>Find Lawyers
                    </a>
                </div>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container text-center" data-aos="zoom-in">
        <h3 style="font-family:'Playfair Display',serif; margin-bottom:1rem">
            Can't find what you're looking for?
        </h3>
        <p style="color:var(--text-muted); margin-bottom:2rem">Contact us directly and we'll connect you with the right attorney.</p>
        <a href="<?= SITE_URL ?>/pages/contact.php" class="btn-gold btn px-4">
            <i class="fas fa-envelope me-2"></i>Contact Us
        </a>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
