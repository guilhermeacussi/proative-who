<?php
require __DIR__ . '/src/init.php';
$me = current_user($pdo);

// Pega últimas perguntas com autor
$stmt = $pdo->query('SELECT q.id, q.titulo, q.conteudo, q.created_at, u.nome as autor 
                     FROM questions q 
                     JOIN users u ON u.id = q.user_id 
                     ORDER BY q.created_at DESC 
                     LIMIT 20');
$questions = $stmt->fetchAll();
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Home - Who?</title>
<link rel="stylesheet" href="/css/style.css">
<style>
body {
    background: #0e0e0e;
    color: #fff;
    font-family: Arial, sans-serif;
    margin: 0;
}
header.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #1c1c1c;
    padding: 15px 30px;
}
header.topbar a { color: #b96cff; text-decoration: none; margin-left: 15px; }
main.container { max-width: 900px; margin: 40px auto; padding: 0 15px; }
.question-item { background: #1c1c1c; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
.question-item h3 { margin-top: 0; color: #b96cff; }
.question-item .description { margin: 10px 0; line-height: 1.5; }
.question-item .meta { font-size: 0.9em; color: #aaa; }
.question-item .btn { display: inline-block; padding: 10px 15px; background: #b96cff; color: #fff; border-radius: 5px; text-decoration: none; transition: 0.3s; }
.question-item .btn:hover { background: #a34de7; }
</style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php">Who?</a>
    <nav>
    <?php if ($me): ?>
        <a href="ask.php">Fazer pergunta</a>
        <a href="profile.php">Meu perfil</a>
        <a href="/src/actions/logout.php">Sair</a>
    <?php else: ?>
        <a href="login.php">Entrar</a>
        <a href="register.php">Registrar</a>
    <?php endif; ?>
    </nav>
</header>

<main class="container">
    <h2>Perguntas recentes</h2>
    <?php foreach ($questions as $q): ?>
        <article class="question-item">
            <h3><?= htmlspecialchars($q['titulo']) ?></h3>
            <div class="description"><?= $q['conteudo'] ?></div>
            <p class="meta">Por <?= htmlspecialchars($q['autor']) ?> em <?= $q['created_at'] ?></p>
            <a href="question.php?id=<?= $q['id'] ?>" class="btn">Ver Respostas</a>
        </article>
    <?php endforeach; ?>
</main>
</body>
</html>
