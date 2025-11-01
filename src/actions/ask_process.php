<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui a configuração do banco de dados
$config = require_once '../config.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Conecta ao banco de dados
$conn = new mysqli(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['conteudo']);
    $user_id = $_SESSION['user_id'];

    if (!empty($titulo) && !empty($conteudo)) {
        $stmt = $conn->prepare("INSERT INTO questions (user_id, titulo, conteudo, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            die("Erro no prepare: " . $conn->error);
        }

        $stmt->bind_param("iss", $user_id, $titulo, $conteudo);

        if ($stmt->execute()) {
            // Pega o ID da pergunta recém-criada
            $last_id = $conn->insert_id;

            $_SESSION['mensagem'] = "Pergunta publicada com sucesso!";

            // Redireciona direto para a página da pergunta
            header("Location: ../../questions.php?id=" . $last_id);
            exit;
        } else {
            $_SESSION['mensagem'] = "Erro ao publicar pergunta: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['mensagem'] = "Preencha todos os campos antes de enviar.";
    }
} else {
    $_SESSION['mensagem'] = "Método inválido.";
}

$conn->close();

// Caso dê algum erro, redireciona para a lista de perguntas
header("Location:  ../../questions.php");
exit;
?>
