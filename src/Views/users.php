<?php
$title = "User Management";
ob_start();
?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 class="card-title">Add New User</h2>
    <form method="POST" action="?route=users">
        <input type="hidden" name="action" value="add">
        <div class="form-grid" style="grid-template-columns: 1fr 1fr auto;">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" style="padding: 0.75rem 2rem;">Add User</button>
        </div>
    </form>
</div>

<div class="card">
    <h2 class="card-title">System Users</h2>
    
    <?php if (!empty($users)): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td style="font-weight: 500; font-size: 1.1rem;"><?= htmlspecialchars($user['username']) ?></td>
                        <td>
                            <div class="actions">
                                <button type="button" class="btn-sm btn-secondary" onclick="openPasswordModal('<?= htmlspecialchars($user['id']) ?>', '<?= htmlspecialchars($user['username']) ?>')">Change Password</button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" action="?route=users" onsubmit="return confirm('Are you sure you want to remove this user?');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                    <button type="submit" class="btn-sm btn-danger">Delete</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No users found.</p>
    <?php endif; ?>
</div>

<!-- Change Password Modal -->
<div class="modal-overlay" id="passwordModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Change Password for <span id="pwd_username_display" style="color: var(--primary);"></span></h2>
            <button class="modal-close" onclick="closePasswordModal()">&times;</button>
        </div>
        <form method="POST" action="?route=users">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="user_id" id="pwd_user_id">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn-sm btn-secondary" onclick="closePasswordModal()" style="font-size: 1rem; padding: 0.75rem 1.5rem;">Cancel</button>
                <button type="submit">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPasswordModal(id, username) {
        document.getElementById('pwd_user_id').value = id;
        document.getElementById('pwd_username_display').innerText = username;
        document.getElementById('passwordModal').classList.add('active');
    }

    function closePasswordModal() {
        document.getElementById('passwordModal').classList.remove('active');
    }

    document.getElementById('passwordModal').addEventListener('click', function(e) {
        if (e.target === this) closePasswordModal();
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
