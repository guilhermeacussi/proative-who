<?php
require __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_POST['user_id'])) {
    echo json_encode(["error" => "Acesso inválido"]);
    exit;
}

$me = (int) $_SESSION['user_id'];
$target = (int) $_POST['user_id'];

if ($me === $target) {
    echo json_encode(["error" => "Você não pode seguir você mesmo"]);
    exit;
}

// Checar follow existente
$stmt = $pdo->prepare("SELECT 1 FROM followers WHERE follower_id=? AND following_id=?");
$stmt->execute([$me, $target]);

$is_following = (bool)$stmt->fetch();

if ($is_following) {
    // UNFOLLOW
    $pdo->prepare("DELETE FROM followers WHERE follower_id=? AND following_id=?")
        ->execute([$me, $target]);

    echo json_encode([
        "status" => "unfollowed",
        "following" => false
    ]);
} else {
    // FOLLOW
    $pdo->prepare("INSERT INTO followers (follower_id, following_id) VALUES (?, ?)")
        ->execute([$me, $target]);

    echo json_encode([
        "status" => "followed",
        "following" => true
    ]);
}

exit;
