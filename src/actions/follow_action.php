<?php
// src/actions/follow_action.php - Endpoint para ações de seguir/deseguir
require_once __DIR__ . '/../init.php'; // Ajuste o caminho se necessário para acessar $pdo
require_once __DIR__ . '/followers.php'; // Ajuste o caminho para followers.php

header('Content-Type: application/json');

$me = current_user($pdo);
if (!$me) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$action = $_POST['action'] ?? '';
$followed_id = (int)($_POST['followed_id'] ?? 0);

if (!$followed_id || !in_array($action, ['follow', 'unfollow'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$follower_id = (int)$me['id'];

if ($action === 'follow') {
    $success = followUser($pdo, $follower_id, $followed_id);
    $message = $success ? 'Agora você segue este usuário!' : 'Erro ao seguir ou já segue.';
} elseif ($action === 'unfollow') {
    $success = unfollowUser($pdo, $follower_id, $followed_id);
    $message = $success ? 'Deixou de seguir este usuário.' : 'Erro ao deixar de seguir ou não seguia.';
}

if ($success) {
    // Recalcular contadores após a ação PARA O PERFIL VISUALIZADO ($followed_id)
    $followers_count = countFollowers($pdo, $followed_id);  // Seguidores do seguido (correto)
    $following_count = countFollowing($pdo, $followed_id);  // Seguindo do seguido (corrigido: antes era do seguidor)
    echo json_encode([
        'success' => true,
        'message' => $message,
        'followers_count' => $followers_count,
        'following_count' => $following_count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $message]);
}
