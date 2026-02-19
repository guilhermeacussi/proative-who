<?php
// Ajuste o caminho para init.php se necessário
require __DIR__ . '/src/init.php';
$me = current_user($pdo);

// Verifica se o ID da pergunta foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Busca a pergunta
try {
    $stmt = $pdo->prepare("SELECT q.titulo, q.conteudo, q.created_at, u.nome AS autor, u.avatar AS autor_avatar 
                           FROM questions q 
                           JOIN users u ON q.user_id = u.id 
                           WHERE q.id = ?");
    $stmt->execute([$id]);
    $pergunta = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pergunta) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    die('Erro ao carregar pergunta: ' . $e->getMessage());
}

// Busca as respostas
try {
    $stmt = $pdo->prepare("SELECT a.conteudo, a.created_at, u.nome AS autor, u.avatar AS autor_avatar 
                           FROM answers a 
                           JOIN users u ON a.user_id = u.id 
                           WHERE a.question_id = ? 
                           ORDER BY a.created_at ASC");
    $stmt->execute([$id]);
    $respostas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $respostas = [];
}

// Busca usuários para a lista (Sidebar)
try {
    $stmt_users = $pdo->query('SELECT id, nome, avatar FROM users ORDER BY id DESC LIMIT 10');
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    error_log('Erro ao buscar usuários: ' . $e->getMessage());
}

// Processa nova resposta se enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($me) && isset($_POST['conteudo'])) {
    $conteudo = trim($_POST['conteudo']);
    if (!empty($conteudo)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO answers (question_id, user_id, conteudo, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$id, $me['id'], $conteudo]);
            header("Location: questions.php?id=$id");
            exit;
        } catch (PDOException $e) {
            $error = 'Erro ao enviar resposta: ' . $e->getMessage();
        }
    } else {
        $error = 'Resposta não pode estar vazia.';
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pergunta['titulo']) ?> - Who?</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="icons/index.png">
    
    <!-- Quill -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>

    <style>
        :root {
            --primary-bg: #0e0e0e; 
            --secondary-bg: #1c1c1c;
            --accent-color: #b96cff;
            --text-color: #fff;
            --text-muted: #aaa;
            --border-color: #2f2f2f;
        }

        /* --- GERAL --- */
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

        /* --- TOPO --- */
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

        /* --- SIDEBAR --- */
        .sidebar {
          width: 300px;
          padding-top: 20px;
          position: sticky;
          top: 60px;
          height: auto;
          height: fit-content;
        }

        .trending-box, .follow-suggestions {
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

        .trending-item {
          display: block;
          text-decoration: none;
          color: var(--text-color);
          padding: 10px 0;
        }

        .trending-item:hover {
          background-color: #252525;
        }

        .trending-item .topic {
          font-weight: 700;
          display: block;
        }

        .trending-item .category, .trending-item .count {
          font-size: 0.8em;
          color: var(--text-muted);
        }

        /* Usuários listados 1 por 1, um embaixo do outro */
        .users-list-content {
          display: flex;
          flex-direction: column;
          gap: 10px;
        }

        .user-item {
          display: flex;
          align-items: center;
          padding: 7px 0;
          text-decoration: none;
          color: var(--text-color);
          border-radius: 7px;
          transition: background 0.2s;
        }

        .user-item:hover {
          background-color: #252525;
        }

        .user-item .avatar {
          width: 36px;
          height: 36px;
          border-radius: 50%;
          object-fit: cover;
          margin-right: 11px;
          background-color: var(--accent-color);
          border: 2px solid #353535;
        }

        .user-item .user-name {
          font-weight: 600;
          font-size: 1em;
        }

        /* --- FEED --- */
        .feed-content {
          flex-grow: 1;
          max-width: 600px;
          border-left: 1px solid var(--border-color);
          border-right: 1px solid var(--border-color);
          min-height: 100vh;
        }

        .feed-content h2 {
          font-size: 1.3rem;
          padding: 15px 20px;
          margin: 0;
          border-bottom: 1px solid var(--border-color);
          font-weight: 700;
        }

        .no-questions {
          padding: 20px;
          color: var(--text-muted);
          text-align: center;
        }

        /* --- POSTS --- */
        .question-item {
          background: var(--primary-bg);
          padding: 15px 20px;
          border-bottom: 1px solid var(--border-color);
          transition: background 0.2s;
        }

        .question-item:hover {
          background: #141414;
        }

        .post-header {
          display: flex;
          align-items: center;
          margin-bottom: 10px;
        }

        .avatar {
          width: 48px;
          height: 48px;
          border-radius: 50%;
          margin-right: 10px;
          background-color: var(--accent-color);
        }

        .post-author {
          font-weight: 700;
          color: var(--text-color);
        }

        .post-meta {
          font-size: 0.9em;
          color: var(--text-muted);
          margin: 0;
        }

        .post-title, .post-body {
          margin: 5px 0 10px 58px;
        }

        .post-title {
          font-size: 1.1rem;
          color: var(--text-color);
          font-weight: 600;
        }

        .post-body {
          line-height: 1.5;
          color: var(--text-color);
        }

        .post-interactions {
          display: flex;
          justify-content: space-around;
          padding-top: 10px;
          margin-left: 58px;
        }

        .interaction-btn {
          background: none;
          border: none;
          color: var(--text-muted);
          font-size: 1em;
          cursor: pointer;
          text-decoration: none;
          transition: color 0.2s;
          display: flex;
          align-items: center;
        }

        .interaction-btn i {
          margin-right: 8px;
          font-size: 1.1em;
        }

        .interaction-btn:hover {
          color: var(--accent-color);
        }

        /* --- BOTÕES --- */
        .btn {
          display: inline-block;
          padding: 8px 15px;
          background: none;
          border: 1px solid var(--accent-color);
          color: var(--accent-color);
          border-radius: 20px;
          text-decoration: none;
          transition: 0.3s;
          font-weight: 600;
          cursor: pointer;
        }

        .btn-primary {
          background: var(--accent-color);
          color: var(--text-color);
          border: 1px solid var(--accent-color);
        }

        .btn:hover { 
          background: #a34de740;
          color: #a34de7;
        }

        .btn-primary:hover { 
          background: #a34de7; 
        }

        .btn-follow {
          cursor: pointer;
        }

        /* --- MOBILE NAV --- */
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

        /* --- RESPONSIVO --- */
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

        /* Estilos adicionais para pergunta e respostas */
        .question-detail {
          background: var(--primary-bg);
          padding: 15px 20px;
          border-bottom: 1px solid var(--border-color);
        }

        .responses {
          background: var(--primary-bg);
          padding: 15px 20px;
          border-bottom: 1px solid var(--border-color);
        }

        .response-item {
          margin-bottom: 20px;
          padding-left: 58px;
        }

        .response-item .post-header {
          margin-bottom: 10px;
        }

        .response-item .post-body {
          line-height: 1.5;
          color: var(--text-color);
        }

        .reply-form {
          padding: 15px 20px;
          border-top: 1px solid var(--border-color);
          background: var(--primary-bg);
        }

        .reply-form textarea {
          width: 100%;
          padding: 10px;
          background: var(--secondary-bg);
          color: var(--text-color);
          border: 1px solid var(--border-color);
          border-radius: 6px;
          font-family: inherit;
        }

        .error {
          color: red;
          text-align: center;
          margin-bottom: 10px;
        }
    </style>
</head>

<body>
<header class="topbar">
    <a class="brand" href="index.php">Who?</a>
    <nav>
        <?php if (!empty($me)): ?>
            <a href="ask.php">Fazer pergunta</a> 
            <a href="profile.php">Meu perfil</a>
            <a href="/src/actions/logout.php">Sair</a>
        <?php else: ?>
            <a href="login.php">Entrar</a>
            <a href="register.php">Registrar</a>
        <?php endif; ?>
    </nav>
</header>

<main class="main-layout main-container">
    <aside class="sidebar">
        <div class="trending-box">
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
        
        <div class="follow-suggestions">
            <h3>Usuários</h3>
            <div class="users-list-content">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <a href="profile.php?id=<?= htmlspecialchars($user['id']) ?>" class="user-item">
                                            <img src="uploads/default.png" 
                             class="avatar" onerror="this.src='uploads/avatars/default.png'">
                            <span class="user-name"><?= htmlspecialchars($user['nome']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); padding: 10px 0;">Nenhum usuário encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </aside>

    <div class="feed-content">
        <h2>Pergunta</h2>
        
        <article class="question-detail">
            <div class="post-header">

                                <img src="uploads/default.png" 
                             class="avatar" onerror="this.src='uploads/avatars/default.png'">
                
                <div>
                    <span class="post-author"><?= htmlspecialchars($pergunta['autor']) ?></span>
                    <p class="post-meta">@<?= htmlspecialchars($pergunta['autor']) ?> - <?= htmlspecialchars($pergunta['created_at']) ?></p>
                </div>
            </div>
            <h3 class="post-title"><?= htmlspecialchars($pergunta['titulo']) ?></h3>
            <div class="post-body">
                <?= $pergunta['conteudo'] ?> <!-- Renderiza HTML do Quill -->
            </div>
        </article>

        <section class="responses">
            <h3>Respostas (<?= count($respostas) ?>)</h3>
            <?php if (!empty($respostas)): ?>
                <?php foreach ($respostas as $resposta): ?>
                    <div class="response-item">
                        <div class="post-header">

                                 <img src="uploads/default.png" 
                             class="avatar" onerror="this.src='uploads/avatars/default.png'">
                            
                            <div>
                                <span class="post-author"><?= htmlspecialchars($resposta['autor']) ?></span>
                                <p class="post-meta">@<?= htmlspecialchars($resposta['autor']) ?> - <?= htmlspecialchars($resposta['created_at']) ?></p>
                            </div>
                        </div>
                        <div class="post-body">
                            <?= $resposta['conteudo'] ?> <!-- Renderiza HTML do Quill -->
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-questions">Nenhuma resposta ainda. Seja o primeiro a responder!</p>
            <?php endif; ?>
        </section>

        <?php if (!empty($me)): ?>
        <section class="reply-form">
            <h3>Sua Resposta</h3>
            <?php if (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form id="replyForm" method="POST">
                <label for="conteudo">Conteúdo da Resposta:</label>
                <div id="editor"></div>
                                <input type="hidden" name="conteudo" id="conteudo">
                <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Enviar Resposta</button>
            </form>
        </section>
        <?php else: ?>
            <p class="no-questions" style="padding: 20px;">
                <a href="login.php" style="color: var(--accent-color); text-decoration: none;">Entre</a> para responder.
            </p>
        <?php endif; ?>
    </div>
</main>

<!-- Barra de navegação inferior (mobile) -->
<div class="mobile-nav-bar">
    <a href="index.php" class="active"><i class="fas fa-home"></i></a>
    <a href="ask.php"><i class="fas fa-pen"></i></a>
    <a href="profile.php"><i class="fas fa-user"></i></a>
</div>

<script>
  // Inicializa o editor Quill
  const quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Escreva sua resposta aqui...',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['link', 'blockquote', 'code-block'],
        ['clean']
      ]
    }
  });

  // Envia o conteúdo formatado para o campo hidden antes de enviar o formulário
  const form = document.getElementById('replyForm');
  form.onsubmit = function() {
    const html = quill.root.innerHTML.trim();
    document.getElementById('conteudo').value = html;
    if (!html || html === '<p><br></p>') {
      alert('Por favor, escreva uma resposta antes de enviar.');
      return false;
    }
  };
</script>

</body>
</html>
