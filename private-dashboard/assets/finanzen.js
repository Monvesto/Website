function initFinanzen() {

    // Fortschrittsbalken-Breiten via data-width setzen (CSP-konform per JS)
    document.querySelectorAll('[data-width]').forEach(function(el) {
        el.style.width = el.getAttribute('data-width') + '%';
    });

    // Beim Load: alle ft-bulk sichtbar erzwingen, fi-bulk verstecken
    document.querySelectorAll('.ft-bulk').forEach(function(el) {
        el.removeAttribute('hidden');
        el.setAttribute('data-vis', 'show');
    });
    document.querySelectorAll('.fi-bulk').forEach(function(el) {
        el.setAttribute('hidden', '');
    });

    function bulkEdit(type) {
        var cardMap = { e: 'card-einnahmen', a: 'card-ausgaben', s: 'card-schulden' };
        var card = document.getElementById(cardMap[type]);
        if (!card) return;

        // Texte verstecken, Inputs zeigen
        card.querySelectorAll('.ft-bulk').forEach(function(el) {
            el.setAttribute('hidden', '');
        });
        card.querySelectorAll('.fi-bulk').forEach(function(el) {
            el.removeAttribute('hidden');
        });

        // Buttons tauschen
        var btnEdit = document.getElementById('btn-edit-' + type);
        var btnSave = document.getElementById('btn-save-' + type);
        if (btnEdit) btnEdit.setAttribute('hidden', '');
        if (btnSave) btnSave.removeAttribute('hidden');
    }

    ['e', 'a', 's'].forEach(function(type) {
        var btnEdit = document.getElementById('btn-edit-' + type);
        if (btnEdit) btnEdit.addEventListener('click', function() { bulkEdit(type); });

        var btnSave = document.getElementById('btn-save-' + type);
        if (btnSave) btnSave.addEventListener('click', function() {
            var frm = document.getElementById('frm-' + type + '-bulk');
            if (frm) frm.submit();
        });
    });

    ['e', 'a', 's'].forEach(function(type) {
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

// Ausführen sobald DOM bereit – egal ob vorher oder nachher
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFinanzen);
} else {
    initFinanzen();
}