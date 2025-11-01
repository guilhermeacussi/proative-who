<?php
require __DIR__ . '/src/init.php';
if (is_logged()) header('Location: index.php');
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<main class="container">
<h1>Entrar</h1>
<form action="/src/actions/login_process.php" method="post">
<label>E-mail</label>
<input name="email" type="email" required>
<label>Senha</label>
<input name="senha" type="password" required>
<button class="btn" type="submit">Entrar</button>
</form>
<p>Não tem conta? <a href="register.php">Registrar</a></p>
</main>
</body>
</html>