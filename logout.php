<?php
require_once __DIR__ . '/auth.php';

$_SESSION = [];
session_destroy();
setcookie(session_name(), '', time() - 42000, '/');

header('Location: ' . BASE_URL . '/index.php');
exit;
