<?php
require __DIR__ . '/src/init.php';
if (is_logged()) header('Location: index.php');
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Registro</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<main class="container">
<h1>Registrar</h1>
<form action="/src/actions/register_process.php" method="post">
<label>Nome</label>
<input name="nome" required>
<label>E-mail</label>
<input name="email" type="email" required>
<label>Senha</label>
<input name="senha" type="password" required>
<button type="submit" class="btn">Criar conta</button>
</form>
<p>Já tem conta? <a href="login.php">Entrar</a></p>
</main>
</body>
</html>