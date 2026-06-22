document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-main-nav]');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('open');
        });
    }

    document.querySelectorAll('.flash').forEach((flash) => {
        const message = flash.textContent.trim();

        if (message.toLowerCase().includes('category already exists')) {
            window.alert(message);
        }

        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-6px)';
            flash.style.transition = 'opacity .25s ease, transform .25s ease';
            setTimeout(() => flash.remove(), 280);
        }, 5200);
    });
});
