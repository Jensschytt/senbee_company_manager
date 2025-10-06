<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// app/api.php — CRUD + sync endpoints (vanilla PHP)
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function readJsonBody() {
    $input = file_get_contents('php://input');
    if (!$input) return [];
    $data = json_decode($input, true);
    return is_array($data) ? $data : [];
}

function validateCvr($cvr) {
    return preg_match('/^\d{8}$/', $cvr) === 1;
}

function companyRow($row) {
    return [
        'id' => (int)$row['id'],
        'cvr_number' => $row['cvr_number'],
        'name' => $row['name'] ?? null,
        'phone' => $row['phone'] ?? null,
        'email' => $row['email'] ?? null,
        'address' => $row['address'] ?? null,
    ];
}

function fetchCvrData($cvr) {
    // Public CVR API
    $url = 'https://cvrapi.dk/api?search=' . urlencode($cvr) . '&country=dk';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_USERAGENT => 'Senbee-Company-Manager/1.0',
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        throw new Exception('CVR API request error: ' . $err);
    }
    if ($http >= 400) {
        throw new Exception('CVR API HTTP ' . $http);
    }
    $json = json_decode($resp, true);
    if (!is_array($json)) {
        throw new Exception('Invalid CVR API response');
    }

    $name  = $json['name'] ?? ($json['ownersname'] ?? null);
    $phone = $json['phone'] ?? ($json['phone_number'] ?? null);
    $email = $json['email'] ?? null;

    $address = $json['address'] ?? null;
    if (!$address && isset($json['address_street'])) {
        $street = trim(($json['address_street'] ?? '') . ' ' . ($json['address_number'] ?? ''));
        $zip = $json['zipcode'] ?? '';
        $city = $json['city'] ?? '';
        $address = trim($street . ', ' . $zip . ' ' . $city);
    }

    return [
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
    ];
}

try {
    if ($method === 'GET' && $action === 'list') {
        $stmt = $pdo->query('SELECT * FROM companies ORDER BY id DESC');
        $rows = $stmt->fetchAll();
        echo json_encode(['items' => array_map('companyRow', $rows)]);
        exit;
    }

    if ($method === 'POST' && $action === 'create') {
        $data = readJsonBody();
        $cvr = $data['cvr_number'] ?? '';
        if (!validateCvr($cvr)) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid CVR. Must be 8 digits.']);
            exit;
        }
        // Insert if not exists
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO companies (cvr_number) VALUES (?)');
        $stmt->execute([$cvr]);

        $stmt = $pdo->prepare('SELECT * FROM companies WHERE cvr_number = ?');
        $stmt->execute([$cvr]);
        $row = $stmt->fetch();
        echo json_encode(['item' => companyRow($row)]);
        exit;
    }

    if ($method === 'DELETE' && $action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM companies WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($method === 'POST' && $action === 'sync') {
        $data = readJsonBody();
        $id = (int)($data['id'] ?? 0);

        $stmt = $pdo->prepare('SELECT * FROM companies WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Company not found']);
            exit;
        }

        $info = fetchCvrData($row['cvr_number']);
        $stmt = $pdo->prepare('UPDATE companies SET name = ?, phone = ?, email = ?, address = ? WHERE id = ?');
        $stmt->execute([$info['name'], $info['phone'], $info['email'], $info['address'], $id]);

        $stmt = $pdo->prepare('SELECT * FROM companies WHERE id = ?');
        $stmt->execute([$id]);
        $updated = $stmt->fetch();
        echo json_encode(['item' => companyRow($updated)]);
        exit;
    }

    if ($method === 'POST' && $action === 'sync_all') {
        $stmt = $pdo->query('SELECT * FROM companies ORDER BY id');
        $rows = $stmt->fetchAll();
        $updated = [];

        foreach ($rows as $row) {
            try {
                $info = fetchCvrData($row['cvr_number']);
                $stmtU = $pdo->prepare('UPDATE companies SET name = ?, phone = ?, email = ?, address = ? WHERE id = ?');
                $stmtU->execute([$info['name'], $info['phone'], $info['email'], $info['address'], $row['id']]);
                $updated[] = (int)$row['id'];
            } catch (Exception $e) {
                $updated[] = ['id' => (int)$row['id'], 'error' => $e->getMessage()];
            }
        }
        echo json_encode(['ok' => true, 'updated' => $updated]);
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
