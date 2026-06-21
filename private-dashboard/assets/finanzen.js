function initFinanzen() {

    document.querySelectorAll('[data-width]').forEach(function(el) {
        el.style.width = el.getAttribute('data-width') + '%';
    });

    document.querySelectorAll('.ft-bulk').forEach(function(el) { el.removeAttribute('hidden'); });
    document.querySelectorAll('.fi-bulk').forEach(function(el) { el.classList.add('fi-hidden'); });

    function bulkEdit(type) {
        var cardMap = { e: 'card-einnahmen', a: 'card-ausgaben', s: 'card-schulden', t: 'card-tasks', m: 'card-maintenance' };
        var card = document.getElementById(cardMap[type]);
        if (!card) return;

        card.querySelectorAll('.ft-bulk').forEach(function(el) { el.setAttribute('hidden', ''); });

        var frmId = 'frm-' + type + '-bulk';
        document.querySelectorAll('.fi-bulk[form="' + frmId + '"]').forEach(function(el) {
            el.classList.remove('fi-hidden');
        });
        card.querySelectorAll('.fi-bulk').forEach(function(el) {
            el.classList.remove('fi-hidden');
        });

        var btnEdit = document.getElementById('btn-edit-' + type);
        var btnSave = document.getElementById('btn-save-' + type);
        if (btnEdit) btnEdit.setAttribute('hidden', '');
        if (btnSave) btnSave.classList.remove('btn-hidden');

        card.querySelectorAll('tbody tr:not(.new-row):not(.new-row-label) td').forEach(function(td) {
            td.classList.add('edit-highlight');
        });
    }

    ['e', 'a', 's', 't', 'm'].forEach(function(type) {
        var btnEdit = document.getElementById('btn-edit-' + type);
        if (btnEdit) btnEdit.addEventListener('click', function() { bulkEdit(type); });
    });

    // Checkliste Verwaltung bulk edit
    [['zv','card-z-bulk','frm-zv-bulk'], ['mv','card-m-bulk','frm-mv-bulk']].forEach(function(cfg) {
        var type = cfg[0], cardId = cfg[1], frmId = cfg[2];
        var btnEdit = document.getElementById('btn-edit-' + type);
        if (btnEdit) btnEdit.addEventListener('click', function() {
            var card = document.getElementById(cardId);
            if (!card) return;
            card.querySelectorAll('.fi-bulk').forEach(function(el) { el.classList.remove('fi-hidden'); });
            card.querySelectorAll('.ft-bulk').forEach(function(el) { el.setAttribute('hidden', ''); });
            btnEdit.setAttribute('hidden', '');
            var btnSave = document.getElementById('btn-save-' + type);
            if (btnSave) btnSave.removeAttribute('hidden');
            card.querySelectorAll('tbody tr:not(.new-row):not(.new-row-label) td').forEach(function(td) {
                td.classList.add('edit-highlight');
            });
        });
        var btnSave = document.getElementById('btn-save-' + type);
        if (btnSave) btnSave.addEventListener('click', function() {
            var frm = document.getElementById(frmId);
            if (frm) {
                frm.querySelectorAll('.fi-hidden').forEach(function(el) { el.classList.remove('fi-hidden'); });
                frm.submit();
            }
        });
    });

    ['e', 'a', 's', 't', 'm', 'z'].forEach(function(type) {
        var btn = document.getElementById('btn-new-' + type);
        if (btn) btn.addEventListener('click', function() {
            var frm = document.getElementById('frm-' + type + '-new');
            if (frm) frm.submit();
        });
    });

    document.querySelectorAll('.btn-delete-confirm').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Diesen Eintrag wirklich löschen?')) {
                e.preventDefault();
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFinanzen);
} else {
    initFinanzen();
}