<?php
// ============================================================
// ARCHIVO: farmacia/includes/footer.php
// DESCRIPCIÓN: Pie de página y scripts globales
// ============================================================
?>
</main>

<script>
// Elementos del sidebar que deben ocultarse en modo collapsed
const SIDEBAR_TEXT_SEL = [
    '#sidebar .nav-item > span',
    '#sidebar .nav-label',
    '#sidebar .nav-sub',
    '#sidebar .brand-text',
    '#sidebar #sidebar-brand-name',
    '#sidebar #sidebar-brand-sub',
    '#sidebar .sidebar-footer .user-name',
    '#sidebar .sidebar-footer .sucursal-label',
    '#sidebar .sidebar-footer .role-badge',
    '#sidebar .sidebar-footer .logout-btn',
].join(',');

function applyCollapsed(collapsed) {
    const sidebar = document.getElementById('sidebar');

    // 1. Clase en el sidebar
    sidebar.classList.toggle('collapsed', collapsed);

    // 2. Ocultar/mostrar texto directamente via inline style (infalible)
    document.querySelectorAll(SIDEBAR_TEXT_SEL).forEach(el => {
        el.style.display = collapsed ? 'none' : '';
    });

    // 3. Centrar / alinear items
    document.querySelectorAll('#sidebar .nav-item').forEach(el => {
        el.style.justifyContent = collapsed ? 'center' : '';
        el.style.padding        = collapsed ? '10px 8px' : '';
    });

    // 4. Brand
    const brand = sidebar.querySelector('.sidebar-brand');
    if (brand) brand.style.justifyContent = collapsed ? 'center' : '';

    // 5. Footer user-info
    const userInfo = sidebar.querySelector('.sidebar-footer .user-info');
    if (userInfo) userInfo.style.justifyContent = collapsed ? 'center' : '';
}

function toggleSidebar() {
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebarOverlay');
    const isMobile  = window.innerWidth <= 768;
    const collapsed = !sidebar.classList.contains('collapsed'); // nuevo estado

    applyCollapsed(collapsed);

    if (isMobile) {
        overlay.classList.toggle('active', !collapsed);
    } else {
        const main   = document.getElementById('main-content');
        const topbar = document.querySelector('.topbar');
        if (main)   main.classList.toggle('expanded', collapsed);
        if (topbar) topbar.style.left = collapsed ? '64px' : 'var(--sidebar-w)';
        try { localStorage.setItem('sb_collapsed', collapsed ? '1' : '0'); } catch(e) {}
    }
}

function closeMobileSidebar() {
    applyCollapsed(true);
    document.getElementById('sidebarOverlay').classList.remove('active');
}

// ── Estado inicial ──
(function() {
    const isMobile = window.innerWidth <= 768;
    const main     = document.getElementById('main-content');
    const topbar   = document.querySelector('.topbar');

    if (isMobile) {
        applyCollapsed(true);
    } else {
        let savedCollapsed = false;
        try { savedCollapsed = localStorage.getItem('sb_collapsed') === '1'; } catch(e) {}
        applyCollapsed(savedCollapsed);
        if (savedCollapsed) {
            if (main)   main.classList.add('expanded');
            if (topbar) topbar.style.left = '64px';
        }
    }
})();

// Al redimensionar de móvil a desktop, cerrar overlay y ajustar márgenes
window.addEventListener('resize', function() {
    const overlay  = document.getElementById('sidebarOverlay');
    const sidebar  = document.getElementById('sidebar');
    const main     = document.getElementById('main-content');
    const topbar   = document.querySelector('.topbar');
    if (window.innerWidth > 768) {
        overlay.classList.remove('active');
        const col = sidebar.classList.contains('collapsed');
        if (main)   main.classList.toggle('expanded', col);
        if (topbar) topbar.style.left = col ? '64px' : 'var(--sidebar-w)';
    }
});

// Fecha actual
(function() {
    const days   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const now    = new Date();
    const el     = document.getElementById('current-date');
    if (el) el.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
})();
</script>
</body>
</html>
