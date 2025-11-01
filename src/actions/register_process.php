<?php
require __DIR__ . '/../init.php';

// Assegura que a sessão está iniciada (necessário para login/flash)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método inválido']);
    exit;
}

// Recebe e "trim" dos inputs
$nome  = trim($_POST['nome']  ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha']      ?? '';

// Validações básicas
$errors = [];

if ($nome === '') {
    $errors[] = 'Nome é obrigatório.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email inválido.';
}

// Exemplo de política de senha mínima — ajuste conforme necessário
if (strlen($senha) < 8) {
    $errors[] = 'Senha deve ter ao menos 8 caracteres.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['errors' => $errors]);
    exit;
}

try {
    // Verifica se já existe usuário com esse email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409); // conflito
        echo json_encode(['error' => 'Email já cadastrado']);
        exit;
    }

    // Cria hash da senha (bcrypt/algoritmo adequado definido pelo PHP)
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    if ($hash === false) {
        throw new RuntimeException('Erro ao gerar hash da senha.');
    }

    // Insere o usuário
    $stmt = $pdo->prepare('INSERT INTO users (nome, email, senha) VALUES (?, ?, ?)');
    $stmt->execute([$nome, $email, $hash]);

    // Recupera ID inserido e faz login automático
    $userId = $pdo->lastInsertId();

    if ($userId) {
        $_SESSION['user_id'] = $userId;
        // Resposta para requisição AJAX; se preferir redirecionar, faça header('Location: ...')
        echo json_encode(['success' => true, 'user_id' => $userId]);
        exit;
    } else {
        throw new RuntimeException('Falha ao recuperar ID do usuário inserido.');
    }

} catch (PDOException $e) {
    // Logue o erro real em arquivo/monitor (não exibir ao usuário)
    error_log('DB Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno (banco de dados).']);
    exit;
} catch (Throwable $e) {
    error_log('Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno.']);
    exit;
}
