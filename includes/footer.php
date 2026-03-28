<?php
// ============================================================
// ARCHIVO: farmacia/includes/footer.php
// DESCRIPCIÓN: Pie de página y scripts globales
// ============================================================
?>
</main>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.getElementById('main-content').classList.toggle('expanded');
}

// Fecha actual
(function() {
    const days = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const now = new Date();
    const str = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    const el = document.getElementById('current-date');
    if (el) el.textContent = str;
})();
</script>
</body>
</html>