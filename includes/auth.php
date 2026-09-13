<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('/login.php');
    }
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}