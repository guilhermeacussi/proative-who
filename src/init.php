<?php
session_start();
$config = require __DIR__ . '/config.php';
$dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}";
try {
$pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
} catch (PDOException $e) {
die('DB error: ' . $e->getMessage());
}


// helper simples para checar login
function is_logged() {
return !empty($_SESSION['user_id']);
}


function current_user($pdo) {
if (!is_logged()) return null;
$stmt = $pdo->prepare('SELECT id, nome, email, bio, avatar FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
return $stmt->fetch();
}