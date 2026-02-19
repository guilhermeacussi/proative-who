<?php
// Ajuste o caminho para init.php se necessário
require __DIR__ . '/src/init.php';
$me = current_user($pdo);

// Debug: Verifique se há conexão
if (!$pdo) {
    die('Erro: Conexão com banco de dados falhou.');
}

// Pega as últimas perguntas com autor, avatar, username, likes_count, respostas_count e ID
try {
    $stmt = $pdo->query('
        SELECT 
            q.id, 
            q.titulo, 
            q.conteudo, 
            q.created_at, 
            COALESCE(q.likes_count, 0) AS likes_count,
            (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) AS respostas_count,
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

// Busca usuários para a lista (Sidebar)
try {
    $stmt_users = $pdo->query('SELECT id, nome, avatar FROM users ORDER BY id DESC LIMIT 10');
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    error_log('Erro ao buscar usuários: ' . $e->getMessage());
}

/**
 * Retorna o caminho da imagem de perfil de um usuário.
 *
 * @param array $user Array com os dados do usuário, precisa ter 'profile_image'.
 * @return string Caminho da imagem pronta para usar no HTML.
 */
function getProfileImage(array $user): string {
    $default = 'uploads/default.png';
    
    // Verifica se existe a coluna e se não é nula
    if (!empty($user['profile_image'])) {
        $path = 'uploads/' . $user['profile_image'];
        // Se o arquivo realmente existe, usa ele
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // Caso contrário, retorna a imagem padrão
    return $default;
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

.header-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--accent-color);
  margin-right: 10px;
  cursor: pointer;
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
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
}

.mobile-nav-bar a.active {
  color: var(--accent-color);
}

.mobile-nav-bar a span {
  font-size: 0.8rem;
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

  /* Melhorias para mobile */
  .post-interactions {
    justify-content: space-between; /* Espalha melhor os botões */
    margin-left: 0; /* Remove margem lateral para telas pequenas */
    padding: 10px 0;
  }

  .interaction-btn {
    font-size: 0.9em; /* Reduz fonte para caber melhor */
  }

  .question-item {
    padding: 10px 15px; /* Reduz padding */
  }

  .post-title {
    font-size: 1rem; /* Ajusta título */
  }

  .post-body {
    font-size: 0.9rem; /* Ajusta corpo */
  }

  .avatar {
    width: 40px;
    height: 40px; /* Reduz avatares */
  }

  .post-title, .post-body {
    margin-left: 50px; /* Ajusta margem para avatar menor */
  }

  .post-interactions {
    margin-left: 50px; /* Ajusta para avatar menor */
  }

  .header-avatar {
    width: 30px;
    height: 30px; /* Reduz avatar no header para mobile */
  }
}

        .topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    background: #000;
    color: #fff;
}

.hamburger-menu {
    background: none;
    border: none;
    font-size: 26px;
    color: #fff;
    cursor: pointer;
    display: none; /* aparece no mobile */
}

.nav-links {
    display: flex;
    gap: 20px;
}

.nav-links a {
    color: white;
    text-decoration: none;
    font-weight: 500;
}

/* MOBILE */
@media (max-width: 768px) {
    .hamburger-menu {
        display: block;
    }

    .nav-links {
        position: absolute;
        top: 60px;
        right: 0;
        background: #000;
        flex-direction: column;
        width: 200px;
        padding: 15px;
        display: none;
    }

    .nav-links.active {
        display: flex;
    }
}

    
    </style>
</head>

<body>
<header class="topbar">
    <a class="brand" href="index.php">Who?</a>

    <button class="hamburger-menu" id="hamburger-btn">
        <i class="fas fa-bars"></i>
    </button>

    <nav class="nav-links" id="nav-menu">
        <a href="ask.php">Fazer pergunta</a>
        <a href="users.php">Descobrir</a>
        <a href="profile.php">Meu perfil</a>
        <a href="/src/actions/logout.php">Sair</a>
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
        <div id="users-list" class="users-list-content">
            <!-- O JS vai preencher esta div com os usuários -->
            <p style="color: var(--text-muted); padding: 10px 0;">Carregando usuários...</p>
        </div>
    </div>
</aside>
    <div class="feed-content">
        <?php if (!empty($me)): ?>
        <section class="post-composer">
            <div class="quick-post-link">
                <div class="quick-post-inner">
                    
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
                        <img src="uploads/default.png" 
                             class="avatar" onerror="this.src='uploads/avatars/default.png'">
                        <div>
                            <span class="post-author"><?= htmlspecialchars($q['autor']) ?></span>
							<p class="post-meta">@<?= htmlspecialchars($q['autor'] ?? 'usuario') ?> - <?= htmlspecialchars($q['created_at']) ?></p>
                        </div>
                    </div>

                    <h3 class="post-title"><?= htmlspecialchars($q['titulo']) ?></h3>
					<div class="post-body"><?= nl2br(trim(strip_tags($q['conteudo']))) ?></div>
                    
                    <div class="post-interactions">
                        <a href="questions.php?id=<?= htmlspecialchars($q['id']) ?>" 
                           class="interaction-btn comment-count" 
                           title="Ver Respostas">
                            <i class="far fa-comment"></i> <?= htmlspecialchars($q['respostas_count']) ?>
                        </a>
                        <button class="interaction-btn" title="Repostar"><i class="fas fa-retweet"></i></button>
                        <button class="interaction-btn like-btn" title="Curtir" data-liked="false">
                            <i class="far fa-heart"></i> <span class="like-count"><?= htmlspecialchars($q['likes_count']) ?></span>
                        </button>
                        <button class="interaction-btn share-btn" title="Compartilhar" data-link="questions.php?id=<?= htmlspecialchars($q['id']) ?>">
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


    <script>
window.isLoggedIn = <?= json_encode(!empty($me)) ?>;
window.csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;

async function checkImageExists(url) {
    try {
        const response = await fetch(url, { method: 'HEAD' });
        return response.ok;
    } catch {
        return false;
    }
}

async function loadUsers() {
    const container = document.getElementById('users-list');

    try {
        const response = await fetch('src/actions/get-users.php');
        const users = await response.json();

        if (!users || users.length === 0) {
            container.innerHTML = '<p style="color: var(--text-muted); padding: 10px 0;">Nenhum usuário encontrado.</p>';
            return;
        }

        container.innerHTML = '';

        for (const user of users) {
            const a = document.createElement('a');
            a.href = `profile.php?id=${user.id}`;
            a.className = 'user-item';

            const img = document.createElement('img');
            img.className = 'avatar';
            img.alt = `Avatar de ${user.nome}`;

            // Verifica se a imagem existe, se não usa default
            const avatarPath = `uploads/${user.avatar || 'default.png'}`;
            img.src = await checkImageExists(avatarPath) ? avatarPath : 'uploads/default.png';

            const span = document.createElement('span');
            span.className = 'user-name';
            span.textContent = user.nome;

            a.appendChild(img);
            a.appendChild(span);
            container.appendChild(a);
        }

    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        container.innerHTML = '<p style="color: red; padding: 10px 0;">Erro ao carregar usuários.</p>';
    }
}

document.addEventListener('DOMContentLoaded', loadUsers);
        
document.addEventListener('DOMContentLoaded', () => {
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const navMenu = document.getElementById('nav-menu');

    if (hamburgerBtn && navMenu) {
        hamburgerBtn.addEventListener('click', () => {
            navMenu.classList.toggle('active');

            const icon = hamburgerBtn.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    }
});


</script>
<script src="js/index.js" defer></script>

    
    <script src="js/index.js"></script>
    </body>
   </html>