// ============================================
// GLH - Main JavaScript
// ============================================

document.addEventListener('DOMContentLoaded', function () {

    // ─── Hamburger Menu ───────────────────────────────────────────────
    const hamburger = document.querySelector('.hamburger');
    const nav       = document.querySelector('nav');
    const spans     = document.querySelectorAll('.hamburger span');

    function closeMenu() {
        nav.classList.remove('active');
        hamburger.setAttribute('aria-expanded', 'false');
        spans[0].style.transform = '';
        spans[1].style.opacity   = '';
        spans[2].style.transform = '';
    }

    function openMenu() {
        nav.classList.add('active');
        hamburger.setAttribute('aria-expanded', 'true');
        spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        spans[1].style.opacity   = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
    }

    if (hamburger && nav) {
        // Toggle on hamburger click
        hamburger.addEventListener('click', function (e) {
            e.stopPropagation();
            nav.classList.contains('active') ? closeMenu() : openMenu();
        });

        // Close on nav link click
        nav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });

        // Close when clicking outside the header
        document.addEventListener('click', function (e) {
            const header = document.querySelector('header');
            if (header && !header.contains(e.target)) {
                closeMenu();
            }
        });
    }

    // ─── Prevent iOS Zoom on Input Focus ─────────────────────────────
    document.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('focus', function () {
            document.body.classList.add('input-focused');
        });
        el.addEventListener('blur', function () {
            document.body.classList.remove('input-focused');
        });
    });

    // ─── FAQ Accordion ────────────────────────────────────────────────
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(function (item) {
        const question = item.querySelector('.faq-question');
        const answer   = item.querySelector('.faq-answer');

        if (!question || !answer) return;

        // Set initial ARIA + height state
        answer.style.overflow = 'hidden';
        answer.style.maxHeight = '0';
        answer.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
        answer.style.opacity = '0';
        question.setAttribute('aria-expanded', 'false');

        question.addEventListener('click', function () {
            const isOpen = item.classList.contains('active');

            // Close all open items first
            faqItems.forEach(function (other) {
                const otherAnswer = other.querySelector('.faq-answer');
                const otherQuestion = other.querySelector('.faq-question');
                if (otherAnswer && other.classList.contains('active')) {
                    other.classList.remove('active');
                    otherAnswer.style.maxHeight = '0';
                    otherAnswer.style.opacity   = '0';
                    otherQuestion.setAttribute('aria-expanded', 'false');
                }
            });

            // Open the clicked item if it was closed
            if (!isOpen) {
                item.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + 'px';
                answer.style.opacity   = '1';
                question.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // ─── Product Search Filter ────────────────────────────────────────
    const searchInput = document.getElementById('product-search');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            document.querySelectorAll('.product-card').forEach(function (card) {
                const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
                const farm = card.querySelector('.farm-name')?.textContent.toLowerCase() || '';
                const match = name.includes(query) || farm.includes(query);
                card.style.display = match ? '' : 'none';
            });
        });
    }

// ─── Stock Management (Display Only) ──────────────────────────────
// Visual stock warnings without blocking form submits

const STOCK_API = '/GLH/api/stock.php';

async function updateStockDisplay(productId, button, available) {
    const card = button.closest('.product-card');
    const warning = card?.querySelector('.stock-warning, .stock-warning-large');
    
    if (available <= 0) {
        button.disabled = true;
        button.textContent = 'Out of Stock';
        button.classList.add('out-of-stock');
        if (warning) {
            warning.textContent = 'Out of Stock';
            warning.style.display = 'block';
        }
    } else {
        button.disabled = false;
        button.classList.remove('out-of-stock');
        button.textContent = button.dataset.originalText || '+';
        if (warning) {
            if (available < 5) {
                warning.textContent = `⚠️ Only ${available} left`;
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        }
    }
}

async function checkStock(productId, button) {
    try {
        const res = await fetch(`${STOCK_API}?product_id=${productId}`);
        const data = await res.json();
        updateStockDisplay(productId, button, data.available);
        return data.available;
    } catch (err) {
        console.warn('Stock check failed:', err);
        return parseInt(button.dataset.stock) || 1;
    }
}

// Load initial stock displays
function initStockButtons() {
    document.querySelectorAll('.add-stock-btn, .btn-add[name="add_to_trolley"]').forEach(async (btn) => {
        const productId = btn.dataset.productId;
        if (!productId || btn.dataset.originalText) return;
        
        btn.dataset.originalText = btn.textContent.trim();
        await checkStock(productId, btn);
    });
}

// Poll every 30s for updates (non-blocking)
setInterval(() => {
    document.querySelectorAll('.add-stock-btn, .btn-add[name="add_to_trolley"]').forEach(btn => {
        const productId = btn.dataset.productId;
        if (productId && !btn.disabled) checkStock(productId, btn);
    });
}, 30000);

initStockButtons();



});

