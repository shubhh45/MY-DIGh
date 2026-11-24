<?php
// ===== DigiLocker — Documents API =====
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

define('DATA_DIR', __DIR__ . '/../data/');
define('DOCS_DIR', DATA_DIR . 'docs/');

if (!is_dir(DOCS_DIR)) mkdir(DOCS_DIR, 0755, true);

function requireSession(): string {
    if (!isset($_SESSION['username'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'Not authenticated']);
        exit;
    }
    return $_SESSION['username'];
}

function docsFile(string $username): string {
    return DOCS_DIR . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $username) . '.json';
}

function readDocs(string $username): array {
    $file = docsFile($username);
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}

function writeDocs(string $username, array $docs): void {
    file_put_contents(docsFile($username), json_encode($docs, JSON_PRETTY_PRINT));
}

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$body   = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: $_POST;
}

// ══════════════════════════════════
// GET action=list
// ══════════════════════════════════
if ($action === 'list') {
    $username = requireSession();
    $docs = readDocs($username);
    respond(['ok' => true, 'docs' => $docs]);
}

// ══════════════════════════════════
// GET action=get&id=…
// ══════════════════════════════════
if ($action === 'get') {
    $username = requireSession();
    $id   = $_GET['id'] ?? '';
    $docs = readDocs($username);
    foreach ($docs as $doc) {
        if ($doc['id'] === $id) respond(['ok' => true, 'doc' => $doc]);
    }
    respond(['ok' => false, 'message' => 'Document not found'], 404);
}

// ══════════════════════════════════
// POST action=add
// ══════════════════════════════════
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = requireSession();

    $name      = trim($body['name']      ?? '');
    $category  = trim($body['category']  ?? '');
    $authority = trim($body['authority'] ?? '');
    $date      = trim($body['date']      ?? date('Y-m-d'));
    $notes     = trim($body['notes']     ?? '');
    $fileName  = trim($body['fileName']  ?? '');
    $fileType  = trim($body['fileType']  ?? '');
    $size      = trim($body['size']      ?? '');
    $data      = $body['data']           ?? '';     // base64 data URL
    $verified  = (bool)($body['verified'] ?? false);

    if (!$name || !$category) {
        respond(['ok' => false, 'message' => 'Name and category are required'], 400);
    }

    $doc = [
        'id'         => 'doc_' . time() . '_' . substr(md5(uniqid()), 0, 6),
        'name'       => $name,
        'category'   => $category,
        'authority'  => $authority,
        'date'       => $date,
        'notes'      => $notes,
        'fileName'   => $fileName,
        'fileType'   => $fileType,
        'size'       => $size,
        'data'       => $data,
        'verified'   => $verified,
        'uploadedAt' => date('c'),
    ];

    $docs = readDocs($username);
    array_unshift($docs, $doc); // newest first
    writeDocs($username, $docs);

    respond(['ok' => true, 'doc' => $doc]);
}

// ══════════════════════════════════
// POST action=delete
// ══════════════════════════════════
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = requireSession();
    $id   = $body['id'] ?? ($_GET['id'] ?? '');
    $docs = readDocs($username);
    $filtered = array_values(array_filter($docs, fn($d) => $d['id'] !== $id));
    writeDocs($username, $filtered);
    respond(['ok' => true]);
}

respond(['ok' => false, 'message' => 'Unknown action'], 400);
