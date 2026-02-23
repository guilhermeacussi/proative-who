<?php
require __DIR__ . '/src/init.php';

if (is_logged()) {
    header('Location: index.php');
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Captura mensagens de erro, se houver
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login</title>
<link rel="stylesheet" href="css/login.css">
</head>

<body>
<main class="container">
<h2>Entrar</h2>
<form action="/src/actions/login_process.php" method="post">
<label>Usuário ou E-mail</label>
<input name="user" type="text" required>
<label>Senha</label>
<input name="senha" type="password" required>
<button class="btn" type="submit">Entrar</button>
    <?php if (!empty($error)): ?>
    <p style="color: #ff6b6b; text-align:center; font-weight:600;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

</form>
<p>Não tem conta? <a href="register.php">Registrar</a></p>
</main>
</body>
</html>