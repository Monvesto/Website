// Global verfügbar für finanzen.js und andere Scripts
function customConfirm(message, onConfirm, confirmLabel, confirmClass) {
    confirmLabel = confirmLabel || 'Löschen';
    confirmClass = confirmClass || 'btn-danger';
    var existing = document.getElementById('confirm-modal');
    if (existing) existing.remove();
    var modal = document.createElement('div');
    modal.id = 'confirm-modal';
    modal.innerHTML = [
        '<div id="confirm-backdrop"></div>',
        '<div id="confirm-box">',
        '  <p id="confirm-msg">' + message + '</p>',
        '  <div id="confirm-btns">',
        '    <button id="confirm-no" class="btn btn-ghost btn-sm">Abbrechen</button>',
        '    <button id="confirm-yes" class="btn ' + confirmClass + ' btn-sm">' + confirmLabel + '</button>',
        '  </div>',
        '</div>'
    ].join('');
    document.body.appendChild(modal);
    document.getElementById('confirm-no').addEventListener('click', function() {
        modal.remove();
    });
    document.getElementById('confirm-yes').addEventListener('click', function() {
        modal.remove();
        onConfirm();
    });
    document.getElementById('confirm-backdrop').addEventListener('click', function() {
        modal.remove();
    });
}

(function () {
    'use strict';

    // Scroll-Position wiederherstellen ohne Flackern
    var savedY = sessionStorage.getItem('scrollY');
    if (savedY) {
        document.documentElement.style.scrollBehavior = 'auto';
        window.scrollTo(0, parseInt(savedY));
        sessionStorage.removeItem('scrollY');
    }

    // Scroll-Position vor Submit speichern
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            sessionStorage.setItem('scrollY', window.scrollY);
        });
    });

    // ── Fortschrittsbalken via data-width ──
    document.querySelectorAll('[data-width]').forEach(function(el) {
        el.style.width = el.getAttribute('data-width') + '%';
    });

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

// ── Tooltip ──
(function() {
    var tip = document.createElement('div');
    tip.id = 'tooltip-popup';
    document.body.appendChild(tip);

    document.querySelectorAll('.tooltip-icon[data-tip]').forEach(function(el) {
        el.addEventListener('mouseenter', function() {
            tip.textContent = el.getAttribute('data-tip');
            tip.style.display = 'block';
            var r = el.getBoundingClientRect();
            var left = r.left + (r.width / 2) - (tip.offsetWidth / 2);
            var top  = r.bottom + 6;
            // Nicht über rechten Rand
            if (left + tip.offsetWidth > window.innerWidth - 10) {
                left = window.innerWidth - tip.offsetWidth - 10;
            }
            // Nicht über linken Rand
            if (left < 10) left = 10;
            tip.style.left = left + 'px';
            tip.style.top  = top  + 'px';
        });
        el.addEventListener('mouseleave', function() {
            tip.style.display = 'none';
        });
    });
})();

// ── Profil Speichern Confirm ──
(function() {
    var form = document.querySelector('form[data-confirm]');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        customConfirm(
            form.getAttribute('data-confirm'),
            function() { form.submit(); },
            'Speichern',
            'btn-primary'
        );
    });
})();