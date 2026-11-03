<?php // includes/footer.php ?>
<footer class="site-footer mt-5">
    <div class="container">
        <div class="row g-4 py-5">
            <div class="col-lg-4" data-aos="fade-up">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-balance-scale gold-icon fs-4"></i>
                    <span class="brand-name fs-5">The Elite <span class="gold-text">Attorney</span></span>
                </div>
                <p class="footer-text">Connecting you with top-tier legal professionals. Justice, expertise, and trust — all in one place.</p>
                <div class="social-links mt-3 d-flex gap-3">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4" data-aos="fade-up" data-aos-delay="100">
                <h6 class="footer-heading">Quick Links</h6>
                <ul class="footer-list">
                    <li><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/lawyers.php">Find Lawyers</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/services.php">Services</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4" data-aos="fade-up" data-aos-delay="200">
                <h6 class="footer-heading">For Lawyers</h6>
                <ul class="footer-list">
                    <li><a href="<?= SITE_URL ?>/lawyer/register.php">Register</a></li>
                    <li><a href="<?= SITE_URL ?>/lawyer/login.php">Login</a></li>
                    <li><a href="<?= SITE_URL ?>/lawyer/dashboard.php">Dashboard</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-4" data-aos="fade-up" data-aos-delay="300">
                <h6 class="footer-heading">Contact Us</h6>
                <ul class="footer-list">
                    <li><i class="fas fa-map-marker-alt gold-text me-2"></i>123 Justice Avenue, Karachi</li>
                    <li><i class="fas fa-phone gold-text me-2"></i>+92 300 1234567</li>
                    <li><i class="fas fa-envelope gold-text me-2"></i>info@eliteattorney.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom py-3 d-flex flex-wrap justify-content-between align-items-center">
            <p class="mb-0 footer-text">&copy; <?= date('Y') ?> The Elite Attorney. All rights reserved.</p>
            <p class="mb-0 footer-text">Built with <i class="fas fa-heart gold-text"></i> using PHP & MySQL</p>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 800, once: true });</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html>
