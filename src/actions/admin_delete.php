<?php
require __DIR__ . '/../init.php';
$me = current_user($pdo);

if (!$me || !$me['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['type']) || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

$type = $data['type'];
$id = (int)$data['id'];

try {
    switch ($type) {
        case 'user':
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            break;
        case 'question':
            $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
            break;
        case 'answer':
            $stmt = $pdo->prepare("DELETE FROM answers WHERE id = ?");
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Tipo inválido']);
            exit;
    }

    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
