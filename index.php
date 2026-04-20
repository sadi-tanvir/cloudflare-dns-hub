<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/cloudflare_api.php';

$selectedDomain = $_GET['domain'] ?? $domains[0];
$errorMsg = '';
$successMsg = '';
$zoneId = '';
$dnsRecords = [];

// 1. Get Zone ID
$zoneData = getZoneId($selectedDomain);
if ($zoneData['success']) {
    $zoneId = $zoneData['zone_id'];
} else {
    $errorMsg = $zoneData['error'];
}

// 2. Handle Form Submissions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $zoneId) {
    if ($_POST['action'] === 'delete_record' && isset($_POST['record_id'])) {
        $deleteRes = deleteDnsRecord($zoneId, $_POST['record_id']);
        if ($deleteRes['success']) {
            $successMsg = "DNS Record deleted successfully!";
        } else {
            $errorMsg = $deleteRes['error'];
        }
    } else {
        $type = $_POST['type'] ?? 'A';
        $name = $_POST['name'] ?? '';
        if ($name === '@') {
            $name = $selectedDomain;
        } elseif (strpos($name, $selectedDomain) === false) {
            $name = $name . '.' . $selectedDomain;
        }
        
        $content = $_POST['content'] ?? '';
        $ttl = (int)($_POST['ttl'] ?? 1);
        $proxied = isset($_POST['proxied']) ? true : false;
        
        if ($_POST['action'] === 'add_record') {
            $addRes = addDnsRecord($zoneId, $type, $name, $content, $ttl, $proxied);
            if ($addRes['success']) {
                $successMsg = "DNS Record added successfully!";
            } else {
                $errorMsg = $addRes['error'];
            }
        } elseif ($_POST['action'] === 'edit_record' && isset($_POST['record_id'])) {
            $updateRes = updateDnsRecord($zoneId, $_POST['record_id'], $type, $name, $content, $ttl, $proxied);
            if ($updateRes['success']) {
                $successMsg = "DNS Record updated successfully!";
            } else {
                $errorMsg = $updateRes['error'];
            }
        }
    }
}

// 3. Fetch DNS Records
if ($zoneId) {
    $recordsData = getDnsRecords($zoneId);
    if ($recordsData['success']) {
        $dnsRecords = $recordsData['records'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloudflare DNS Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>DNS Manager</h1>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Logged in as <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="domain-selector">
                <form method="GET" action="">
                    <select name="domain" onchange="this.form.submit()">
                        <?php foreach ($domains as $domain): ?>
                            <option value="<?= htmlspecialchars($domain) ?>" <?= $domain === $selectedDomain ? 'selected' : '' ?>>
                                <?= htmlspecialchars($domain) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <a href="logout.php" class="btn-sm btn-danger" style="text-decoration: none; padding: 0.75rem 1rem;">Logout</a>
        </div>
    </div>

    <?php if ($errorMsg): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <?php if ($successMsg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2 class="card-title">Add DNS Record</h2>
        <form method="POST" action="?domain=<?= urlencode($selectedDomain) ?>">
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
        <h2 class="card-title">DNS Records for <?= htmlspecialchars($selectedDomain) ?></h2>
        
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
                                    <form method="POST" action="?domain=<?= urlencode($selectedDomain) ?>" onsubmit="return confirm('Are you sure you want to delete this record?');">
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
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit DNS Record</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" action="?domain=<?= urlencode($selectedDomain) ?>">
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

</body>
</html>
