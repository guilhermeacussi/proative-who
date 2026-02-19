<?php
require __DIR__ . '/../init.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../register.php");
    exit;
}

// Recebe inputs
$nome  = trim($_POST['nome']  ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha']      ?? '';

$errors = [];

// Validações
if ($nome === '') {
    $errors[] = 'Nome é obrigatório.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email inválido.';
}

if (strlen($senha) < 8) {
    $errors[] = 'Senha deve ter ao menos 8 caracteres.';
}

// Se erros → volta para register
if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    header("Location: ../register.php");
    exit;
}

try {

    // Verifica email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $_SESSION['register_errors'] = ['Email já cadastrado'];
        header("Location: ../register.php");
        exit;
    }

    // Cria o hash
    $hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere
    $stmt = $pdo->prepare('INSERT INTO users (nome, email, senha) VALUES (?, ?, ?)');
    $stmt->execute([$nome, $email, $hash]);

    $userId = $pdo->lastInsertId();

    // Login automático
    $_SESSION['user_id'] = $userId;

    // SUCESSO → vai para index
    header("Location: https://who.gamer.gd/index.php");
    exit;

} catch (Exception $e) {

    error_log("Erro no cadastro: " . $e->getMessage());
    $_SESSION['register_errors'] = ['Erro interno. Tente novamente mais tarde.'];

    header("Location: https://who.gamer.gd/register.php");
    exit;
}
