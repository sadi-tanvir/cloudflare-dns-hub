<?php
$title = "Domain Management";
ob_start();
?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 class="card-title">Add New Domain</h2>
    <form method="POST" action="?route=domains">
        <input type="hidden" name="action" value="add">
        <div class="form-grid" style="grid-template-columns: 1fr auto;">
            <div class="form-group">
                <input type="text" name="domain_name" placeholder="example.com" required>
            </div>
            <button type="submit" style="padding: 0.75rem 2rem;">Add</button>
        </div>
    </form>
</div>

<div class="card">
    <h2 class="card-title">Managed Domains</h2>
    
    <?php if (!empty($domains)): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Domain Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($domains as $domain): ?>
                    <tr>
                        <td><?= htmlspecialchars($domain['id']) ?></td>
                        <td style="font-weight: 500; font-size: 1.1rem;"><?= htmlspecialchars($domain['domain_name']) ?></td>
                        <td>
                            <div class="actions">
                                <button type="button" class="btn-sm btn-secondary" onclick="openDomainEditModal('<?= htmlspecialchars($domain['id']) ?>', '<?= htmlspecialchars($domain['domain_name']) ?>')">Edit</button>
                                <form method="POST" action="?route=domains" onsubmit="return confirm('Are you sure you want to remove this domain?');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="domain_id" value="<?= htmlspecialchars($domain['id']) ?>">
                                    <button type="submit" class="btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No domains configured.</p>
    <?php endif; ?>
</div>

<!-- Edit Domain Modal -->
<div class="modal-overlay" id="editDomainModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Domain</h2>
            <button class="modal-close" onclick="closeDomainEditModal()">&times;</button>
        </div>
        <form method="POST" action="?route=domains">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="domain_id" id="edit_domain_id">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Domain Name</label>
                <input type="text" name="domain_name" id="edit_domain_name" required>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn-sm btn-secondary" onclick="closeDomainEditModal()" style="font-size: 1rem; padding: 0.75rem 1.5rem;">Cancel</button>
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDomainEditModal(id, name) {
        document.getElementById('edit_domain_id').value = id;
        document.getElementById('edit_domain_name').value = name;
        document.getElementById('editDomainModal').classList.add('active');
    }

    function closeDomainEditModal() {
        document.getElementById('editDomainModal').classList.remove('active');
    }

    document.getElementById('editDomainModal').addEventListener('click', function(e) {
        if (e.target === this) closeDomainEditModal();
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
