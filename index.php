<?php
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

// 2. Handle Add DNS Record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_record' && $zoneId) {
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
    
    $addRes = addDnsRecord($zoneId, $type, $name, $content, $ttl, $proxied);
    if ($addRes['success']) {
        $successMsg = "DNS Record added successfully!";
    } else {
        $errorMsg = $addRes['error'];
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
        <h1>DNS Manager</h1>
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
                    <input type="checkbox" name="proxied" id="proxied" checked>
                    <label for="proxied">Proxy status</label>
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

</body>
</html>
