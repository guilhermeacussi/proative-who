<?php
$config = require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Conexão
$conn = new mysqli(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conteudo = trim($_POST['conteudo']);
    $question_id = (int)$_POST['question_id'];
    $user_id = $_SESSION['user_id'];

    if ($conteudo && $question_id) {
        $stmt = $conn->prepare("INSERT INTO ansewers (question_id, user_id, conteudo, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $question_id, $user_id, $conteudo);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: ../../question.php?id=" . $question_id);
exit;
