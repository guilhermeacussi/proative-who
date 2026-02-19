<?php
// Ajuste o caminho para init.php se necessário
require __DIR__ . '/src/init.php';
$me = current_user($pdo);

// Verifica se o usuário está logado
if (empty($me)) {
    header('Location: login.php');
    exit;
}

// Processa o formulário de atualização
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Atualizar nome
    if (isset($_POST['update_name'])) {
        $new_name = trim($_POST['nome']);
        if (empty($new_name)) {
            $errors[] = 'O nome não pode estar vazio.';
        } elseif (strlen($new_name) > 100) {
            $errors[] = 'O nome deve ter no máximo 100 caracteres.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE users SET nome = ? WHERE id = ?');
                $stmt->execute([$new_name, $me['id']]);
                $success = 'Nome atualizado com sucesso!';
                // Atualiza a sessão ou $me se necessário
                $me['nome'] = $new_name;
            } catch (PDOException $e) {
                $errors[] = 'Erro ao atualizar nome: ' . $e->getMessage();
            }
        }
    }

    // Atualizar senha
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = 'Todos os campos de senha são obrigatórios.';
        } elseif (!password_verify($current_password, $me['senha'])) {
            $errors[] = 'A senha atual está incorreta.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'A nova senha e a confirmação não coincidem.';
        } elseif (strlen($new_password) < 8) {
            $errors[] = 'A nova senha deve ter pelo menos 8 caracteres.';
        } else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET senha = ? WHERE id = ?');
                $stmt->execute([$hashed_password, $me['id']]);
                $success = 'Senha atualizada com sucesso!';
            } catch (PDOException $e) {
                $errors[] = 'Erro ao atualizar senha: ' . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurações - Who?</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="icons/index.png">
    
    <style>
        /* Reutilizando estilos da index.php */
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
            padding: 0 15px;
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
            width: 300px;
            padding-top: 20px;
            position: sticky;
            top: 60px;
            height: fit-content;
        }

        .settings-box {
            background: var(--secondary-bg);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .sidebar h3 {
            margin-top: 0;
            font-size: 1.2rem;
            color: var(--text-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .feed-content {
            flex-grow: 1;
            max-width: 600px;
            border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            min-height: 100vh;
            padding: 20px;
        }

        .feed-content h2 {
            font-size: 1.3rem;
            margin: 0 0 20px 0;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            background: var(--primary-bg);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            color: var(--text-color);
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: var(--accent-color);
            outline: none;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: var(--accent-color);
            border: 1px solid var(--accent-color);
            color: var(--text-color);
            border-radius: 20px;
            text-decoration: none;
            transition: 0.3s;
            font-weight: 600;
            cursor: pointer;
        }

        .btn:hover {
            background: #a34de7;
        }

        .error {
            color: #ff6b6b;
            margin-bottom: 10px;
        }

        .success {
            color: #51cf66;
            margin-bottom: 10px;
        }

        .mobile-nav-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--secondary-bg);
            border-top: 1px solid var(--border-color);
            justify-content: space-around;
            padding: 10px 0;
            z-index: 20;
        }

        .mobile-nav-bar a {
            color: var(--text-muted);
            font-size: 1.5rem;
            text-decoration: none;
        }

        .mobile-nav-bar a.active {
            color: var(--accent-color);
        }

        @media (max-width: 992px) {
            .sidebar {
                display: none;
            }

            .feed-content {
                max-width: 100%;
                border-left: none;
                border-right: none;
            }

            .main-container {
                padding: 0;
            }

            header.topbar {
                padding: 10px 15px;
            }

            .mobile-nav-bar {
                display: flex;
            }
        }
    </style>
</head>

<body>
<header class="topbar">
    <a class="brand" href="index.php">Who?</a>
    <nav>
        <a href="ask.php">Fazer pergunta</a> 
        <a href="profile.php">Meu perfil</a>
        <a href="/src/actions/logout.php">Sair</a>
    </nav>
</header>

<main class="main-layout main-container">
    <aside class="sidebar">
        <div class="settings-box">
            <h3>Regras do Site</h3>
            <div class="trending-list">
                <div class="trending-item">
                    <span class="topic">1. Respeite os outros usuários</span>
                    <span class="category">Não poste conteúdo ofensivo ou discriminatório.</span>
                </div>
                <div class="trending-item">
                    <span class="topic">2. Seja relevante</span>
                    <span class="category">Perguntas devem ser claras e relacionadas ao tema.</span>
                </div>
                <div class="trending-item">
                    <span class="topic">3. Não spam</span>
                    <span class="category">Evite postagens repetitivas ou irrelevantes.</span>
                </div>
                <div class="trending-item">
                    <span class="topic">4. Privacidade</span>
                    <span class="category">Não compartilhe informações pessoais sem consentimento.</span>
                </div>
                <div class="trending-item">
                    <span class="topic">5. Moderação</span>
                    <span class="category">Violadores das regras podem ser banidos.</span>
                </div>
            </div>
        </div>
    </aside>

    <div class="feed-content">
        <h2>Configurações da Conta</h2>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Formulário para alterar nome -->
        <form method="post">
            <div class="form-group">
                <label for="nome">Nome de Usuário:</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($me['nome']) ?>" required>
            </div>
            <button type="submit" name="update_name" class="btn">Atualizar Nome</button>
        </form>

        <hr style="border: 1px solid var(--border-color); margin: 20px 0;">

        <!-- Formulário para alterar senha -->
        <form method="post">
            <div class="form-group">
                <label for="current_password">Senha Atual:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nova Senha:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Nova Senha:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="update_password" class="btn">Atualizar Senha</button>
        </form>
    </div>
</main>



</body>
</html>
