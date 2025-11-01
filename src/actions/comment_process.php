<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conteudo = trim($_POST['conteudo']);
    $resposta_id = $_POST['resposta_id'];
    $user_id = $_SESSION['user_id'];

    if ($conteudo && $resposta_id) {
        $stmt = $conn->prepare("INSERT INTO comentarios (conteudo, resposta_id, user_id, data_criacao) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sii", $conteudo, $resposta_id, $user_id);
        $stmt->execute();
    }
}

header("Location: ../../public/questions.php");
exit;
?>
