// ============================================================
// main.js — The Elite Attorney
// ============================================================

// Navbar scroll effect
window.addEventListener('scroll', () => {
    const nav = document.getElementById('mainNav');
    if (nav) {
        nav.classList.toggle('scrolled', window.scrollY > 50);
    }
});

// Animated number counters (for hero stats)
function animateCounters() {
    document.querySelectorAll('[data-count]').forEach(el => {
        const target = parseInt(el.getAttribute('data-count'));
        let current = 0;
        const step = Math.ceil(target / 60);
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current + (el.getAttribute('data-suffix') || '');
            if (current >= target) clearInterval(timer);
        }, 25);
    });
}

// Trigger counter when in viewport
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) { animateCounters(); observer.disconnect(); }
    });
}, { threshold: 0.5 });

const statsEl = document.querySelector('.hero-stats');
if (statsEl) observer.observe(statsEl);

// Auto-dismiss flash alerts after 4s
setTimeout(() => {
    document.querySelectorAll('.flash-alert').forEach(el => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 4000);
