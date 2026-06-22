// ════════════════════════════════════════════════
// finanzen.js – Bulk-Edit, New-Entry, Delete-Confirm
// ════════════════════════════════════════════════
function initFinanzen() {

    // ── Fortschrittsbalken via data-width ──
    document.querySelectorAll('[data-width]').forEach(function(el) {
        el.style.width = el.getAttribute('data-width') + '%';
    });

    // ── Initialer Zustand: ft-bulk sichtbar, fi-bulk versteckt ──
    document.querySelectorAll('.ft-bulk').forEach(function(el) { el.removeAttribute('hidden'); });
    document.querySelectorAll('.fi-bulk').forEach(function(el) { el.classList.add('fi-hidden'); });

    // ── Bulk-Edit Toggle ──
    function bulkEdit(type) {
        var cardMap = {
            e:  'card-einnahmen',
            a:  'card-ausgaben',
            s:  'card-schulden',
            t:  'card-tasks',
            m:  'card-maintenance',
            i:  'card-immobilien',
            iv: 'card-investments',
            zv: 'card-z-bulk',
            mv: 'card-m-bulk',
            zl: 'card-ziele'
        };
        var card = document.getElementById(cardMap[type]);
        if (!card) return;

        var btnEdit = document.getElementById('btn-edit-' + type);
        var btnSave = document.getElementById('btn-save-' + type);
        var isEditing = btnEdit && btnEdit.getAttribute('data-editing') === '1';

        if (isEditing) {
            // ── Bearbeitungsmodus verlassen ──
            card.querySelectorAll('.ft-bulk').forEach(function(el) { el.removeAttribute('hidden'); });
            card.querySelectorAll('.fi-bulk').forEach(function(el) { el.classList.add('fi-hidden'); });
            var frmId = 'frm-' + type + '-bulk';
            document.querySelectorAll('.fi-bulk[form="' + frmId + '"]').forEach(function(el) {
                el.classList.add('fi-hidden');
            });
            if (btnEdit) {
                btnEdit.removeAttribute('data-editing');
                btnEdit.textContent = '✏ Bearbeiten';
            }
            if (btnSave) btnSave.classList.add('btn-hidden');
            card.querySelectorAll('tbody tr td').forEach(function(td) {
                td.classList.remove('edit-highlight');
            });
        } else {
            // ── Bearbeitungsmodus betreten ──
            card.querySelectorAll('.ft-bulk').forEach(function(el) { el.setAttribute('hidden', ''); });
            card.querySelectorAll('.fi-bulk').forEach(function(el) { el.classList.remove('fi-hidden'); });
            var frmId = 'frm-' + type + '-bulk';
            document.querySelectorAll('.fi-bulk[form="' + frmId + '"]').forEach(function(el) {
                el.classList.remove('fi-hidden');
            });
            if (btnEdit) {
                btnEdit.setAttribute('data-editing', '1');
                btnEdit.textContent = '✕ Abbrechen';
            }
            if (btnSave) btnSave.classList.remove('btn-hidden');
            card.querySelectorAll('tbody tr td').forEach(function(td) {
                td.classList.add('edit-highlight');
            });
        }
    }

    // ── Bearbeiten-Buttons registrieren ──
    ['e', 'a', 's', 't', 'm', 'i', 'iv', 'zl', 'zv', 'mv'].forEach(function(type) {
        var btnEdit = document.getElementById('btn-edit-' + type);
        if (btnEdit) btnEdit.addEventListener('click', function() { bulkEdit(type); });
    });

    // ── Hinzufügen-Buttons registrieren ──
    // mc = Neue Mieteinnahme (Checkliste), z = Neue Zahlung (Checkliste)
    ['e', 'a', 's', 't', 'm', 'i', 'iv', 'z', 'zl', 'mc'].forEach(function(type) {
        var btn = document.getElementById('btn-new-' + type);
        if (btn) btn.addEventListener('click', function() {
            var frm = document.getElementById('frm-' + type + '-new');
            if (frm) frm.submit();
        });
    });

    // ── Löschen mit Bestätigung ──
    document.querySelectorAll('.btn-delete-confirm').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var form = btn.form || btn.closest('form');
            var doConfirm = typeof customConfirm === 'function'
                ? customConfirm
                : function(msg, cb) { if (confirm(msg)) cb(); };
            doConfirm('Diesen Eintrag wirklich löschen?', function() {
                if (form) form.submit();
            });
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFinanzen);
} else {
    initFinanzen();
}