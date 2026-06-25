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
        $username     = trim($_POST['username']     ?? '');
        $email        = trim($_POST['email']        ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $password     = $_POST['password']          ?? '';
        $role         = in_array($_POST['role'] ?? '', ['admin','user','partner']) ? $_POST['role'] : 'user';
        $aktiv        = isset($_POST['aktiv'])    && $_POST['aktiv']    === '1' ? 1 : 0;
        $verified     = isset($_POST['verified']) && $_POST['verified'] === '1' ? 1 : 0;
        $geburtsdatum = trim($_POST['geburtsdatum'] ?? '') ?: null;

        if ($username && $email && $display_name && $password) {
            try {
                $db->prepare("INSERT INTO users (username,email,password,display_name,role,geburtsdatum,verified,aktiv) VALUES (?,?,?,?,?,?,?,?)")
                   ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $display_name, $role, $geburtsdatum, $verified, $aktiv]);
                $newUid = (int)$db->lastInsertId();
                $db->prepare("INSERT INTO user_profiles (user_id,profile_name,is_default,sort_order) VALUES (?,?,1,0)")
                   ->execute([$newUid, $display_name]);
                header("Location: ?page=admin&msg=user_created"); exit;
            } catch (PDOException $e) {
                header("Location: ?page=admin&error=duplicate"); exit;
            }
        }
    }

    if ($act === 'update_user') {
        $uid          = (int)($_POST['user_id']      ?? 0);
        $display_name = trim($_POST['display_name']  ?? '');
        $geburtsdatum = trim($_POST['geburtsdatum']  ?? '') ?: null;
        if ($uid && $display_name) {
            $db->prepare("UPDATE users SET display_name=?, geburtsdatum=? WHERE id=?")
               ->execute([$display_name, $geburtsdatum, $uid]);
        }
        header("Location: ?page=admin&msg=saved"); exit;
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
        $role = in_array($_POST['role'] ?? '', ['admin','user','partner']) ? $_POST['role'] : 'user';
        if ($uid && $uid !== current_user_id()) {
            $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role, $uid]);
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
        $uid          = (int)($_POST['user_id']      ?? 0);
        $profile_name = trim($_POST['profile_name']  ?? '');
        $count = (int)$db->query("SELECT COUNT(*) FROM user_profiles WHERE user_id=$uid")->fetchColumn();
        if ($uid && $profile_name && $count < MAX_PROFILES) {
            try {
                $sort = (int)$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM user_profiles WHERE user_id=$uid")->fetchColumn();
                $db->prepare("INSERT INTO user_profiles (user_id,profile_name,is_default,sort_order) VALUES (?,?,0,?)")
                   ->execute([$uid, $profile_name, $sort]);
                if ($uid === current_user_id()) load_user_profiles($db, $uid);
                header("Location: ?page=admin&msg=profile_added"); exit;
            } catch (PDOException $e) {
                header("Location: ?page=admin&error=profile_exists"); exit;
            }
        }
    }

    if ($act === 'delete_profile') {
        $pid     = (int)($_POST['profile_id'] ?? 0);
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
        $uid      = (int)($_POST['user_id']    ?? 0);
        $new_pass = $_POST['new_password']     ?? '';
        if ($uid && strlen($new_pass) >= 8) {
            $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new_pass, PASSWORD_DEFAULT), $uid]);
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

$roleLabels = ['admin' => 'Admin', 'user' => 'Nutzer', 'partner' => 'Partner'];
$roleBadgeClass = ['admin' => 'badge--warning', 'user' => 'badge--muted', 'partner' => 'badge--success'];
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success"><?= $msgs[$_GET['msg']] ?? 'Gespeichert.' ?></div>
<?php elseif (isset($_GET['error'])): ?>
<div class="alert alert-error"><?= $errors[$_GET['error']] ?? 'Fehler.' ?></div>
<?php endif; ?>

<!-- ════ NUTZERÜBERSICHT ════ -->
<div class="card">
    <div class="card-head">
        <span class="card-title">Nutzerverwaltung</span>
        <span class="badge badge--muted"><?= count($users) ?> Nutzer</span>
    </div>

    <div class="admin-table-wrap">
        <div class="admin-user-header">
            <div>Nutzerdaten</div>
            <div>Display-Name / Geburtsdatum</div>
            <div>Profile</div>
            <div>Status</div>
            <div>Verifiziert</div>
            <div>Rolle</div>
            <div>Passwort</div>
        </div>

        <?php foreach ($users as $u): ?>
        <div class="admin-user-row <?= !$u['aktiv'] ? 'admin-user-inactive' : '' ?>">

            <!-- Nutzerdaten -->
            <div class="admin-cell">
                <div class="admin-user-name"><?= htmlspecialchars($u['username']) ?></div>
                <div class="admin-user-meta"><?= htmlspecialchars($u['email']) ?></div>
                <div class="admin-user-meta">📅 <?= date('d.m.Y', strtotime($u['created_at'])) ?></div>
            </div>

            <!-- Display-Name + Geburtsdatum -->
            <div class="admin-cell">
                <div class="admin-user-name"><?= htmlspecialchars($u['display_name']) ?></div>
                <div class="admin-user-meta">🎂 <?= !empty($u['geburtsdatum']) ? date('d.m.Y', strtotime($u['geburtsdatum'])) : '<span class="text-light">–</span>' ?></div>
                <button class="btn btn-xs btn-ghost btn-admin-edit-user"
                        data-id="<?= $u['id'] ?>"
                        data-display="<?= htmlspecialchars($u['display_name']) ?>"
                        data-geb="<?= htmlspecialchars($u['geburtsdatum'] ?? '') ?>">
                    Bearbeiten
                </button>
            </div>

            <!-- Profile -->
            <div class="admin-cell">
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

            <!-- Status -->
            <div class="admin-cell">
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
                <?php else: ?>
                <span class="badge badge--success">Aktiv (du)</span>
                <?php endif; ?>
            </div>

            <!-- Verifiziert -->
            <div class="admin-cell">
                <form method="POST" action="?page=admin" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="toggle_verified">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-xs <?= $u['verified'] ? 'btn-ok' : 'btn-ghost' ?>">
                        <?= $u['verified'] ? '✓ Ja' : '✗ Nein' ?>
                    </button>
                </form>
            </div>

            <!-- Rolle -->
            <div class="admin-cell">
                <?php if ($u['id'] !== current_user_id()): ?>
                <form method="POST" action="?page=admin" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="set_role">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <select name="role" class="input-sm" onchange="this.form.submit()">
                        <option value="user"    <?= $u['role']==='user'    ? 'selected' : '' ?>>Nutzer</option>
                        <option value="partner" <?= $u['role']==='partner' ? 'selected' : '' ?>>Partner</option>
                        <option value="admin"   <?= $u['role']==='admin'   ? 'selected' : '' ?>>Admin</option>
                    </select>
                </form>
                <?php else: ?>
                <span class="badge <?= $roleBadgeClass[$u['role']] ?? 'badge--muted' ?>"><?= $roleLabels[$u['role']] ?? $u['role'] ?></span>
                <?php endif; ?>
            </div>

            <!-- Passwort -->
            <div class="admin-cell">
                <form method="POST" action="?page=admin" class="admin-pw-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="reset_password">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="password" name="new_password" placeholder="Neues Passwort">
                    <button type="submit" class="btn btn-primary btn-xs admin-pw-confirm">Setzen</button>
                </form>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ════ NEUEN USER ANLEGEN ════ -->
<div class="card card--mt">
    <div class="card-head"><span class="card-title">Neuen Nutzer anlegen</span></div>
    <form method="POST" action="?page=admin">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="create_user">
        <div class="form-grid form-grid--compact">
            <div class="form-group">
                <label>Benutzername</label>
                <input type="text" name="username" placeholder="Benutzername" required>
            </div>
            <div class="form-group">
                <label>E-Mail</label>
                <input type="text" name="email" placeholder="email@beispiel.de" required inputmode="email" autocomplete="email">
            </div>
            <div class="form-group">
                <label>Display-Name</label>
                <input type="text" name="display_name" placeholder="z.B. Max" required>
            </div>
            <div class="form-group">
                <label>Geburtsdatum</label>
                <input type="date" name="geburtsdatum">
            </div>
            <div class="form-group">
                <label>Rolle</label>
                <select name="role">
                    <option value="user">Nutzer</option>
                    <option value="partner">Partner</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="aktiv">
                    <option value="1">Aktiv</option>
                    <option value="0">Inaktiv</option>
                </select>
            </div>
            <div class="form-group">
                <label>Verifiziert</label>
                <select name="verified">
                    <option value="0">Nicht verifiziert</option>
                    <option value="1">Verifiziert</option>
                </select>
            </div>
            <div class="form-group">
                <label>Passwort</label>
                <input type="password" name="password" placeholder="min. 8 Zeichen" required>
            </div>
        </div>
        <div class="form-actions form-actions--pad">
            <button type="submit" class="btn btn-primary btn-sm">+ Nutzer anlegen</button>
        </div>
    </form>
</div>

<!-- ════ EDIT MODAL ════ -->
<div id="admin-edit-modal" hidden>
    <div id="confirm-backdrop"></div>
    <div id="confirm-box" class="tr-modal-box">
        <h3 class="tr-modal-title">Nutzer bearbeiten</h3>
        <form method="POST" action="?page=admin">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="update_user">
            <input type="hidden" name="user_id" id="admin-edit-uid">
            <div class="form-group tr-modal-field">
                <label for="admin-edit-display">Display-Name</label>
                <input type="text" id="admin-edit-display" name="display_name" required>
            </div>
            <div class="form-group tr-modal-field-last">
                <label for="admin-edit-geb">Geburtsdatum</label>
                <input type="date" id="admin-edit-geb" name="geburtsdatum">
            </div>
            <div id="confirm-btns">
                <button type="button" class="btn btn-ghost btn-sm" id="admin-edit-cancel">Abbrechen</button>
                <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/admin.js"></script>