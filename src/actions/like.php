<?php
require __DIR__ . '/../init.php'; // Ajuste conforme sua estrutura
header('Content-Type: application/json; charset=utf-8');

session_start();

// --- Verifica login ---
$me = current_user($pdo);
if (!$me) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado.']);
    exit;
}

// --- Lê o corpo JSON ---
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}

$question_id = (int)($data['question_id'] ?? 0);
$action = $data['action'] ?? '';

// --- Valida entrada ---
if ($question_id <= 0 || !in_array($action, ['like', 'unlike'], true)) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// --- (Opcional) Proteção CSRF ---
if (!empty($_SESSION['csrf_token'])) {
    $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (hash_equals($_SESSION['csrf_token'], $csrfHeader) === false) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
        exit;
    }
}

try {
    $pdo->beginTransaction();

    if ($action === 'like') {
        // Verifica se já curtiu
        $stmt = $pdo->prepare('SELECT id FROM likes WHERE user_id = ? AND question_id = ?');
        $stmt->execute([$me['id'], $question_id]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Você já curtiu esta pergunta.']);
            exit;
        }

        // Adiciona o like
        $pdo->prepare('INSERT INTO likes (user_id, question_id) VALUES (?, ?)')->execute([$me['id'], $question_id]);
        // Atualiza contador
        $pdo->prepare('UPDATE questions SET likes_count = likes_count + 1 WHERE id = ?')->execute([$question_id]);
    } else {
        // Remove o like
        $pdo->prepare('DELETE FROM likes WHERE user_id = ? AND question_id = ?')->execute([$me['id'], $question_id]);
        // Garante que não fica negativo
        $pdo->prepare('UPDATE questions SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?')->execute([$question_id]);
    }

    $pdo->commit();

    // Busca o novo contador atualizado
    $stmt = $pdo->prepare('SELECT likes_count FROM questions WHERE id = ?');
    $stmt->execute([$question_id]);
    $new_count = (int)$stmt->fetchColumn();

    echo json_encode(['success' => true, 'new_count' => $new_count]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro no like.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados.']);
}
