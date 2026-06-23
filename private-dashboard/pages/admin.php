<?php
// ════════════════════════════════════════════════
// pages/admin.php – Admin-Panel
// ════════════════════════════════════════════════

$db = get_db();

if (!is_admin()) {
    echo '<div class="alert alert-error">⚠ Kein Zugriff. Diese Seite ist nur für Administratoren.</div>';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    if ($act === 'create_user') {
        $username     = trim($_POST['username'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $password     = $_POST['password'] ?? '';
        $role         = $_POST['role'] ?? 'user';
        if ($username && $email && $display_name && $password) {
            try {
                $geburtsdatum = trim($_POST['geburtsdatum'] ?? '') ?: null;
                $verified     = isset($_POST['verified']) && $_POST['verified'] === '1' ? 1 : 0;
                $db->prepare("INSERT INTO users (username,email,password,display_name,role,geburtsdatum,verified) VALUES (?,?,?,?,?,?,?)")
                  ->execute([$username,$email,password_hash($password,PASSWORD_DEFAULT),$display_name,$role,$geburtsdatum,$verified]);
                $uid = (int)$db->lastInsertId();
                $db->prepare("INSERT INTO user_profiles (user_id,profile_name,is_default,sort_order) VALUES (?,?,1,0)")
                   ->execute([$uid,$display_name]);
                header("Location: ?page=admin&msg=user_created"); exit;
            } catch (PDOException $e) {
                header("Location: ?page=admin&error=duplicate"); exit;
            }
        }
    }

    if ($act === 'toggle_user') {
        $uid = (int)($_POST['user_id'] ?? 0);
        if ($uid && $uid !== current_user_id()) {
            $db->prepare("UPDATE users SET aktiv = 1-aktiv WHERE id=?")->execute([$uid]);
        }
        header("Location: ?page=admin&msg=saved"); exit;
    }

    if ($act === 'set_role') {
        $uid  = (int)($_POST['user_id'] ?? 0);
        $role = in_array($_POST['role']??'', ['admin','user']) ? $_POST['role'] : 'user';
        if ($uid && $uid !== current_user_id()) {
            $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role,$uid]);
        }
        header("Location: ?page=admin&msg=saved"); exit;
    }

    if ($act === 'toggle_verified') {
        $uid = (int)($_POST['user_id'] ?? 0);
        if ($uid) {
            $db->prepare("UPDATE users SET verified = 1-verified WHERE id=?")->execute([$uid]);
        }
        header("Location: ?page=admin&msg=saved"); exit;
    }

    if ($act === 'add_profile') {
        $uid          = (int)($_POST['user_id'] ?? 0);
        $profile_name = trim($_POST['profile_name'] ?? '');
        $count = (int)$db->query("SELECT COUNT(*) FROM user_profiles WHERE user_id=$uid")->fetchColumn();
        if ($uid && $profile_name && $count < MAX_PROFILES) {
            try {
                $sort = (int)$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM user_profiles WHERE user_id=$uid")->fetchColumn();
                $db->prepare("INSERT INTO user_profiles (user_id,profile_name,is_default,sort_order) VALUES (?,?,0,?)")
                   ->execute([$uid,$profile_name,$sort]);
                if ($uid === current_user_id()) load_user_profiles($db, $uid);
                header("Location: ?page=admin&msg=profile_added"); exit;
            } catch (PDOException $e) {
                header("Location: ?page=admin&error=profile_exists"); exit;
            }
        }
    }

    if ($act === 'delete_profile') {
        $pid = (int)($_POST['profile_id'] ?? 0);
        $profile = $db->prepare("SELECT * FROM user_profiles WHERE id=?");
        $profile->execute([$pid]);
        $profile = $profile->fetch();
        if ($profile && !$profile['is_default']) {
            $db->prepare("DELETE FROM user_profiles WHERE id=?")->execute([$pid]);
            if ((int)$profile['user_id'] === current_user_id()) load_user_profiles($db, current_user_id());
        }
        header("Location: ?page=admin&msg=saved"); exit;
    }

    if ($act === 'reset_password') {
        $uid      = (int)($_POST['user_id'] ?? 0);
        $new_pass = $_POST['new_password'] ?? '';
        if ($uid && strlen($new_pass) >= 8) {
            $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new_pass,PASSWORD_DEFAULT),$uid]);
            header("Location: ?page=admin&msg=password_reset"); exit;
        }
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$users    = $db->query("SELECT u.*, (SELECT COUNT(*) FROM user_profiles WHERE user_id=u.id) as profile_count FROM users u ORDER BY u.created_at ASC")->fetchAll();
$profiles = $db->query("SELECT p.*, u.username FROM user_profiles p JOIN users u ON p.user_id=u.id ORDER BY p.user_id, p.sort_order")->fetchAll();

$profiles_by_user = [];
foreach ($profiles as $p) $profiles_by_user[$p['user_id']][] = $p;

$msgs   = ['user_created'=>'Nutzer angelegt.','saved'=>'Gespeichert.','profile_added'=>'Profil hinzugefügt.','password_reset'=>'Passwort zurückgesetzt.'];
$errors = ['duplicate'=>'Benutzername oder E-Mail bereits vergeben.','profile_exists'=>'Profil mit diesem Namen existiert bereits.'];
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success"><?= $msgs[$_GET['msg']] ?? 'Gespeichert.' ?></div>
<?php elseif (isset($_GET['error'])): ?>
<div class="alert alert-error"><?= $errors[$_GET['error']] ?? 'Fehler.' ?></div>
<?php endif; ?>

<!-- ════ NUTZERÜBERSICHT ════ -->
<div class="card mt-4">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">👥 Nutzerverwaltung</h2>
            <span class="badge badge-neutral"><?= count($users) ?> Nutzer</span>
        </div>
        <span class="text-muted" style="font-size:13px">Max. Profile pro Nutzer: <strong><?= MAX_PROFILES ?></strong></span>
    </div>

<!-- Spalten-Header -->
<div class="admin-user-header">
    <div>Nutzerdaten</div>
    <div>Profile</div>
    <div>Verifiziert</div>
    <div>Status</div>
    <div>Rolle</div>
    <div>Passwort</div>
</div>

    <?php foreach ($users as $u): ?>
    <div class="admin-user-card <?= !$u['aktiv'] ? 'admin-user-inactive' : '' ?>">

        <!-- ── Zeile 1 ── -->
        <div class="admin-user-top">

            <!-- Nutzerdaten -->
            <div class="admin-user-info">
                <div class="admin-user-name"><?= htmlspecialchars($u['username']) ?></div>
                <div class="admin-user-meta"><?= htmlspecialchars($u['email']) ?></div>
            </div>

            <!-- Info -->
            <div class="admin-user-info">
                <div class="admin-user-meta">
                    🎂 <?= !empty($u['geburtsdatum']) ? date('d.m.Y', strtotime($u['geburtsdatum'])) : '<span class="text-light">–</span>' ?><br>
                    📅 Seit <?= date('d.m.Y', strtotime($u['created_at'])) ?>
                </div>
            </div>

            <!-- Verifiziert -->
            <div class="admin-user-actions">
                <form method="POST" action="?page=admin" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="toggle_verified">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-xs <?= $u['verified'] ? 'btn-ok' : 'btn-ghost' ?>">
                        <?= $u['verified'] ? '✓ Verifiziert' : '✗ Nicht verifiziert' ?>
                    </button>
                </form>
            </div>

            <!-- Profile -->
            <div class="admin-user-profiles">
                <?php foreach ($profiles_by_user[$u['id']] ?? [] as $p): ?>
                <span class="admin-profile-tag">
                    <?= htmlspecialchars($p['profile_name']) ?>
                    <?= $p['is_default'] ? ' ★' : '' ?>
                    <?php if (!$p['is_default']): ?>
                    <form method="POST" action="?page=admin" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="delete_profile">
                        <input type="hidden" name="profile_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="admin-profile-del btn-delete-confirm" title="Profil löschen">✕</button>
                    </form>
                    <?php endif; ?>
                </span>
                <?php endforeach; ?>
                <?php if (($u['profile_count'] ?? 0) < MAX_PROFILES): ?>
                <form method="POST" action="?page=admin" class="admin-profile-add-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="add_profile">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="text" name="profile_name" placeholder="+ Profil">
                    <button type="submit" class="btn btn-primary btn-xs">+</button>
                </form>
                <?php endif; ?>
            </div>

        </div>

        <!-- ── Zeile 2: Aktionen ── -->
        <div class="admin-user-bottom">
            <div class="admin-user-bottom-left">
                <?php if ($u['id'] !== current_user_id()): ?>
                <form method="POST" action="?page=admin" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="toggle_user">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit"
                        class="btn btn-xs btn-delete-confirm <?= $u['aktiv'] ? 'btn-amber' : 'btn-ok' ?>"
                        data-confirm-msg="Nutzer '<?= htmlspecialchars($u['username']) ?>' wirklich <?= $u['aktiv'] ? 'deaktivieren' : 'aktivieren' ?>?">
                        <?= $u['aktiv'] ? 'Deaktivieren' : 'Aktivieren' ?>
                    </button>
                </form>
                <form method="POST" action="?page=admin" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="set_role">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="role" value="<?= $u['role']==='admin' ? 'user' : 'admin' ?>">
                    <button type="submit"
                        class="btn btn-primary btn-xs btn-delete-confirm"
                        data-confirm-msg="Rolle von '<?= htmlspecialchars($u['username']) ?>' zu <?= $u['role']==='admin' ? 'Nutzer' : 'Admin' ?> ändern?">
                        <?= $u['role']==='admin' ? '→ Nutzer' : '→ Admin' ?>
                    </button>
                </form>
                <?php else: ?>
                <span class="badge badge-ok">Aktiv (du)</span>
                <span class="text-muted" style="font-size:12px">–</span>
                <?php endif; ?>
            </div>
            <div class="admin-user-bottom-right">
                <form method="POST" action="?page=admin" class="admin-pw-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="reset_password">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="password" name="new_password" placeholder="Neues Passwort">
                    <button type="submit" class="btn btn-primary btn-xs admin-pw-confirm">PW setzen</button>
                </form>
            </div>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<!-- ════ NEUEN USER ANLEGEN ════ -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">➕ Neuen Nutzer anlegen</h2></div>
    <form method="POST" action="?page=admin">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="create_user">
        <div class="form-grid form-grid--compact">
            <div class="form-group">
                <label>Display-Name (Profilname)</label>
                <input type="text" name="display_name" placeholder="z.B. Max" required>
            </div>
            <div class="form-group">
                <label>Benutzername</label>
                <input type="text" name="username" placeholder="Benutzername" required>
            </div>
            <div class="form-group">
                <label>E-Mail</label>
                <input type="text" name="email" placeholder="email@beispiel.de" required inputmode="email" autocomplete="email">
            </div>
            <div class="form-group">
                <label>Passwort</label>
                <input type="password" name="password" placeholder="min. 8 Zeichen" required>
            </div>
            <div class="form-group">
                <label>Rolle</label>
                <select name="role">
                    <option value="user">Nutzer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Geburtsdatum</label>
                <input type="date" name="geburtsdatum">
            </div>
            <div class="form-group">
                <label>Verifiziert</label>
                <select name="verified">
                    <option value="0">Nicht verifiziert</option>
                    <option value="1">Verifiziert</option>
                </select>
            </div>
        </div>
        <div class="form-actions form-actions--pad">
            <button type="submit" class="btn btn-primary btn-sm">+ Nutzer anlegen</button>
        </div>
    </form>
</div>

<script>
// PW-Confirm für admin
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

// Rolle/Status Confirm via data-confirm-msg
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
</script>