<?php
// Configuration
$apiToken = 'cfut_B1UwasfadfsG5XiywqE173u8gGBLPUD07iz3zasdfsdafa2167aab3';
$domains = [
    'bsdbdisp.com',
    // Add more domains here as needed
];

$selectedDomain = $_GET['domain'] ?? $domains[0];
$errorMsg = '';
$successMsg = '';
$zoneId = '';
$dnsRecords = [];

// Helper to make API requests
function cloudflareApiRequest($endpoint, $method = 'GET', $data = null) {
    global $apiToken;
    $url = 'https://api.cloudflare.com/client/v4/' . $endpoint;
    
    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . $apiToken,
        'Content-Type: application/json'
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

// 1. Get Zone ID for selected domain
$zoneRes = cloudflareApiRequest('zones?name=' . urlencode($selectedDomain));
if ($zoneRes['code'] == 200 && !empty($zoneRes['body']['result'])) {
    $zoneId = $zoneRes['body']['result'][0]['id'];
} else {
    $errorDetails = $zoneRes['body']['errors'][0]['message'] ?? 'Unknown error';
    if ($zoneRes['code'] != 200) {
        $errorMsg = "Could not connect to Cloudflare API. HTTP Code: {$zoneRes['code']} - {$errorDetails}";
    } else {
        $errorMsg = "Could not find zone for $selectedDomain. Ensure the domain is added to this Cloudflare account.";
    }
}

// 2. Handle Add DNS Record Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_record' && $zoneId) {
    // Validate and sanitize
    $type = $_POST['type'] ?? 'A';
    $name = $_POST['name'] ?? '';
    // If name is just "@", map it to the domain name as Cloudflare expects the full domain
    if ($name === '@') {
        $name = $selectedDomain;
    } elseif (strpos($name, $selectedDomain) === false) {
        // If they enter "www", append the domain
        $name = $name . '.' . $selectedDomain;
    }
    
    $content = $_POST['content'] ?? '';
    $ttl = (int)($_POST['ttl'] ?? 1);
    $proxied = isset($_POST['proxied']) ? true : false;
    
    // CNAME and MX records might have specific proxy requirements or root domain requirements, but let Cloudflare API handle validation.
    // If MX, we might need priority, but keeping it simple for now as per raw requirements.
    
    $postData = [
        'type' => $type,
        'name' => $name,
        'content' => $content,
        'ttl' => $ttl,
        'proxied' => $proxied
    ];
    
    $addRes = cloudflareApiRequest("zones/{$zoneId}/dns_records", 'POST', $postData);
    if ($addRes['code'] == 200 && $addRes['body']['success']) {
        $successMsg = "DNS Record added successfully!";
    } else {
        $errorDetails = $addRes['body']['errors'][0]['message'] ?? 'Unknown error';
        $errorMsg = "Failed to add record: " . $errorDetails;
    }
}

// 3. Fetch DNS Records
if ($zoneId) {
    $recordsRes = cloudflareApiRequest("zones/{$zoneId}/dns_records?per_page=100");
    if ($recordsRes['code'] == 200 && $recordsRes['body']['success']) {
        $dnsRecords = $recordsRes['body']['result'];
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
    <style>
        :root {
            --bg-color: #0f172a;
            --container-bg: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #8b5cf6;
            --primary-hover: #7c3aed;
            --danger: #ef4444;
            --success: #10b981;
            --gradient: linear-gradient(135deg, #8b5cf6, #ec4899);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.2) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.2) 0, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 2rem;
            line-height: 1.5;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            background: var(--container-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.6);
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .domain-selector form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        select, input[type="text"], input[type="number"] {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            transition: all 0.2s;
            width: 100%;
            appearance: none;
        }

        select {
            cursor: pointer;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23f8fafc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }
        
        select option {
            background: var(--bg-color);
            color: var(--text-main);
        }

        select:focus, input[type="text"]:focus, input[type="number"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
        }

        button {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
        }

        button:hover {
            opacity: 0.9;
        }

        button:active {
            transform: scale(0.98);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            height: 100%;
            padding-top: 1.5rem;
        }

        .checkbox-group label {
            color: var(--text-main);
            cursor: pointer;
        }

        .checkbox-group input {
            cursor: pointer;
            width: 1.25rem;
            height: 1.25rem;
            accent-color: var(--primary);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            color: #6ee7b7;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.1);
            display: inline-block;
        }

        .badge.proxied {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.5);
        }

        .badge.dns-only {
            background: rgba(148, 163, 184, 0.2);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.5);
        }
        
        .badge.type {
            background: rgba(139, 92, 246, 0.2);
            color: #c4b5fd;
            border: 1px solid rgba(139, 92, 246, 0.5);
        }

    </style>
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
