<?php
// ============================================================
// ARCHIVO: farmacia/includes/footer.php
// DESCRIPCIÓN: Pie de página y scripts globales
// ============================================================
?>
</main>

<script>
// Breakpoints sincronizados con style.css
const BP_MOBILE = 540;   // sidebar oculto con overlay
const BP_TABLET = 768;   // sidebar icon-only (CSS lo maneja)

// Elementos de texto que se ocultan en modo collapsed (desktop)
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

function isMobileView()  { return window.innerWidth <= BP_MOBILE; }
function isTabletView()  { return window.innerWidth > BP_MOBILE && window.innerWidth <= BP_TABLET; }
function isDesktopView() { return window.innerWidth > BP_TABLET; }

// ── Desktop: colapsar/expandir sidebar (icon-only ↔ texto) ──
function applyCollapsedDesktop(collapsed) {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed', collapsed);

    document.querySelectorAll(SIDEBAR_TEXT_SEL).forEach(el => {
        el.style.display = collapsed ? 'none' : '';
    });
    document.querySelectorAll('#sidebar .nav-item').forEach(el => {
        el.style.justifyContent = collapsed ? 'center' : '';
        el.style.padding        = collapsed ? '10px 8px' : '';
    });
    const brand    = sidebar.querySelector('.sidebar-brand');
    const userInfo = sidebar.querySelector('.sidebar-footer .user-info');
    if (brand)    brand.style.justifyContent    = collapsed ? 'center' : '';
    if (userInfo) userInfo.style.justifyContent = collapsed ? 'center' : '';

    const main   = document.getElementById('main-content');
    const topbar = document.querySelector('.topbar');
    if (main)   main.classList.toggle('expanded', collapsed);
    if (topbar) topbar.style.left = collapsed ? '64px' : 'var(--sidebar-w)';
}

// ── Móvil: abrir/cerrar sidebar con overlay ──
function openMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.add('mobile-open');
    overlay.classList.add('active');
}
function closeMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
}

// ── Botón hamburguesa (llamado desde header) ──
function toggleSidebar() {
    if (isMobileView() || isTabletView()) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('mobile-open')) {
            closeMobileSidebar();
        } else {
            openMobileSidebar();
        }
    } else if (isDesktopView()) {
        let savedCollapsed = false;
        try { savedCollapsed = localStorage.getItem('sb_collapsed') === '1'; } catch(e) {}
        const newCollapsed = !savedCollapsed;
        applyCollapsedDesktop(newCollapsed);
        try { localStorage.setItem('sb_collapsed', newCollapsed ? '1' : '0'); } catch(e) {}
    }
}

// ── Estado inicial según viewport ──
(function init() {
    if (isDesktopView()) {
        let savedCollapsed = false;
        try { savedCollapsed = localStorage.getItem('sb_collapsed') === '1'; } catch(e) {}
        applyCollapsedDesktop(savedCollapsed);
    }
    // móvil y tablet: el CSS aplica los estilos correctos automáticamente
})();

// ── Al redimensionar: limpiar clases residuales ──
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const main    = document.getElementById('main-content');
    const topbar  = document.querySelector('.topbar');

    // Siempre cerrar el overlay al redimensionar
    overlay.classList.remove('active');
    sidebar.classList.remove('mobile-open');

    if (isDesktopView()) {
        let savedCollapsed = false;
        try { savedCollapsed = localStorage.getItem('sb_collapsed') === '1'; } catch(e) {}
        applyCollapsedDesktop(savedCollapsed);
    } else if (isMobileView()) {
        sidebar.classList.remove('collapsed');
        document.querySelectorAll(SIDEBAR_TEXT_SEL).forEach(el => { el.style.display = ''; });
        document.querySelectorAll('#sidebar .nav-item').forEach(el => {
            el.style.justifyContent = '';
            el.style.padding = '';
        });
        if (main)   { main.classList.remove('expanded'); main.style.marginLeft = ''; }
        if (topbar) topbar.style.left = '';
    }
});

// ── Panel usuario/sucursales en footer del sidebar ──
function toggleUserPanel() {
    const panel   = document.getElementById('userSucPanel');
    const chevron = document.getElementById('userSucChevron');
    if (!panel) return;
    const open = panel.classList.toggle('open');
    if (chevron) chevron.style.transform = open ? 'rotate(180deg)' : '';
}

document.addEventListener('click', function(e) {
    const panel   = document.getElementById('userSucPanel');
    const footer  = document.querySelector('.sidebar-footer');
    if (!panel || !panel.classList.contains('open')) return;
    if (footer && (footer.contains(e.target) || panel.contains(e.target))) return;
    panel.classList.remove('open');
    const chevron = document.getElementById('userSucChevron');
    if (chevron) chevron.style.transform = '';
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
