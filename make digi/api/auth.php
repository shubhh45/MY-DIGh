<?php
// ===== DigiLocker — Auth API =====
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

define('DATA_DIR', __DIR__ . '/../data/');
define('USERS_FILE', DATA_DIR . 'users.json');

// ── Ensure data directory exists ──
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

// ── Helpers ──
function readUsers(): array {
    if (!file_exists(USERS_FILE)) return [];
    $json = file_get_contents(USERS_FILE);
    return json_decode($json, true) ?: [];
}

function writeUsers(array $users): void {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// ── Input ──
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$body   = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: $_POST;
}

// ══════════════════════════════════
// GET action=me  — return session user
// ══════════════════════════════════
if ($action === 'me') {
    if (!isset($_SESSION['username'])) {
        respond(['ok' => false, 'user' => null]);
    }
    $users = readUsers();
    foreach ($users as $u) {
        if ($u['username'] === $_SESSION['username']) {
            unset($u['password']); // never send password to client
            respond(['ok' => true, 'user' => $u]);
        }
    }
    // session invalid
    session_destroy();
    respond(['ok' => false, 'user' => null]);
}

// ══════════════════════════════════
// POST action=signup
// ══════════════════════════════════
if ($action === 'signup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($body['name']     ?? '');
    $aadhaar  = trim($body['aadhaar']  ?? '');
    $email    = trim($body['email']    ?? '');
    $phone    = trim($body['phone']    ?? '');
    $username = trim($body['username'] ?? '');
    $password = $body['password']      ?? '';

    if (!$name || !$username || !$password || !$aadhaar) {
        respond(['ok' => false, 'message' => 'Required fields missing'], 400);
    }

    $users = readUsers();
    foreach ($users as $u) {
        if ($u['username'] === $username) {
            respond(['ok' => false, 'message' => 'Username already exists'], 409);
        }
    }

    $newUser = [
        'name'     => $name,
        'aadhaar'  => $aadhaar,
        'email'    => $email,
        'phone'    => $phone,
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'joined'   => date('M Y'),
    ];
    $users[] = $newUser;
    writeUsers($users);

    $_SESSION['username'] = $username;

    unset($newUser['password']);
    respond(['ok' => true, 'user' => $newUser]);
}

// ══════════════════════════════════
// POST action=login
// ══════════════════════════════════
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($body['username'] ?? '');
    $password   = $body['password']      ?? '';

    if (!$identifier || !$password) {
        respond(['ok' => false, 'message' => 'Username and password required'], 400);
    }

    $users = readUsers();
    foreach ($users as $u) {
        $matchUser    = $u['username'] === $identifier;
        $matchAadhaar = isset($u['aadhaar']) && $u['aadhaar'] === preg_replace('/\s+/', '', $identifier);
        if (($matchUser || $matchAadhaar) && password_verify($password, $u['password'])) {
            $_SESSION['username'] = $u['username'];
            unset($u['password']);
            respond(['ok' => true, 'user' => $u]);
        }
    }
    respond(['ok' => false, 'message' => 'Invalid username or password'], 401);
}

// ══════════════════════════════════
// POST action=logout
// ══════════════════════════════════
if ($action === 'logout') {
    session_destroy();
    respond(['ok' => true]);
}

respond(['ok' => false, 'message' => 'Unknown action'], 400);
