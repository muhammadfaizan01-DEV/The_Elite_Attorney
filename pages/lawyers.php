<?php
// ============================================================
// pages/lawyers.php — Browse All Lawyers
// ============================================================
require_once '../includes/config.php';
$pageTitle = 'Find a Lawyer';

$search    = isset($_GET['search'])  ? clean($conn, $_GET['search'])  : '';
$serviceId = isset($_GET['service']) ? (int)$_GET['service']          : 0;
$city      = isset($_GET['city'])    ? clean($conn, $_GET['city'])     : '';
$sort      = isset($_GET['sort'])    ? clean($conn, $_GET['sort'])     : 'rating';

$where = "WHERE l.is_approved=1 AND l.is_active=1";
if ($search)    $where .= " AND (l.name LIKE '%$search%' OR l.specialization LIKE '%$search%')";
if ($city)      $where .= " AND l.city='$city'";
if ($serviceId) $where .= " AND EXISTS (SELECT 1 FROM lawyer_services ls WHERE ls.lawyer_id=l.id AND ls.service_id=$serviceId)";

$orderBy = match($sort) {
    'experience' => 'l.experience DESC',
    'fee_low'    => 'l.fee ASC',
    'fee_high'   => 'l.fee DESC',
    default      => 'l.rating DESC',
};

$lawyers  = $conn->query("SELECT l.id,l.name,l.specialization,l.experience,l.photo,l.fee,l.rating,l.city FROM lawyers l $where ORDER BY $orderBy");
$services = $conn->query("SELECT id,name FROM services WHERE is_active=1");
$cities   = $conn->query("SELECT DISTINCT city FROM lawyers WHERE is_approved=1 AND city IS NOT NULL ORDER BY city");

include '../includes/header.php';
?>
<div style="padding:100px 0 40px;background:linear-gradient(135deg,#0d0d0d,#0a0800);border-bottom:1px solid var(--dark-border)">
    <div class="container" data-aos="fade-up">
        <h1 class="section-heading">Find an <span class="gold-text">Attorney</span></h1>
        <p style="color:var(--text-muted)">Browse our network of verified legal professionals</p>
    </div>
</div>
<section style="padding:3rem 0;background:var(--dark-bg)">
    <div class="container">
        <!-- Filter -->
        <div class="glass-card p-4 mb-4" data-aos="fade-up">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name or specialization..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Practice Area</label>
                        <select name="service" class="form-select">
                            <option value="">All Areas</option>
                            <?php while ($s=$services->fetch_assoc()): ?>
                            <option value="<?= $s['id'] ?>" <?= $serviceId==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">City</label>
                        <select name="city" class="form-select">
                            <option value="">All Cities</option>
                            <?php while ($c=$cities->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($c['city']) ?>" <?= $city===$c['city']?'selected':'' ?>><?= htmlspecialchars($c['city']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="rating"     <?= $sort==='rating'?'selected':'' ?>>Top Rated</option>
                            <option value="experience" <?= $sort==='experience'?'selected':'' ?>>Experience</option>
                            <option value="fee_low"    <?= $sort==='fee_low'?'selected':'' ?>>Fee: Low–High</option>
                            <option value="fee_high"   <?= $sort==='fee_high'?'selected':'' ?>>Fee: High–Low</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn-gold btn w-100"><i class="fas fa-search me-2"></i>Search</button>
                    </div>
                </div>
            </form>
        </div>
        <p style="color:var(--text-muted);margin-bottom:1.5rem"><?= $lawyers->num_rows ?> attorney<?= $lawyers->num_rows!==1?'s':'' ?> found</p>
        <div class="row g-4">
            <?php if ($lawyers->num_rows===0): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x mb-3" style="color:var(--text-muted)"></i>
                <p style="color:var(--text-muted)">No lawyers found.</p>
                <a href="lawyers.php" class="btn-outline-gold btn mt-2">Clear Filters</a>
            </div>
            <?php else: $d=0; while ($lw=$lawyers->fetch_assoc()): $d+=50; ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $d%300 ?>">
                <div class="glass-card lawyer-card h-100">
                    <div class="lawyer-photo-wrap">
                        <img src="<?= SITE_URL ?>/uploads/photos/<?= htmlspecialchars($lw['photo']) ?>"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($lw['name']) ?>&background=C9A84C&color=000&size=240'"
                             alt="<?= htmlspecialchars($lw['name']) ?>">
                        <div class="overlay"></div>
                    </div>
                    <div class="card-body">
                        <h5><?= htmlspecialchars($lw['name']) ?></h5>
                        <div class="specialization-badge"><?= htmlspecialchars($lw['specialization']??'General') ?></div>
                        <div class="lawyer-meta d-flex flex-column gap-1 mb-3">
                            <span><i class="fas fa-briefcase me-2"></i><?= (int)$lw['experience'] ?> Yrs</span>
                            <span><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($lw['city']??'N/A') ?></span>
                            <span><i class="fas fa-tag me-2"></i>Rs. <?= number_format($lw['fee'],0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stars"><?php for($i=1;$i<=5;$i++) echo $i<=round($lw['rating'])?'<i class="fas fa-star"></i>':'<i class="far fa-star"></i>'; ?></div>
                            <a href="<?= SITE_URL ?>/pages/lawyer_profile.php?id=<?= $lw['id'] ?>" class="btn-gold btn btn-sm px-3 py-1">View</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
