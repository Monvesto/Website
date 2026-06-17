(function () {
    'use strict';

    // ── Mobile Sidebar Toggle ──────────────────────────
    const toggle  = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
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

    // ── Flash-Meldungen nach 5 Sekunden ausblenden ─────
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

    // ── Collapse Toggle ────────────────────────────────
    window.toggleCollapse = function(id) {
        const body  = document.getElementById(id);
        const arrow = document.getElementById('arrow-' + id);
        if (!body) return;
        const open = body.style.display !== 'none';
        body.style.display = open ? 'none' : 'block';
        if (arrow) arrow.textContent = open ? '▼' : '▲';
    };

    // ── Inline Editing ─────────────────────────────────
    window.startEdit = function(type, id) {
        const row = document.getElementById('row-' + type + '-' + id);
        if (!row) return;
        row.querySelectorAll('.view-cell').forEach(c => c.style.display = 'none');
        row.querySelectorAll('.edit-cell').forEach(c => c.style.display = '');
        row.querySelector('.btn-edit').style.display   = 'none';
        row.querySelector('.btn-save').style.display   = 'inline-flex';
        row.querySelector('.btn-cancel').style.display = 'inline-flex';
        row.classList.add('editing');
        const first = row.querySelector('.inline-input');
        if (first) first.focus();
    };

    window.cancelEdit = function(type, id) {
        const row = document.getElementById('row-' + type + '-' + id);
        if (!row) return;
        row.querySelectorAll('.view-cell').forEach(c => c.style.display = '');
        row.querySelectorAll('.edit-cell').forEach(c => c.style.display = 'none');
        row.querySelector('.btn-edit').style.display   = 'inline-flex';
        row.querySelector('.btn-save').style.display   = 'none';
        row.querySelector('.btn-cancel').style.display = 'none';
        row.classList.remove('editing');
    };

    window.submitInline = function(type, id) {
        const row  = document.getElementById('row-' + type + '-' + id);
        if (!row) return;
        const form = row.querySelector('form.inline-form');
        if (form) form.submit();
    };

})();