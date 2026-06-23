<?php
// ════════════════════════════════════════════════
// pages/admin.php – Admin-Panel
//
// Nur für Nutzer mit role='admin' zugänglich.
// Funktionen:
//   - Nutzerübersicht (alle registrierten User)
//   - Neuen User anlegen (mit Profil)
//   - User aktivieren/deaktivieren
//   - Rolle ändern (admin/user)
//   - Profile eines Users verwalten
//   - MAX_PROFILES Limit anpassen
// ════════════════════════════════════════════════

$db = get_db();

// ── Zugriff nur für Admins ──
if (!is_admin()) {
    echo '<div class="alert alert-error">⚠ Kein Zugriff. Diese Seite ist nur für Administratoren.</div>';
    return;
}

// ════════════════════════════════════════════════
// POST-Handler
// ════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    // ── Neuen User anlegen ──
    if ($act === 'create_user') {
        $username     = trim($_POST['username'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $password     = $_POST['password'] ?? '';
        $role         = $_POST['role'] ?? 'user';

        if ($username && $email && $display_name && $password) {
            try {
                $db->prepare("INSERT INTO users (username,email,password,display_name,role) VALUES (?,?,?,?,?)")
                   ->execute([$username,$email,password_hash($password,PASSWORD_DEFAULT),$display_name,$role]);
                $uid = (int)$db->lastInsertId();
                // Default-Profil anlegen
                $db->prepare("INSERT INTO user_profiles (user_id,profile_name,is_default,sort_order) VALUES (?,?,1,0)")
                   ->execute([$uid,$display_name]);
                header("Location: ?page=admin&msg=user_created"); exit;
            } catch (PDOException $e) {
                header("Location: ?page=admin&error=duplicate"); exit;
            }
        }
    }

    // ── User aktivieren/deaktivieren ──
    if ($act === 'toggle_user') {
        $uid = (int)($_POST['user_id'] ?? 0);
        if ($uid && $uid !== current_user_id()) { // Sich selbst nicht deaktivieren
            $db->prepare("UPDATE users SET aktiv = 1-aktiv WHERE id=?")->execute([$uid]);
        }
        header("Location: ?page=admin&msg=saved"); exit;
    }

    // ── Rolle ändern ──
    if ($act === 'set_role') {
        $uid  = (int)($_POST['user_id'] ?? 0);
        $role = in_array($_POST['role']??'', ['admin','user']) ? $_POST['role'] : 'user';
        if ($uid && $uid !== current_user_id()) {
            $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role,$uid]);
        }
        header("Location: ?page=admin&msg=saved"); exit;
    }

    // ── Profil zu User hinzufügen ──
    if ($act === 'add_profile') {
        $uid          = (int)($_POST['user_id'] ?? 0);
        $profile_name = trim($_POST['profile_name'] ?? '');

        // Anzahl bestehender Profile prüfen
        $count = (int)$db->prepare("SELECT COUNT(*) FROM user_profiles WHERE user_id=?")
                         ->execute([$uid]) ? $db->query("SELECT COUNT(*) FROM user_profiles WHERE user_id=$uid")->fetchColumn() : 0;

        if ($uid && $profile_name && $count < MAX_PROFILES) {
            try {
                $sort = (int)$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM user_profiles WHERE user_id=$uid")->fetchColumn();
                $db->prepare("INSERT INTO user_profiles (user_id,profile_name,is_default,sort_order) VALUES (?,?,0,?)")
                   ->execute([$uid,$profile_name,$sort]);
                // Session-Profile neu laden wenn eigener User
                if ($uid === current_user_id()) load_user_profiles($db, $uid);
                header("Location: ?page=admin&msg=profile_added"); exit;
            } catch (PDOException $e) {
                header("Location: ?page=admin&error=profile_exists"); exit;
            }
        }
    }

    // ── Profil löschen ──
    if ($act === 'delete_profile') {
        $pid = (int)($_POST['profile_id'] ?? 0);
        // Default-Profil nicht löschen
        $profile = $db->prepare("SELECT * FROM user_profiles WHERE id=?");
        $profile->execute([$pid]);
        $profile = $profile->fetch();
        if ($profile && !$profile['is_default']) {
            $db->prepare("DELETE FROM user_profiles WHERE id=?")->execute([$pid]);
            if ((int)$profile['user_id'] === current_user_id()) load_user_profiles($db, current_user_id());
        }
        header("Location: ?page=admin&msg=saved"); exit;
    }

    // ── Passwort zurücksetzen ──
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

// ── Daten laden ──
$users    = $db->query("SELECT u.*, (SELECT COUNT(*) FROM user_profiles WHERE user_id=u.id) as profile_count FROM users u ORDER BY u.created_at ASC")->fetchAll();
$profiles = $db->query("SELECT p.*, u.username FROM user_profiles p JOIN users u ON p.user_id=u.id ORDER BY p.user_id, p.sort_order")->fetchAll();

// Profile nach user_id gruppieren
$profiles_by_user = [];
foreach ($profiles as $p) $profiles_by_user[$p['user_id']][] = $p;

$msgs = ['user_created'=>'User angelegt.','saved'=>'Gespeichert.','profile_added'=>'Profil hinzugefügt.','password_reset'=>'Passwort zurückgesetzt.'];
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
        <div style="font-size:13px;color:var(--text-muted)">
            Max. Profile pro Nutzer: <strong><?= MAX_PROFILES ?></strong>
        </div>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Username</th><th>Display-Name</th><th>E-Mail</th>
            <th>Rolle</th><th>Profile</th><th>Registriert</th><th>Status</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr class="<?= !$u['aktiv']?'row-done':'' ?>">
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['display_name']) ?></td>
            <td style="font-size:12px"><?= htmlspecialchars($u['email']) ?></td>
            <td>
                <span class="badge <?= $u['role']==='admin'?'badge-ok':'badge-neutral' ?>"><?= $u['role'] ?></span>
            </td>
            <td>
                <!-- Profile des Users anzeigen -->
                <?php foreach ($profiles_by_user[$u['id']] ?? [] as $p): ?>
                <span class="badge badge-neutral" style="margin-right:3px">
                    <?= htmlspecialchars($p['profile_name']) ?>
                    <?= $p['is_default'] ? ' ★' : '' ?>
                    <?php if (!$p['is_default']): ?>
                    <form method="POST" action="?page=admin" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="delete_profile">
                        <input type="hidden" name="profile_id" value="<?= $p['id'] ?>">
                        <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--red);font-size:10px;padding:0 2px" title="Profil löschen">✕</button>
                    </form>
                    <?php endif; ?>
                </span>
                <?php endforeach; ?>
                <!-- Profil hinzufügen wenn unter Limit -->
                <?php if (($u['profile_count'] ?? 0) < MAX_PROFILES): ?>
                <form method="POST" action="?page=admin" class="form-inline" style="display:inline-flex;gap:4px;margin-left:4px">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="add_profile">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="text" name="profile_name" placeholder="+ Profil" style="width:80px;padding:2px 6px;font-size:12px;border:1px solid var(--border);border-radius:5px">
                    <button type="submit" class="btn btn-primary btn-xs">+</button>
                </form>
                <?php endif; ?>
            </td>
            <td style="font-size:12px"><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
            <td><span class="badge <?= $u['aktiv']?'badge-ok':'badge-neutral' ?>"><?= $u['aktiv']?'Aktiv':'Inaktiv' ?></span></td>
            <td class="col-actions" style="display:flex;gap:4px;flex-wrap:wrap">
                <?php if ($u['id'] !== current_user_id()): ?>
                <!-- Aktivieren/Deaktivieren -->
                <form method="POST" action="?page=admin" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="toggle_user">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-xs"><?= $u['aktiv']?'Deakt.':'Akt.' ?></button>
                </form>
                <!-- Rolle ändern -->
                <form method="POST" action="?page=admin" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="set_role">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="role" value="<?= $u['role']==='admin'?'user':'admin' ?>">
                    <button type="submit" class="btn btn-ghost btn-xs"><?= $u['role']==='admin'?'→ User':'→ Admin' ?></button>
                </form>
                <?php endif; ?>
                <!-- Passwort zurücksetzen -->
                <form method="POST" action="?page=admin" class="form-inline" style="display:inline-flex;gap:4px">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="reset_password">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="password" name="new_password" placeholder="Neues PW" style="width:100px;padding:2px 6px;font-size:12px;border:1px solid var(--border);border-radius:5px">
                    <button type="submit" class="btn btn-ghost btn-xs">PW setzen</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<!-- ════ NEUEN USER ANLEGEN ════ -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neuen Nutzer anlegen</h2></div>
    <form method="POST" action="?page=admin">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="create_user">
        <div class="form-grid">
            <div class="form-group">
                <label>Display-Name (Profilname)</label>
                <input type="text" name="display_name" placeholder="z.B. Kim" required>
            </div>
            <div class="form-group">
                <label>Benutzername</label>
                <input type="text" name="username" placeholder="Benutzername" required>
            </div>
            <div class="form-group">
                <label>E-Mail</label>
                <input type="email" name="email" placeholder="email@beispiel.de" required>
            </div>
            <div class="form-group">
                <label>Passwort</label>
                <input type="password" name="password" placeholder="Passwort (min. 8 Zeichen)" required>
            </div>
            <div class="form-group">
                <label>Rolle</label>
                <select name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="form-actions form-actions--pad">
            <button type="submit" class="btn btn-primary btn-sm">+ Nutzer anlegen</button>
        </div>
    </form>
</div>