<?php
require __DIR__ . '/src/init.php';
require_once __DIR__ . '/src/actions/followers.php';
$me = current_user($pdo);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!$me) {
    header('Location: login.php');
    exit;
}

$viewing_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : (int) $me['id'];

try {
    $stmt = $pdo->prepare('SELECT id, nome, bio, pgp_key, profile_image, avatar FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$viewing_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('profile.php - erro ao buscar usuário: ' . $e->getMessage());
    $profile = false;
}

if (!$profile) {
    http_response_code(404);
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Usuário não encontrado</title></head><body><h1>Usuário não encontrado.</h1></body></html>';
    exit;
}

$avatar = 'uploads/default.png';
$profile_image_db = trim((string)($profile['profile_image'] ?? ''));
$avatar_db = trim((string)($profile['avatar'] ?? ''));

if ($profile_image_db !== '') {
    if (filter_var($profile_image_db, FILTER_VALIDATE_URL)) {
        $avatar = $profile_image_db;
    } elseif (file_exists(__DIR__ . '/' . $profile_image_db)) {
        $avatar = $profile_image_db;
    } else {
        $avatar = $profile_image_db;
    }
} elseif ($avatar_db !== '') {
    if (filter_var($avatar_db, FILTER_VALIDATE_URL)) {
        $avatar = $avatar_db;
    } elseif (file_exists(__DIR__ . '/' . $avatar_db)) {
        $avatar = $avatar_db;
    } else {
        $avatar = $avatar_db;
    }
}

try {
    $stmt = $pdo->prepare('SELECT id, titulo, conteudo, created_at FROM questions WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$profile['id']]);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('profile.php - erro ao buscar perguntas: ' . $e->getMessage());
    $perguntas = [];
}

$is_own_profile = ($viewing_id === (int)$me['id']);

// Dados de seguidores
$is_following = isFollowing($pdo, (int)$me['id'], $viewing_id);
$followers_count = countFollowers($pdo, $viewing_id);
$following_count = countFollowing($pdo, $viewing_id);

// Buscar lista de seguidores (usuários que seguem o viewing_id)
try {
    $stmt = $pdo->prepare('
        SELECT u.id, u.nome, u.avatar, u.profile_image
        FROM followers f
        JOIN users u ON f.follower_id = u.id
        WHERE f.followed_id = ?
        ORDER BY f.created_at DESC
    ');
    $stmt->execute([$viewing_id]);
    $followers_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('profile.php - erro ao buscar seguidores: ' . $e->getMessage());
    $followers_list = [];
}

// Buscar lista de seguindo (usuários que viewing_id segue)
try {
    $stmt = $pdo->prepare('
        SELECT u.id, u.nome, u.avatar, u.profile_image
        FROM followers f
        JOIN users u ON f.followed_id = u.id
        WHERE f.follower_id = ?
        ORDER BY f.created_at DESC
    ');
    $stmt->execute([$viewing_id]);
    $following_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('profile.php - erro ao buscar seguindo: ' . $e->getMessage());
    $following_list = [];
}

// Função auxiliar para avatar de usuário na lista
function get_user_avatar($user) {
    $avatar = 'uploads/default.png';
    $profile_image = trim((string)($user['profile_image'] ?? ''));
    $avatar_db = trim((string)($user['avatar'] ?? ''));
    if ($profile_image !== '') {
        if (filter_var($profile_image, FILTER_VALIDATE_URL)) {
            $avatar = $profile_image;
        } elseif (file_exists(__DIR__ . '/' . $profile_image)) {
            $avatar = $profile_image;
        }
    } elseif ($avatar_db !== '') {
        if (filter_var($avatar_db, FILTER_VALIDATE_URL)) {
            $avatar = $avatar_db;
        } elseif (file_exists(__DIR__ . '/' . $avatar_db)) {
            $avatar = $avatar_db;
        }
    }
    return $avatar;
}

// Função para verificar se o usuário atual segue um usuário específico
function isFollowingUser($pdo, $follower_id, $followed_id) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM followers WHERE follower_id = ? AND followed_id = ?');
        $stmt->execute([$follower_id, $followed_id]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

if (isset($_POST['reload_follow'])) {
    header("Location: profile.php?id=" . $profileId);
    exit;
}


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Perfil de <?= htmlspecialchars($profile['nome']) ?> - Who?</title>
<link rel="stylesheet" href="css/profile.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript"></script>
    
    <style>
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
        <a href="index.php">Inicio</a>
        <a href="ask.php">Fazer pergunta</a>
        <a href="users.php">Descobrir</a>
        <a href="/src/actions/logout.php">Sair</a>
    </nav>
</header>


<main class="container">
    <header class="profile-header">
        <div class="cover-photo"></div>
        <div class="profile-info">
            
            <div class="avatar-actions-wrapper">
                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="profile-avatar-lg" onerror="this.src='uploads/default.png'">
                
                <div class="action-buttons">
                    <?php if ($is_own_profile): ?>
                        <button class="btn-secondary" id="editBtn">Editar Perfil</button>
                    <?php else: ?>
                        <button class="btn-secondary follow-btn <?= $is_following ? 'following' : '' ?>" id="followBtn" data-following="<?= $is_following ? '1' : '0' ?>" data-followed-id="<?= $viewing_id ?>">
                            <i class="fas <?= $is_following ? 'fa-user-minus' : 'fa-user-plus' ?>"></i>
                            <?= $is_following ? 'Deixar de seguir' : 'Seguir' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <h1 class="username"><?= htmlspecialchars($profile['nome']) ?></h1>
            <span class="handle">@<?= htmlspecialchars(preg_replace('/\s+/', '', mb_strtolower($profile['nome']))) ?></span>
            <p class="bio">
                <?= htmlspecialchars($profile['bio'] ?? 'Nenhuma bio adicionada ainda.') ?>
            </p>
            
            <div class="profile-stats">
                <a href="#" class="stat-item" data-tab="posts">
                    <strong><?= count($perguntas) ?></strong><span>Posts</span>
                </a>
                <a href="#" class="stat-item" data-tab="followers">
                    <strong id="followersCount"><?= $followers_count ?></strong><span>Seguidores</span>
                </a>
                <a href="#" class="stat-item" data-tab="following">
                    <strong id="followingCount"><?= $following_count ?></strong><span>Seguindo</span>
                </a>
            </div>

            <?php if ($is_own_profile): ?>
                <form class="edit-form" id="editForm" enctype="multipart/form-data" style="display: none;">
                    <div id="message"></div>
                    <label>Imagem de Perfil (Cloud upload)</label>
                    <div style="display:flex;gap:10px;margin-bottom:12px;">
                        <button type="button" class="btn" id="upload_widget_btn">Enviar imagem (Cloud)</button>
                        <input type="hidden" name="profile_image" id="profile_image_input" value="<?= htmlspecialchars($profile['profile_image'] ?? '') ?>">
                    </div>

                    <label>Ou enviar arquivo local (fallback)</label>
                    <input type="file" name="profile_image_file" accept="image/*">

                    <label>Biografia</label>
                    <textarea name="bio" rows="3" style="width:100%;background:#0c0c0c;color:#fff;border:1px solid var(--border-color);padding:8px;border-radius:6px;"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>

                    <button type="submit" class="btn" style="margin-top:10px;">Salvar Alterações</button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <div class="profile-content">
        <h3 class="feed-title">Posts</h3>
        <br>
        <?php if (!empty($perguntas)): ?>
            <?php foreach ($perguntas as $p): 
                $conteudoLimpo = strip_tags($p['conteudo'] ?? '');
                if (mb_strlen($conteudoLimpo) > 200) $conteudoLimpo = mb_substr($conteudoLimpo, 0, 200) . '...';
            ?>
                <article class="question-item">
                    <div class="post-header">
                        <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="avatar" onerror="this.src='uploads/default.png'">
                        <div>
                            <span class="post-author"><?= htmlspecialchars($profile['nome']) ?></span>
                            <span class="post-meta">@<?= htmlspecialchars(preg_replace('/\s+/', '', mb_strtolower($profile['nome']))) ?> · <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
                        </div>
                    </div>
                    <h4 class="post-title"><?= htmlspecialchars($p['titulo']) ?></h4>
                    <p class="post-body"><?= htmlspecialchars($conteudoLimpo) ?></p>
                    <div class="post-interactions">
                        <a href="questions.php?id=<?= htmlspecialchars($p['id']) ?>" class="interaction-btn">
                            <i class="fas fa-eye"></i> Ver mais
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 20px;">Nenhum post ainda.</p>
        <?php endif; ?>
    </div>
</main>

<div class="followers-modal-container" id="followers-modal">
    <div class="modal-backdrop" id="modal-backdrop"></div>
    <div class="modal-content">
        <header class="modal-header">
            <h2 id="modal-title">Seguidores</h2>
            <button class="modal-close-btn" id="modal-close-btn"><i class="fas fa-times"></i></button>
        </header>

        <nav class="modal-tabs">
            <a href="#" class="modal-tab-link active" data-tab="followers">Seguidores</a>
            <a href="#" class="modal-tab-link" data-tab="following">Seguindo</a>
        </nav>

        <div class="modal-tab-content" id="followers-content" style="display: block;">
            <?php if (!empty($followers_list)): ?>
                <?php foreach ($followers_list as $user): 
                    $user_avatar = get_user_avatar($user);
                    $is_following_this = isFollowingUser($pdo, (int)$me['id'], (int)$user['id']);
                ?>
                    <div class="user-list-item">
                        <div class="user-info-flex">
                            <img src="<?= htmlspecialchars($user_avatar) ?>" alt="Avatar" class="avatar-md" onerror="this.src='uploads/default.png'">
                            <div>
                                <span class="user-name-list"><?= htmlspecialchars($user['nome']) ?></span>
                                <span class="user-handle-list">@<?= htmlspecialchars(preg_replace('/\s+/', '', mb_strtolower($user['nome']))) ?></span>
                            </div>
                        </div>
                        <?php if ((int)$user['id'] !== (int)$me['id']): ?>
                            <button class="follow-button-list <?= $is_following_this ? 'unfollow' : '' ?>" data-user-id="<?= $user['id'] ?>" data-following="<?= $is_following_this ? '1' : '0' ?>">
                                <?= $is_following_this ? 'Deixar de Seguir' : 'Seguir' ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 20px;">Nenhum seguidor ainda.</p>
            <?php endif; ?>
        </div>
        
        <div class="modal-tab-content" id="following-content">
            <?php if (!empty($following_list)): ?>
                <?php foreach ($following_list as $user): 
                    $user_avatar = get_user_avatar($user);
                    $is_following_this = isFollowingUser($pdo, (int)$me['id'], (int)$user['id']);
                ?>
                    <div class="user-list-item">
                        <div class="user-info-flex">
                            <img src="<?= htmlspecialchars($user_avatar) ?>" alt="Avatar" class="avatar-md" onerror="this.src='uploads/default.png'">
                            <div>
                                <span class="user-name-list"><?= htmlspecialchars($user['nome']) ?></span>
                                <span class="user-handle-list">@<?= htmlspecialchars(preg_replace('/\s+/', '', mb_strtolower($user['nome']))) ?></span>
                            </div>
                        </div>
                        <?php if ((int)$user['id'] !== (int)$me['id']): ?>
                            <button class="follow-button-list <?= $is_following_this ? 'unfollow' : '' ?>" data-user-id="<?= $user['id'] ?>" data-following="<?= $is_following_this ? '1' : '0' ?>">
                                <?= $is_following_this ? 'Deixar de Seguir' : 'Seguir' ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 20px;">Não seguindo ninguém ainda.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

    <script>
document.querySelectorAll('.follow-button-list').forEach(button => {
    button.addEventListener('click', function () {
        const userId = this.getAttribute('data-user-id');
        const isFollowing = this.getAttribute('data-following') === '1';
        const action = isFollowing ? 'unfollow' : 'follow';

        fetch('src/actions/follow_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=${action}&followed_id=${userId}`
        })
        .then(r => r.json())
        .then(data => {
            // Apenas recarregar a página — EXATAMENTE o que você pediu
            if (data.success) {
                location.reload();
            }
        });
    });
});
        
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

    
    <script src="js/profile.js"></script>

</body>
</html>