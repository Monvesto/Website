(function () {
    'use strict';

    // ── Mobile Sidebar Toggle ──
    var toggle  = document.getElementById('menuToggle');
    var sidebar = document.getElementById('sidebar');
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

    // ── Flash-Meldungen nach 5 Sekunden ausblenden ──
    // Statt style.opacity/transition → CSS-Klasse
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.classList.add('alert-fade');
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

    // ── Collapse Toggle ──
    window.toggleCollapse = function(id) {
        var body  = document.getElementById(id);
        var arrow = document.getElementById('arrow-' + id);
        if (!body) return;
        var open = !body.hasAttribute('hidden');
        if (open) {
            body.setAttribute('hidden', '');
            if (arrow) arrow.textContent = '▼';
        } else {
            body.removeAttribute('hidden');
            if (arrow) arrow.textContent = '▲';
        }
    };

    // ── Inline Editing (alte Seiten) ──
    window.startEdit = function(type, id) {
        var row = document.getElementById('row-' + type + '-' + id);
        if (!row) return;
        row.querySelectorAll('.view-cell').forEach(function(c) { c.setAttribute('hidden', ''); });
        row.querySelectorAll('.edit-cell').forEach(function(c) { c.removeAttribute('hidden'); });
        row.querySelector('.btn-edit').setAttribute('hidden', '');
        row.querySelector('.btn-save').removeAttribute('hidden');
        row.querySelector('.btn-cancel').removeAttribute('hidden');
        row.classList.add('editing');
        var first = row.querySelector('.inline-input');
        if (first) first.focus();
    };

    window.cancelEdit = function(type, id) {
        var row = document.getElementById('row-' + type + '-' + id);
        if (!row) return;
        row.querySelectorAll('.view-cell').forEach(function(c) { c.removeAttribute('hidden'); });
        row.querySelectorAll('.edit-cell').forEach(function(c) { c.setAttribute('hidden', ''); });
        row.querySelector('.btn-edit').removeAttribute('hidden');
        row.querySelector('.btn-save').setAttribute('hidden', '');
        row.querySelector('.btn-cancel').setAttribute('hidden', '');
        row.classList.remove('editing');
    };

    window.submitInline = function(type, id) {
        var row  = document.getElementById('row-' + type + '-' + id);
        if (!row) return;
        var form = row.querySelector('form.inline-form');
        if (form) form.submit();
    };

})();