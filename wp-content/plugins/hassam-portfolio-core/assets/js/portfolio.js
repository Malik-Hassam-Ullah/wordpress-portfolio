/* ==========================================================================
   MALIK HASSAM ULLAH — CLEAN WORDPRESS PORTFOLIO SCRIPTS
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {

    /* ----------------------------------------------------------------------
       1. SKILL FILTERING
       ---------------------------------------------------------------------- */
    const skillTabs = document.querySelectorAll('.skill-tab');
    const skillCards = document.querySelectorAll('.skill-card');

    skillTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            skillTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const category = tab.getAttribute('data-category');

            skillCards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    /* ----------------------------------------------------------------------
       3. PROJECT FILTERING
       ---------------------------------------------------------------------- */
    const projectTabs = document.querySelectorAll('.project-tab');
    const projectCards = document.querySelectorAll('.project-card');

    projectTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            projectTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const filter = tab.getAttribute('data-filter');

            projectCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-category') === filter) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    /* ----------------------------------------------------------------------
       4. COPY TO CLIPBOARD & TOAST
       ---------------------------------------------------------------------- */
    const toastContainer = document.getElementById('toastContainer') || (() => {
        const el = document.createElement('div');
        el.id = 'toastContainer';
        el.className = 'toast-container';
        document.body.appendChild(el);
        return el;
    })();

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerText = message;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const textToCopy = btn.getAttribute('data-copy');
            if (navigator.clipboard && textToCopy) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    showToast(`Copied to clipboard: ${textToCopy}`);
                });
            }
        });
    });

    // Check for contact success in URL
    if (window.location.search.includes('contact_success=1')) {
        setTimeout(() => {
            showToast('Thank you! Your message has been sent successfully.');
        }, 500);
    }
});
