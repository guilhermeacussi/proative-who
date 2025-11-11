<?php
require __DIR__ . '/src/init.php';
$me = current_user($pdo);

// Se não estiver logado, redireciona
if (!$me) {
    header("Location: login.php");
    exit;
}

// 🔍 Busca na database se o usuário é administrador
try {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$me['id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se não encontrar o usuário ou não for admin, bloqueia
    if (!$userData || $userData['is_admin'] != 1) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao verificar permissões: " . $e->getMessage());
}

// Busca estatísticas básicas
try {
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_questions = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
    $total_answers = $pdo->query("SELECT COUNT(*) FROM answers")->fetchColumn();
    $total_likes = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
} catch (PDOException $e) {
    die('Erro ao carregar estatísticas: ' . $e->getMessage());
}

// Busca usuários recentes
try {
    $users = $pdo->query("SELECT id, nome, email, created_at FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}

// Busca perguntas recentes
try {
    $questions = $pdo->query("SELECT q.id, q.titulo, u.nome AS autor, q.created_at FROM questions q JOIN users u ON q.user_id = u.id ORDER BY q.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $questions = [];
}

// Busca respostas recentes
try {
    $answers = $pdo->query("SELECT a.id, a.conteudo, u.nome AS autor, q.titulo AS pergunta, a.created_at FROM answers a JOIN users u ON a.user_id = u.id JOIN questions q ON a.question_id = q.id ORDER BY a.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $answers = [];
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administração - Who?</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="icons/index.png">
    
    <style>
        :root {
            --primary-bg: #0e0e0e; 
            --secondary-bg: #1c1c1c;
            --accent-color: #b96cff;
            --text-color: #fff;
            --text-muted: #aaa;
            --border-color: #2f2f2f;
        }

        body {
            background: var(--primary-bg);
            color: var(--text-color);
            margin: 0;
            font-family: "Libre Franklin", sans-serif;
        }

        .main-container {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            gap: 30px;
        }

        header.topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--secondary-bg);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        header.topbar a {
            color: var(--accent-color);
            font-weight: 600;
            text-decoration: none;
            margin-left: 15px;
            transition: color 0.2s;
        }

        header.topbar a:hover {
            color: #a34de7;
        }

        .sidebar {
            width: 250px;
            background: var(--secondary-bg);
            padding: 20px;
            border-radius: 10px;
        }

        .sidebar h3 {
            margin-top: 0;
            font-size: 1.2rem;
            color: var(--text-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar li {
            margin-bottom: 10px;
        }

        .sidebar a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .sidebar a:hover {
            color: var(--accent-color);
        }

        .content {
            flex-grow: 1;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--secondary-bg);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
        }

        .stat-card h4 {
            margin: 0;
            font-size: 2rem;
            color: var(--accent-color);
        }

        .stat-card p {
            margin: 5px 0 0 0;
            color: var(--text-muted);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--secondary-bg);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background: var(--primary-bg);
            color: var(--accent-color);
        }

        .btn {
            padding: 5px 10px;
            background: var(--accent-color);
            color: var(--text-color);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn:hover {
            background: #a34de7;
        }

        .btn-danger {
            background: #ff4d4d;
        }

        .btn-danger:hover {
            background: #cc0000;
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
            .stats {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
<header class="topbar">
    <a class="brand" href="index.php">Who?</a>
    <nav>
        <a href="index.php">Voltar ao Site</a>
        <a href="/src/actions/logout.php">Sair</a>
    </nav>
</header>

<main class="main-container">
    <aside class="sidebar">
        <h3>Administração</h3>
        <ul>
            <li><a href="#stats">Estatísticas</a></li>
            <li><a href="#users">Usuários</a></li>
            <li><a href="#questions">Perguntas</a></li>
            <li><a href="#answers">Respostas</a></li>
        </ul>
    </aside>

    <div class="content">
        <h2>Painel Administrativo</h2>

        <section id="stats" class="stats">
            <div class="stat-card">
                <h4><?= $total_users ?></h4>
                <p>Usuários</p>
            </div>
            <div class="stat-card">
                <h4><?= $total_questions ?></h4>
                <p>Perguntas</p>
            </div>
            <div class="stat-card">
                <h4><?= $total_answers ?></h4>
                <p>Respostas</p>
            </div>
            <div class="stat-card">
                <h4><?= $total_likes ?></h4>
                <p>Curtidas</p>
            </div>
        </section>

        <section id="users">
            <h3>Usuários Recentes</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Data de Criação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['nome']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td>
                                <a href="profile.php?id=<?= $user['id'] ?>" class="btn">Ver</a>
                                <button class="btn btn-danger" onclick="deleteUser(<?= $user['id'] ?>)">Deletar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="questions">
            <h3>Perguntas Recentes</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q): ?>
                        <tr>
                            <td><?= htmlspecialchars($q['id']) ?></td>
                            <td><?= htmlspecialchars($q['titulo']) ?></td>
                            <td><?= htmlspecialchars($q['autor']) ?></td>
                            <td><?= htmlspecialchars($q['created_at']) ?></td>
                            <td>
                                <a href="questions.php?id=<?= $q['id'] ?>" class="btn">Ver</a>
                                <button class="btn btn-danger" onclick="deleteQuestion(<?= $q['id'] ?>)">Deletar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="answers">
            <h3>Respostas Recentes</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Conteúdo</th>
                        <th>Autor</th>
                        <th>Pergunta</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($answers as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['id']) ?></td>
                            <td><?= substr(htmlspecialchars(strip_tags($a['conteudo'])), 0, 50) ?>...</td>
                            <td><?= htmlspecialchars($a['autor']) ?></td>
                            <td><?= htmlspecialchars($a['pergunta']) ?></td>
                            <td><?= htmlspecialchars($a['created_at']) ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="deleteAnswer(<?= $a['id'] ?>)">Deletar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</main>

<script>
    function deleteUser(id) {
        if (confirm('Tem certeza que deseja deletar este usuário?')) {
            // Implementar AJAX para deletar usuário
            fetch('src/actions/admin_delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: 'user', id: id })
            }).then(() => location.reload());
        }
    }

    function deleteQuestion(id) {
        if (confirm('Tem certeza que deseja deletar esta pergunta?')) {
            fetch('src/actions/admin_delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: 'question', id: id })
            }).then(() => location.reload());
        }
    }

    function deleteAnswer(id) {
        if (confirm('Tem certeza que deseja deletar esta resposta?')) {
            fetch('src/actions/admin_delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: 'answer', id: id })
            }).then(() => location.reload());
        }
    }
</script>

</body>
</html>
