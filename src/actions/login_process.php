<?php
require __DIR__ . '/../init.php';

// Inicia sessão (caso ainda não tenha sido iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Garante que a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido');
}

// Captura e valida os dados do formulário
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if (!$email || !$senha) {
    $_SESSION['error'] = 'Preencha todos os campos';
    header('Location: /../../login.php');
    exit;
}

// Busca usuário no banco
$stmt = $pdo->prepare('SELECT id, senha FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica credenciais
if (!$user || !password_verify($senha, $user['senha'])) {
    $_SESSION['error'] = 'Email ou senha incorretos';
    header('Location: /../../login.php');
    exit;
}

// Login bem-sucedido → grava sessão
$_SESSION['user_id'] = $user['id'];

// Redireciona para a home
header('Location: /../../index.php');
exit;
