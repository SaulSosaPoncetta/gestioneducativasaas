// ─── Sidebar toggle ───────────────────────────────────────────────────

const menuBtn = document.getElementById('menu-btn');
const sidebar = document.querySelector('.sidebar');
const overlay = document.getElementById('overlay');

if (menuBtn) {
    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });
}

overlay?.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
});
