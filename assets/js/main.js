// assets/js/main.js
console.log('Main JS loaded.');

document.addEventListener('DOMContentLoaded', function() {
    // Automatically close alerts after 2 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alertNode) {
        setTimeout(function() {
            if (typeof bootstrap !== 'undefined') {
                const bsAlert = new bootstrap.Alert(alertNode);
                bsAlert.close();
            } else {
                alertNode.remove();
            }
        }, 2000);
    });

    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
});
