<?php
require __DIR__ . '/../init.php'; // Ajuste o caminho se necessário

header('Content-Type: application/json');

$user_id = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int) $_GET['user_id'] : null;
$type = isset($_GET['type']) ? $_GET['type'] : null; // 'followers' ou 'following'

if (!$user_id || !in_array($type, ['followers', 'following'])) {
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit;
}

try {
    if ($type === 'followers') {
        // Seguidores: usuários que seguem o user_id
        $stmt = $pdo->prepare('
            SELECT u.id, u.nome, u.avatar, u.profile_image
            FROM followers f
            JOIN users u ON f.follower_id = u.id
            WHERE f.followed_id = ?
            ORDER BY f.created_at DESC
        ');
    } elseif ($type === 'following') {
        // Seguindo: usuários que o user_id segue
        $stmt = $pdo->prepare('
            SELECT u.id, u.nome, u.avatar, u.profile_image
            FROM followers f
            JOIN users u ON f.followed_id = u.id
            WHERE f.follower_id = ?
            ORDER BY f.created_at DESC
        ');
    }
    $stmt->execute([$user_id]);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retornar a lista em JSON
    echo json_encode(['list' => $list]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao buscar dados']);
}
?>