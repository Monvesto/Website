(function () {
    'use strict';

    const toggle  = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) &&
                e.target !== toggle) {
                sidebar.classList.remove('open');
            }
        });
        sidebar.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                sidebar.classList.remove('open');
            });
        });
    }

    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

})();