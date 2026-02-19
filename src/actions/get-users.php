<?php
require __DIR__ . '/../init.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query('SELECT id, nome, avatar FROM users ORDER BY id DESC LIMIT 10');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona fallback para avatar
    foreach ($users as &$user) {
        $user['avatar'] = !empty($user['avatar']) && file_exists(__DIR__ . '/../../uploads/' . $user['avatar'])
            ? 'uploads/' . $user['avatar']
            : 'uploads/default.png';
    }

    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode([]);
}
