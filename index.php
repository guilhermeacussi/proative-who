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
    <link rel="stylesheet" type="text/css" href="css/index.css">
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