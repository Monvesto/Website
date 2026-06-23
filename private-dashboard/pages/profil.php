<?php
// ════════════════════════════════════════════════
// pages/profil.php – Eigene Profilverwaltung
// ════════════════════════════════════════════════

$db      = get_db();
$user_id = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    if ($act === 'update_profile') {
        $username     = trim($_POST['username'] ?? '');
        $geburtsdatum = trim($_POST['geburtsdatum'] ?? '') ?: null;

        if ($username === '') {
            header("Location: ?page=profil&error=username_empty"); exit;
        }
        if (strlen($username) < 3) {
            header("Location: ?page=profil&error=username_short"); exit;
        }
        $check = $db->prepare("SELECT id FROM users WHERE username=? AND id!=?");
        $check->execute([$username, $user_id]);
        if ($check->fetch()) {
            header("Location: ?page=profil&error=username_taken"); exit;
        }

        // Geburtsdatum nur updaten wenn noch nicht gesetzt
        $existing = $db->prepare("SELECT geburtsdatum FROM users WHERE id=?");
        $existing->execute([$user_id]);
        $existing_geb = $existing->fetchColumn();

        if ($existing_geb) {
            // Geburtsdatum bereits gesetzt – nicht überschreiben
            $db->prepare("UPDATE users SET username=? WHERE id=?")
               ->execute([$username, $user_id]);
        } else {
            $db->prepare("UPDATE users SET username=?, geburtsdatum=? WHERE id=?")
               ->execute([$username, $geburtsdatum, $user_id]);
        }

        $_SESSION['username'] = $username;

        if (is_admin()) {
            $role = trim($_POST['role'] ?? 'user');
            if (in_array($role, ['user', 'admin'], true)) {
                $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role, $user_id]);
            }
        }

        header("Location: ?page=profil&msg=saved"); exit;
    }

    if ($act === 'change_password') {
        $old  = $_POST['old_password'] ?? '';
        $new  = $_POST['new_password'] ?? '';
        $new2 = $_POST['new_password2'] ?? '';

        $stmt = $db->prepare("SELECT password FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($old, $hash)) {
            header("Location: ?page=profil&error=wrong_password"); exit;
        }
        if (strlen($new) < 8) {
            header("Location: ?page=profil&error=too_short"); exit;
        }
        if ($new !== $new2) {
            header("Location: ?page=profil&error=mismatch"); exit;
        }
        $db->prepare("UPDATE users SET password=? WHERE id=?")
           ->execute([password_hash($new, PASSWORD_DEFAULT), $user_id]);
        header("Location: ?page=profil&msg=password_changed"); exit;
    }

    if ($act === 'add_profile') {
        $profile_name = trim($_POST['profile_name'] ?? '');
        $count = (int)$db->query("SELECT COUNT(*) FROM user_profiles WHERE user_id=$user_id")->fetchColumn();
        if ($profile_name && $count < MAX_PROFILES) {
            try {
                $sort = (int)$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM user_profiles WHERE user_id=$user_id")->fetchColumn();
                $db->prepare("INSERT INTO user_profiles (user_id, profile_name, is_default, sort_order) VALUES (?,?,0,?)")
                   ->execute([$user_id, $profile_name, $sort]);
                load_user_profiles($db, $user_id);
                header("Location: ?page=profil&msg=profile_added"); exit;
            } catch (PDOException $e) {
                header("Location: ?page=profil&error=profile_exists"); exit;
            }
        }
        header("Location: ?page=profil"); exit;
    }

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

$stmt = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$profiles = $db->prepare("SELECT * FROM user_profiles WHERE user_id=? ORDER BY is_default DESC, sort_order");
$profiles->execute([$user_id]);
$profiles = $profiles->fetchAll();

$role_labels = ['admin' => 'Administrator', 'user' => 'Nutzer'];
$role_label  = $role_labels[$user['role']] ?? ucfirst($user['role']);

$has_geburtsdatum = !empty($user['geburtsdatum']);

$msgs = [
    'saved'            => 'Änderungen gespeichert.',
    'password_changed' => 'Passwort erfolgreich geändert.',
    'profile_added'    => 'Profil hinzugefügt.',
];
$errors = [
    'wrong_password' => 'Aktuelles Passwort ist falsch.',
    'too_short'      => 'Neues Passwort muss mindestens 8 Zeichen haben.',
    'mismatch'       => 'Die Passwörter stimmen nicht überein.',
    'profile_exists' => 'Ein Profil mit diesem Namen existiert bereits.',
    'username_empty' => 'Benutzername darf nicht leer sein.',
    'username_short' => 'Benutzername muss mindestens 3 Zeichen haben.',
    'username_taken' => 'Dieser Benutzername ist bereits vergeben.',
];
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($msgs[$_GET['msg']] ?? 'Gespeichert.') ?></div>
<?php elseif (isset($_GET['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($errors[$_GET['error']] ?? 'Ein Fehler ist aufgetreten.') ?></div>
<?php endif; ?>

<div class="dashboard-row mt-4">

    <!-- ── Account-Daten ── -->
    <div class="card">
        <div class="card-head"><h2 class="card-title">👤 Mein Account</h2></div>
        <form method="POST" action="?page=profil" data-confirm="Benutzernamen ändern? Der alte Benutzername wird danach ungültig.">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="update_profile">
            <div class="form-grid">

                <div class="form-group">
                    <label>Benutzername <span class="tooltip-icon" data-tip="Nach der Änderung ist der alte Benutzername ungültig.">?</span></label>
                    <input type="text" name="username"
                        value="<?= htmlspecialchars($user['username']) ?>"
                        minlength="3" required>
                </div>

                <div class="form-group">
                    <label>E-Mail <span class="tooltip-icon" data-tip="Änderung nur über den Support möglich.">?</span></label>
                    <div class="field-wrap">
                        <input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                </div>

                <div class="form-group">
                    <label>Geburtsdatum<?php if ($has_geburtsdatum): ?> <span class="tooltip-icon" data-tip="Änderung nur über den Support möglich.">?</span><?php endif; ?></label>
                    <?php if ($has_geburtsdatum): ?>
                    <input type="date" value="<?= htmlspecialchars($user['geburtsdatum']) ?>" disabled>
                    <?php else: ?>
                    <input type="date" name="geburtsdatum">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Rolle</label>
                    <?php if (is_admin()): ?>
                    <select name="role">
                        <option value="user"  <?= $user['role'] === 'user'  ? 'selected' : '' ?>>Nutzer</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    </select>
                    <?php else: ?>
                    <input type="text" value="<?= htmlspecialchars($role_label) ?>" disabled>
                    <?php endif; ?>
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
            <div class="form-grid form-grid--profil">
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

    <div>
        <?php foreach ($profiles as $p): ?>
        <div class="profil-profile-row">
            <span class="profil-profile-name"><?= htmlspecialchars($p['profile_name']) ?></span>
            <?php if ($p['is_default']): ?>
                <span class="badge badge-ok">Standard</span>
            <?php else: ?>
                <form method="POST" action="?page=profil" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete_profile">
                    <input type="hidden" name="profile_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕ Löschen</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="profil-add-row">
        <?php if (count($profiles) < MAX_PROFILES): ?>
        <form method="POST" action="?page=profil" class="profil-add-form">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="add_profile">
            <input type="text" name="profile_name" placeholder="Neues Profil (z.B. Max)" required>
            <button type="submit" class="btn btn-primary btn-sm">+ Profil hinzufügen</button>
            <span class="form-hint">Noch <?= MAX_PROFILES - count($profiles) ?> weiteres Profil möglich. Profile erscheinen als Person-Filter in allen Seiten.</span>
        </form>
        <?php else: ?>
        <p class="form-hint">Maximum von <?= MAX_PROFILES ?> Profilen erreicht.</p>
        <?php endif; ?>
    </div>
</div>