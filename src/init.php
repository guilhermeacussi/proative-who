<?php
// -------------------------------------------------------------
// Inicia sessão de forma segura
// -------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------------
// Configuração do banco de dados
// -------------------------------------------------------------
$config = require __DIR__ . '/config.php';

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $config['db']['host'],
    $config['db']['name'],
    $config['db']['charset']
);

try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Erro de conexão com o banco de dados: ' . $e->getMessage());
}

// -------------------------------------------------------------
// Token CSRF (gera se não existir)
// -------------------------------------------------------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// -------------------------------------------------------------
// Funções auxiliares
// -------------------------------------------------------------

/**
 * Verifica se o usuário está logado.
 */
function is_logged(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Retorna os dados do usuário logado ou null se não houver.
 */
function current_user(PDO $pdo): ?array {
    if (!is_logged()) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, nome, email, bio, avatar FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}
