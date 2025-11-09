<?php
require __DIR__ . '/../init.php'; // Ajuste se o caminho para init.php estiver errado (ex: se for /src/init.php, use __DIR__ . '/../../src/init.php')
header('Content-Type: application/json');

$me = current_user($pdo);
if (!$me) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$question_id = $data['question_id'] ?? null;
$action = $data['action'] ?? null;

if (!$question_id || !in_array($action, ['like', 'unlike'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

try {
    if ($action === 'like') {
        // Verifica se já curtiu (para evitar duplicatas)
        $stmt = $pdo->prepare('SELECT id FROM likes WHERE user_id = ? AND question_id = ?');
        $stmt->execute([$me['id'], $question_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Você já curtiu esta pergunta.']);
            exit;
        }
        // Adiciona like
        $pdo->prepare('INSERT INTO likes (user_id, question_id) VALUES (?, ?)')->execute([$me['id'], $question_id]);
        // Atualiza contador na tabela questions
        $pdo->prepare('UPDATE questions SET likes_count = likes_count + 1 WHERE id = ?')->execute([$question_id]);
    } else {
        // Remove like
        $pdo->prepare('DELETE FROM likes WHERE user_id = ? AND question_id = ?')->execute([$me['id'], $question_id]);
        // Atualiza contador
        $pdo->prepare('UPDATE questions SET likes_count = likes_count - 1 WHERE id = ?')->execute([$question_id]);
    }
    // Pega o novo contador
    $stmt = $pdo->prepare('SELECT likes_count FROM questions WHERE id = ?');
    $stmt->execute([$question_id]);
    $new_count = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'new_count' => $new_count]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
}
?>
