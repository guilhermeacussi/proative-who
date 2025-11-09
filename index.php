<?php
// Ajuste o caminho para init.php se necessário
require __DIR__ . '/src/init.php';
$me = current_user($pdo);

// Debug: Verifique se há conexão
if (!$pdo) {
    die('Erro: Conexão com banco de dados falhou.');
}

// Pega as últimas perguntas com autor, avatar, username, likes_count e ID
try {
    $stmt = $pdo->query('
        SELECT 
            q.id, 
            q.titulo, 
            q.conteudo, 
            q.created_at, 
            COALESCE(q.likes_count, 0) AS likes_count,
            u.id AS autor_id, 
            u.nome AS autor, 
            u.avatar AS autor_avatar
        FROM questions q
        JOIN users u ON u.id = q.user_id
        ORDER BY q.created_at DESC
        LIMIT 20
    ');

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Conte as perguntas
    $question_count = count($questions);
    error_log("Debug: Encontradas $question_count perguntas.");
} catch (PDOException $e) {
    $questions = [];
    error_log('Erro na query: ' . $e->getMessage());
    $error_message = 'Erro ao carregar perguntas: ' . $e->getMessage();  // Exibe no HTML
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home - Who?</title>
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

/* --- POST COMPOSER --- */
.post-composer {
  padding: 15px 20px;
  border-bottom: 1px solid var(--border-color);
  background: var(--primary-bg);
}

.quick-post-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  text-decoration: none;
  padding: 10px;
  background: var(--primary-bg);
  border: 1px solid var(--border-color);
  border-radius: 50px;
  transition: background 0.2s;
}

.quick-post-link:hover {
  background: #141414;
}

.quick-post-inner {
  display: flex;
  align-items: center;
  flex-grow: 1;
}

.post-prompt {
  color: var(--text-muted);
  font-size: 1rem;
  margin-left: 15px;
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
            <h3>O que está acontecendo</h3>
            <div class="trending-list">
                <a href="#" class="trending-item">
                    <span class="category">Vírginia e Vini Jr.</span>
                    <span class="topic">#VirginiaTrazOHexa</span>
                    <span class="count">2.5k Perguntas</span>
                </a>
                <a href="#" class="trending-item">
                    <span class="category">OR3</span>
                    <span class="topic">#OliviaLançaLogo</span>
                    <span class="count">12.2k Perguntas</span>
                </a>
            </div>
        </div>
        
        <div class="follow-suggestions">
            <h3>Quem seguir</h3>
            <div class="suggestion-item">
                <span class="username">@mluizasousx</span>
                <button class="btn btn-follow">Seguir</button>
            </div>
        </div>
    </aside>

    <div class="feed-content">
        <?php if (!empty($me)): ?>
        <section class="post-composer">
            <div class="quick-post-link">
                <div class="quick-post-inner">
                    <img src="uploads/avatars/<?= htmlspecialchars($me['avatar'] ?? 'default.png') ?>" 
                         alt="Foto de perfil de <?= htmlspecialchars($me['username'] ?? 'usuário') ?>" 
                         class="avatar" onerror="this.src='uploads/avatars/default.png'">
                    <span class="post-prompt">O que você gostaria de perguntar ou compartilhar? (máx 800 caracteres)</span>
                </div>
                <a href="ask.php" class="btn btn-primary btn-compose">Perguntar</a>
            </div>
        </section>
        <?php endif; ?>
        
        <h2>Perguntas Recentes</h2>
        
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $q): ?>
                <article class="question-item" data-question-id="<?= htmlspecialchars($q['id']) ?>">
                    <div class="post-header">
                        <img src="uploads/avatars/<?= htmlspecialchars($q['autor_avatar'] ?? 'default.png') ?>" 
                             alt="Foto de perfil de <?= htmlspecialchars($q['autor']) ?>" 
                             class="avatar" onerror="this.src='uploads/avatars/default.png'">
                        <div>
                            <span class="post-author"><?= htmlspecialchars($q['autor']) ?></span>
							<p class="post-meta">@<?= htmlspecialchars($q['autor'] ?? 'usuario') ?> - <?= htmlspecialchars($q['created_at']) ?></p>
                        </div>
                    </div>

                    <h3 class="post-title"><?= htmlspecialchars($q['titulo']) ?></h3>
					<div class="post-body"><?= nl2br(trim(strip_tags($q['conteudo']))) ?></div>
                    
                    <div class="post-interactions">
                        <a href="question.php?id=<?= htmlspecialchars($q['id']) ?>" 
                           class="interaction-btn comment-count" 
                           title="Ver Respostas">
                            <i class="far fa-comment"></i> <?= htmlspecialchars($q['respostas_count'] ?? 0) ?>
                        </a>
                        <button class="interaction-btn" title="Repostar"><i class="fas fa-retweet"></i></button>
                        <button class="interaction-btn like-btn" title="Curtir" data-liked="false">
                            <i class="far fa-heart"></i> <span class="like-count"><?= htmlspecialchars($q['likes_count']) ?></span>
                        </button>
                        <button class="interaction-btn share-btn" title="Compartilhar" data-link="question.php?id=<?= htmlspecialchars($q['id']) ?>">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-questions">
                Nenhuma pergunta encontrada. 
                <?php if (isset($error_message)): ?>
                    <br><small style="color: red;"><?= htmlspecialchars($error_message) ?></small>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</main>

<nav class="mobile-nav-bar">
    <a href="index.php" class="active"><i class="fas fa-home"></i></a>
    <a href="explore.php"><i class="fas fa-search"></i></a>
    <a href="notifications.php"><i class="fas fa-bell"></i></a>
    <a href="messages.php"><i class="fas fa-envelope"></i></a>
</nav>

<script>
// Função de likes via AJAX
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!<?= json_encode(!empty($me)) ?>) {
            alert('Você precisa estar logado para curtir.');
            return;
        }
        const questionId = btn.closest('.question-item').dataset.questionId;
        const likeCountSpan = btn.querySelector('.like-count');
        const icon = btn.querySelector('i');
        const isLiked = btn.dataset.liked === 'true';

        try {
            const response = await fetch('src/actions/like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ question_id: questionId, action: isLiked ? 'unlike' : 'like' })
            });
            const result = await response.json();
            if (result.success) {
                likeCountSpan.textContent = result.new_count;
                btn.dataset.liked = isLiked ? 'false' : 'true';
                icon.className = isLiked ? 'far fa-heart' : 'fas fa-heart';
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (err) {
            console.error('Erro no like:', err);
            alert('Erro ao curtir. Tente novamente.');
        }
    });
});

// Função de compartilhar (copiar link)
document.querySelectorAll('.share-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const link = window.location.origin + '/' + btn.dataset.link;
        navigator.clipboard.writeText(link).then(() => {
            alert('Link copiado para a área de transferência!');
        }).catch(err => {
            console.error('Erro ao copiar:', err);
            alert('Erro ao copiar o link.');
        });
    });
});
</script>

</body>
</html>
