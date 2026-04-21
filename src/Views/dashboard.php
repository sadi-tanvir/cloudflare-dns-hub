<?php
$title = "DNS Manager Dashboard";
ob_start();
?>

<div class="domain-selector" style="margin-bottom: 2rem;">
    <form method="GET" action="index.php">
        <input type="hidden" name="route" value="dashboard">
        <div style="display: flex; gap: 1rem; align-items: center;">
            <label style="font-weight: 500;">Select Domain:</label>
            <select name="domain" onchange="this.form.submit()" style="max-width: 300px;">
                <?php foreach ($domainList as $d): ?>
                    <option value="<?= htmlspecialchars($d) ?>" <?= $d === $selectedDomain ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($selectedDomain): ?>
<div class="card" style="margin-bottom: 2rem;">
    <h2 class="card-title">Add DNS Record for <?= htmlspecialchars($selectedDomain) ?></h2>
    <form method="POST" action="?route=dashboard&domain=<?= urlencode($selectedDomain) ?>">
        <input type="hidden" name="action" value="add_record">
        
        <div class="form-grid">
            <div class="form-group">
                <label>Type</label>
                <select name="type" required>
                    <option value="A">A</option>
                    <option value="AAAA">AAAA</option>
                    <option value="CNAME">CNAME</option>
                    <option value="TXT">TXT</option>
                    <option value="MX">MX</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="@ for root or subdomain" required>
            </div>
            
            <div class="form-group">
                <label>Content</label>
                <input type="text" name="content" placeholder="IP address or target" required>
            </div>
            
            <div class="form-group">
                <label>TTL</label>
                <select name="ttl">
                    <option value="1">Auto</option>
                    <option value="120">2 min</option>
                    <option value="300">5 min</option>
                    <option value="3600">1 hr</option>
                </select>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" name="proxied" id="proxied_add" checked>
                <label for="proxied_add">Proxy status</label>
            </div>
        </div>
        
        <button type="submit">Save Record</button>
    </form>
</div>

<div class="card">
    <h2 class="card-title">DNS Records</h2>
    
    <?php if (!empty($dnsRecords)): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Content</th>
                    <th>Proxy Status</th>
                    <th>TTL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dnsRecords as $record): ?>
                    <tr>
                        <td><span class="badge type"><?= htmlspecialchars($record['type']) ?></span></td>
                        <td style="font-weight: 500;"><?= htmlspecialchars($record['name']) ?></td>
                        <td style="font-family: monospace; color: var(--text-muted);"><?= htmlspecialchars($record['content']) ?></td>
                        <td>
                            <?php if ($record['proxied']): ?>
                                <span class="badge proxied">Proxied</span>
                            <?php else: ?>
                                <span class="badge dns-only">DNS Only</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $record['ttl'] === 1 ? 'Auto' : htmlspecialchars($record['ttl']) ?></td>
                        <td>
                            <div class="actions">
                                <button class="btn-sm btn-secondary" onclick="openEditModal('<?= htmlspecialchars($record['id']) ?>', '<?= htmlspecialchars($record['type']) ?>', '<?= htmlspecialchars($record['name']) ?>', '<?= htmlspecialchars(addslashes($record['content'])) ?>', '<?= $record['ttl'] ?>', <?= $record['proxied'] ? 'true' : 'false' ?>)">Edit</button>
                                <form method="POST" action="?route=dashboard&domain=<?= urlencode($selectedDomain) ?>" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                    <input type="hidden" name="action" value="delete_record">
                                    <input type="hidden" name="record_id" value="<?= htmlspecialchars($record['id']) ?>">
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
        <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No records found or unable to fetch.</p>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit DNS Record</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" action="?route=dashboard&domain=<?= urlencode($selectedDomain) ?>">
            <input type="hidden" name="action" value="edit_record">
            <input type="hidden" name="record_id" id="edit_record_id">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="edit_type" required>
                        <option value="A">A</option>
                        <option value="AAAA">AAAA</option>
                        <option value="CNAME">CNAME</option>
                        <option value="TXT">TXT</option>
                        <option value="MX">MX</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="edit_name" placeholder="@ for root" required>
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Content</label>
                    <input type="text" name="content" id="edit_content" placeholder="IP address or target" required>
                </div>
                
                <div class="form-group">
                    <label>TTL</label>
                    <select name="ttl" id="edit_ttl">
                        <option value="1">Auto</option>
                        <option value="120">2 min</option>
                        <option value="300">5 min</option>
                        <option value="3600">1 hr</option>
                    </select>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="proxied" id="edit_proxied">
                    <label for="edit_proxied">Proxy status</label>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn-sm btn-secondary" onclick="closeEditModal()" style="font-size: 1rem; padding: 0.75rem 1.5rem;">Cancel</button>
                <button type="submit">Update Record</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, type, name, content, ttl, proxied) {
        document.getElementById('edit_record_id').value = id;
        document.getElementById('edit_type').value = type;
        
        let domain = '<?= htmlspecialchars($selectedDomain) ?>';
        if (name === domain) {
            document.getElementById('edit_name').value = '@';
        } else {
            document.getElementById('edit_name').value = name.replace('.' + domain, '');
        }
        
        document.getElementById('edit_content').value = content;
        document.getElementById('edit_ttl').value = ttl;
        document.getElementById('edit_proxied').checked = proxied;
        
        document.getElementById('editModal').classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
