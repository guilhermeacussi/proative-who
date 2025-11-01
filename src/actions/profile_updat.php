<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $bio = trim($_POST['bio']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nome, $bio, $user_id);
    $stmt->execute();

    $_SESSION['nome'] = $nome;
}

header("Location: ../../public/profile.php");
exit;
?>
