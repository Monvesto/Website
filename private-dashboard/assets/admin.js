// assets/admin.js – Admin-Panel JS

// Rollen-Dropdown
document.querySelectorAll('.rf-role-select').forEach(function(sel) {
    sel.addEventListener('change', function() {
        this.closest('form').submit();
    });
});

// Edit-Modal für Display-Name / Geburtsdatum
document.querySelectorAll('.btn-admin-edit-user').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('admin-edit-uid').value     = this.dataset.id;
        document.getElementById('admin-edit-display').value = this.dataset.display;
        document.getElementById('admin-edit-geb').value     = this.dataset.geb;
        document.getElementById('admin-edit-modal').hidden  = false;
    });
});

var cancelBtn = document.getElementById('admin-edit-cancel');
if (cancelBtn) {
    cancelBtn.addEventListener('click', function() {
        document.getElementById('admin-edit-modal').hidden = true;
    });
}

// Passwort-Confirm
document.querySelectorAll('.admin-pw-confirm').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var form = btn.closest('form');
        var pw   = form.querySelector('input[name="new_password"]').value;
        if (!pw || pw.length < 8) {
            alert('Bitte ein Passwort mit mindestens 8 Zeichen eingeben.');
            return;
        }
        customConfirm(
            'Passwort wirklich zurücksetzen?',
            function() { form.submit(); },
            'PW setzen',
            'btn-primary'
        );
    });
});

// Confirm-Dialoge
document.querySelectorAll('[data-confirm-msg]').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var form = btn.closest('form');
        customConfirm(
            btn.getAttribute('data-confirm-msg'),
            function() { form.submit(); },
            btn.textContent.trim(),
            btn.classList.contains('btn-amber') ? 'btn-amber' : 'btn-primary'
        );
    });
});