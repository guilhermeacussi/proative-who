<?php
require __DIR__ . '/../init.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido');
}

$userInput = trim($_POST['user'] ?? '');
$senha = $_POST['senha'] ?? '';

if (empty($userInput) || empty($senha)) {
    $_SESSION['error'] = 'Preencha todos os campos';
    header('Location: /../../login.php');
    exit;
}

// Detecta se o campo é email ou nome de usuário
if (filter_var($userInput, FILTER_VALIDATE_EMAIL)) {
    $query = 'SELECT id, senha FROM users WHERE email = ? LIMIT 1';
} else {
    $query = 'SELECT id, senha FROM users WHERE nome = ? LIMIT 1';
}

$stmt = $pdo->prepare($query);
$stmt->execute([$userInput]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($senha, $user['senha'])) {
    $_SESSION['error'] = 'Usuário ou senha incorretos';
    header('Location: /../../login.php');
    exit;
}

// Login bem-sucedido
$_SESSION['user_id'] = $user['id'];

header('Location: /../../index.php');
exit;
?>
