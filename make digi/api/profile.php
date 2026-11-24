<?php
// ===== DigiLocker — Profile API =====
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

define('DATA_DIR', __DIR__ . '/../data/');
define('USERS_FILE', DATA_DIR . 'users.json');

function requireSession(): string {
    if (!isset($_SESSION['username'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'Not authenticated']);
        exit;
    }
    return $_SESSION['username'];
}

function readUsers(): array {
    if (!file_exists(USERS_FILE)) return [];
    return json_decode(file_get_contents(USERS_FILE), true) ?: [];
}

function writeUsers(array $users): void {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
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
// GET action=get
// ══════════════════════════════════
if ($action === 'get') {
    $username = requireSession();
    $users    = readUsers();
    foreach ($users as $u) {
        if ($u['username'] === $username) {
            unset($u['password']);
            respond(['ok' => true, 'user' => $u]);
        }
    }
    respond(['ok' => false, 'message' => 'User not found'], 404);
}

// ══════════════════════════════════
// POST action=update
// ══════════════════════════════════
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = requireSession();
    $users    = readUsers();

    foreach ($users as &$u) {
        if ($u['username'] === $username) {
            if (isset($body['name']))    $u['name']    = trim($body['name']);
            if (isset($body['email']))   $u['email']   = trim($body['email']);
            if (isset($body['phone']))   $u['phone']   = trim($body['phone']);
            if (isset($body['dob']))     $u['dob']     = trim($body['dob']);
            if (isset($body['address'])) $u['address'] = trim($body['address']);

            writeUsers($users);
            $safe = $u;
            unset($safe['password']);
            respond(['ok' => true, 'user' => $safe]);
        }
    }
    respond(['ok' => false, 'message' => 'User not found'], 404);
}

// ══════════════════════════════════
// POST action=change_password
// ══════════════════════════════════
if ($action === 'change_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = requireSession();
    $currentPass = $body['current_password'] ?? '';
    $newPass     = $body['new_password']     ?? '';

    if (!$currentPass || !$newPass) {
        respond(['ok' => false, 'message' => 'Both password fields are required'], 400);
    }
    if (strlen($newPass) < 6) {
        respond(['ok' => false, 'message' => 'New password must be at least 6 characters'], 400);
    }

    $users = readUsers();
    foreach ($users as &$u) {
        if ($u['username'] === $username) {
            if (!password_verify($currentPass, $u['password'])) {
                respond(['ok' => false, 'message' => 'Current password is incorrect'], 401);
            }
            $u['password'] = password_hash($newPass, PASSWORD_DEFAULT);
            writeUsers($users);
            respond(['ok' => true]);
        }
    }
    respond(['ok' => false, 'message' => 'User not found'], 404);
}

respond(['ok' => false, 'message' => 'Unknown action'], 400);
