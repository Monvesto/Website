<?php
// ════════════════════════════════════════════════
// pages/profil.php – Eigene Profilverwaltung
//
// Jeder eingeloggte User kann hier:
//   - Display-Name ändern
//   - Passwort ändern
//   - Weitere Profile hinzufügen (bis MAX_PROFILES)
//   - Profile löschen (außer Default)
// ════════════════════════════════════════════════

$db      = get_db();
$user_id = current_user_id();

// ── POST-Handler ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    // Eigene Daten aktualisieren
    if ($act === 'update_profile') {
        $display_name = trim($_POST['display_name'] ?? '');
        if ($display_name) {
            $db->prepare("UPDATE users SET display_name=? WHERE id=?")->execute([$display_name, $user_id]);
            $_SESSION['username'] = $display_name;
        }
        header("Location: ?page=profil&msg=saved"); exit;
    }

    // Passwort ändern
    if ($act === 'change_password') {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $new2 = $_POST['new_password2'] ?? '';
        $user = $db->prepare("SELECT password FROM users WHERE id=?");
        $user->execute([$user_id]);
        $hash = $user->fetchColumn();
        if (!password_verify($old, $hash)) {
            header("Location: ?page=profil&error=wrong_password"); exit;
        }
        if (strlen($new) < 8) {
            header("Location: ?page=profil&error=too_short"); exit;
        }
        if ($new !== $new2) {
            header("Location: ?page=profil&error=mismatch"); exit;
        }
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new,PASSWORD_DEFAULT), $user_id]);
        header("Location: ?page=profil&msg=password_changed"); exit;
    }

    // Profil hinzufügen
    if ($act === 'add_profile') {
        $profile_name = trim($_POST['profile_name'] ?? '');
        $count = (int)$db->query("SELECT COUNT(*) FROM user_profiles WHERE user_id=$user_id")->fetchColumn();
        if ($profile_name && $count < MAX_PROFILES) {
            try {
                $sort = (int)$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM user_profiles WHERE user_id=$user_id")->fetchColumn();
                $db->prepare("INSERT INTO user_profiles (user_id,profile_name,is_default,sort_order) VALUES (?,?,0,?)")
                   ->execute([$user_id,$profile_name,$sort]);
                load_user_profiles($db, $user_id);
                header("Location: ?page=profil&msg=profile_added"); exit;
            } catch (PDOException $e) {
                header("Location: ?page=profil&error=profile_exists"); exit;
            }
        }
    }

    // Profil löschen
    if ($act === 'delete_profile') {
        $pid = (int)($_POST['profile_id'] ?? 0);
        $profile = $db->prepare("SELECT * FROM user_profiles WHERE id=? AND user_id=?");
        $profile->execute([$pid, $user_id]);
        $profile = $profile->fetch();
        if ($profile && !$profile['is_default']) {
            $db->prepare("DELETE FROM user_profiles WHERE id=?")->execute([$pid]);
            load_user_profiles($db, $user_id);
        }
        header("Location: ?page=profil&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$user     = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$user_id]);
$user     = $user->fetch();
$profiles = $db->prepare("SELECT * FROM user_profiles WHERE user_id=? ORDER BY is_default DESC, sort_order");
$profiles->execute([$user_id]);
$profiles = $profiles->fetchAll();

$msgs   = ['saved'=>'Gespeichert.','password_changed'=>'Passwort geändert.','profile_added'=>'Profil hinzugefügt.'];
$errors = ['wrong_password'=>'Aktuelles Passwort falsch.','too_short'=>'Neues Passwort zu kurz (min. 8 Zeichen).','mismatch'=>'Passwörter stimmen nicht überein.','profile_exists'=>'Profil mit diesem Namen existiert bereits.'];
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success"><?= $msgs[$_GET['msg']] ?? 'Gespeichert.' ?></div>
<?php elseif (isset($_GET['error'])): ?>
<div class="alert alert-error"><?= $errors[$_GET['error']] ?? 'Fehler.' ?></div>
<?php endif; ?>

<div class="dashboard-row mt-4">

    <!-- ── Profil-Daten ── -->
    <div class="card">
        <div class="card-head"><h2 class="card-title">👤 Mein Account</h2></div>
        <form method="POST" action="?page=profil">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="update_profile">
            <div class="form-grid">
                <div class="form-group">
                    <label>Benutzername</label>
                    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled style="background:var(--bg)">
                </div>
                <div class="form-group">
                    <label>E-Mail</label>
                    <input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:var(--bg)">
                </div>
                <div class="form-group">
                    <label>Display-Name</label>
                    <input type="text" name="display_name" value="<?= htmlspecialchars($user['display_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Rolle</label>
                    <input type="text" value="<?= htmlspecialchars($user['role']) ?>" disabled style="background:var(--bg)">
                </div>
            </div>
            <div class="form-actions form-actions--pad">
                <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
            </div>
        </form>
    </div>

    <!-- ── Passwort ändern ── -->
    <div class="card">
        <div class="card-head"><h2 class="card-title">🔒 Passwort ändern</h2></div>
        <form method="POST" action="?page=profil">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="change_password">
            <div class="form-grid">
                <div class="form-group fg-wide">
                    <label>Aktuelles Passwort</label>
                    <input type="password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label>Neues Passwort</label>
                    <input type="password" name="new_password" placeholder="min. 8 Zeichen" required>
                </div>
                <div class="form-group">
                    <label>Passwort bestätigen</label>
                    <input type="password" name="new_password2" required>
                </div>
            </div>
            <div class="form-actions form-actions--pad">
                <button type="submit" class="btn btn-primary btn-sm">Passwort ändern</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Profile verwalten ── -->
<div class="card mt-4">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">🪪 Meine Profile</h2>
            <span class="badge badge-neutral"><?= count($profiles) ?> / <?= MAX_PROFILES ?></span>
        </div>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Profilname</th><th>Standard</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($profiles as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['profile_name']) ?></td>
            <td><?= $p['is_default'] ? '<span class="badge badge-ok">Standard</span>' : '' ?></td>
            <td class="col-actions">
                <?php if (!$p['is_default']): ?>
                <form method="POST" action="?page=profil" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete_profile">
                    <input type="hidden" name="profile_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕ Löschen</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>

    <?php if (count($profiles) < MAX_PROFILES): ?>
    <div style="padding:16px 24px;border-top:0.5px solid var(--border)">
        <form method="POST" action="?page=profil" style="display:flex;gap:10px;align-items:center">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="add_profile">
            <input type="text" name="profile_name" placeholder="Neues Profil (z.B. Kim)" style="max-width:220px" required>
            <button type="submit" class="btn btn-primary btn-sm">+ Profil hinzufügen</button>
        </form>
        <p style="font-size:12px;color:var(--text-muted);margin-top:8px">
            Du kannst noch <?= MAX_PROFILES - count($profiles) ?> weiteres Profil hinzufügen.
            Profile erscheinen als Person-Filter in allen Seiten.
        </p>
    </div>
    <?php else: ?>
    <p style="padding:16px 24px;font-size:13px;color:var(--text-muted);border-top:0.5px solid var(--border)">
        Maximum von <?= MAX_PROFILES ?> Profilen erreicht.
    </p>
    <?php endif; ?>
</div>