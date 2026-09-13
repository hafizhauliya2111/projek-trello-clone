<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['Method tidak diizinkan.']]);
    exit;
}

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody, true);

$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$csrf_token = $input['csrf_token'] ?? '';

$errors = [];

if (!csrf_verify($csrf_token)) {
    $errors[] = 'Token CSRF tidak valid.';
}

if (empty($username)) {
    $errors[] = 'Username harus diisi.';
}

if (empty($email)) {
    $errors[] = 'Email harus diisi.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid.';
}

if (empty($password)) {
    $errors[] = 'Password harus diisi.';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password harus memiliki minimal 6 karakter.';
}

if (empty($errors)) {
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email sudah terdaftar.';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare('INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)');
$stmt->execute([
    'username' => $username,
    'email' => $email,
    'password_hash' => $password_hash
]);

echo json_encode(['success' => true, 'message' => 'Registrasi berhasil.']);

