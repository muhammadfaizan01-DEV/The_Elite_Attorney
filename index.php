<?php
// ============================================================
// index.php — Home / Landing Page
// ============================================================
require_once 'includes/config.php';
$pageTitle = 'Premier Legal Services';

$lawyersResult = $conn->query("SELECT id, name, specialization, experience, photo, fee, rating, city FROM lawyers WHERE is_approved=1 AND is_active=1 ORDER BY rating DESC LIMIT 6");
$servicesResult = $conn->query("SELECT * FROM services WHERE is_active=1 LIMIT 6");

include 'includes/header.php';
?>

<section class="hero-section">
    <div class="container position-relative z-1">
        <div class="row align-items-center">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="hero-badge"><i class="fas fa-certificate"></i> Pakistan's Premier Legal Directory</div>
                <h1 class="hero-title">Find Your <span class="gold-text">Trusted</span><br>Legal Advocate</h1>
                <p class="hero-subtitle">Connect with verified, top-rated attorneys specialized in every field of law. Expert counsel, when you need it most.</p>
                <div class="d-flex flex-wrap gap-3 mb-5">
                    <a href="<?= SITE_URL ?>/pages/lawyers.php" class="btn-gold btn"><i class="fas fa-search me-2"></i>Find a Lawyer</a>
                    <a href="<?= SITE_URL ?>/lawyer/register.php" class="btn-outline-gold btn"><i class="fas fa-user-plus me-2"></i>Join as Attorney</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item"><div class="stat-number" data-count="200" data-suffix="+">0+</div><div class="stat-label">Verified Lawyers</div></div>
                    <div class="stat-item"><div class="stat-number" data-count="1500" data-suffix="+">0+</div><div class="stat-label">Cases Handled</div></div>
                    <div class="stat-item"><div class="stat-number" data-count="98" data-suffix="%">0%</div><div class="stat-label">Client Satisfaction</div></div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center" data-aos="fade-left">
                <div class="hero-visual">
                    <div class="hero-ring"><div class="hero-ring-inner"><i class="fas fa-balance-scale"></i></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <h2 class="section-heading">Practice <span class="gold-text">Areas</span></h2>
            <div class="gold-divider"></div>
            <p class="section-sub">Our network covers every major area of law</p>
        </div>
        <div class="row g-4">
            <?php while ($svc = $servicesResult->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="glass-card service-card h-100">
                    <div class="service-icon"><i class="fas <?= htmlspecialchars($svc['icon']) ?>"></i></div>
                    <h5><?= htmlspecialchars($svc['name']) ?></h5>
                    <p><?= htmlspecialchars($svc['description']) ?></p>
                    <a href="<?= SITE_URL ?>/pages/lawyers.php?service=<?= $svc['id'] ?>" class="gold-text mt-2 d-inline-block" style="font-size:0.85rem;font-weight:600;">
                        View Lawyers <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Lawyers -->
<section class="lawyers-section">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <h2 class="section-heading">Featured <span class="gold-text">Attorneys</span></h2>
            <div class="gold-divider"></div>
            <p class="section-sub">Meet our top-rated legal professionals</p>
        </div>
        <div class="row g-4">
            <?php if ($lawyersResult->num_rows === 0): ?>
            <div class="col-12 text-center text-muted">
                <p>No lawyers registered yet. <a href="<?= SITE_URL ?>/lawyer/register.php" class="gold-text">Be the first!</a></p>
            </div>
            <?php else: $delay=0; while ($lw = $lawyersResult->fetch_assoc()): $delay+=100; ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $delay%300 ?>">
                <div class="glass-card lawyer-card h-100">
                    <div class="lawyer-photo-wrap">
                        <img src="<?= SITE_URL ?>/uploads/photos/<?= htmlspecialchars($lw['photo']) ?>"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($lw['name']) ?>&background=C9A84C&color=000&size=240'"
                             alt="<?= htmlspecialchars($lw['name']) ?>">
                        <div class="overlay"></div>
                    </div>
                    <div class="card-body">
                        <h5><?= htmlspecialchars($lw['name']) ?></h5>
                        <div class="specialization-badge"><?= htmlspecialchars($lw['specialization'] ?? 'General') ?></div>
                        <div class="lawyer-meta d-flex flex-column gap-1 mb-3">
                            <span><i class="fas fa-briefcase me-2"></i><?= (int)$lw['experience'] ?> Years Exp.</span>
                            <span><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($lw['city'] ?? 'N/A') ?></span>
                            <span><i class="fas fa-tag me-2"></i>Rs. <?= number_format($lw['fee'],0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stars">
                                <?php for($i=1;$i<=5;$i++) echo $i<=round($lw['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                            </div>
                            <a href="<?= SITE_URL ?>/pages/lawyer_profile.php?id=<?= $lw['id'] ?>" class="btn-gold btn btn-sm px-3 py-1">View</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; endif; ?>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="<?= SITE_URL ?>/pages/lawyers.php" class="btn-outline-gold btn px-4">
                <i class="fas fa-users me-2"></i>View All Attorneys
            </a>
        </div>
    </div>
</section>

<!-- Why Us -->
<section class="whyus-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5" data-aos="fade-right">
                <h2 class="section-heading">Why <span class="gold-text">Elite Attorney?</span></h2>
                <div class="gold-divider" style="margin:1rem 0;"></div>
                <p style="color:var(--text-muted);">We set the gold standard in connecting clients with the finest legal minds in Pakistan.</p>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="why-item"><div class="why-icon"><i class="fas fa-shield-alt"></i></div><div class="why-content"><h6>Verified & Approved Lawyers</h6><p>Every lawyer undergoes manual review by our admin team before going live.</p></div></div>
                <div class="why-item"><div class="why-icon"><i class="fas fa-calendar-check"></i></div><div class="why-content"><h6>Easy Appointment Booking</h6><p>Book consultations directly from any lawyer profile — quick and hassle-free.</p></div></div>
                <div class="why-item"><div class="why-icon"><i class="fas fa-star"></i></div><div class="why-content"><h6>Genuine Client Reviews</h6><p>Transparent ratings and reviews from real clients.</p></div></div>
                <div class="why-item"><div class="why-icon"><i class="fas fa-lock"></i></div><div class="why-content"><h6>Confidential & Secure</h6><p>Your personal information remains completely private and protected.</p></div></div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container" data-aos="zoom-in">
        <h2 class="section-heading mb-3">Need Legal Help <span class="gold-text">Today?</span></h2>
        <p style="color:var(--text-muted);max-width:500px;margin:0 auto 2rem;">Browse verified attorneys and book your consultation in minutes.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= SITE_URL ?>/pages/lawyers.php" class="btn-gold btn px-4"><i class="fas fa-search me-2"></i>Find a Lawyer Now</a>
            <a href="<?= SITE_URL ?>/pages/contact.php" class="btn-outline-gold btn px-4"><i class="fas fa-envelope me-2"></i>Contact Us</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
