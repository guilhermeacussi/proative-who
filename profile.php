<?php
// profile.php - versão segura e sem causar 500 por colunas inexistentes
require __DIR__ . '/src/init.php'; // ajusta se necessário

// Para debug local: (desative em produção)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$me = current_user($pdo);

// Se não estiver logado, redireciona para login (mantive o comportamento original)
if (!$me) {
    header('Location: login.php');
    exit;
}

// Determina qual perfil estamos visualizando: ?id=NN ou o próprio
$viewing_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : (int) $me['id'];

// Busca dados do usuário que vamos exibir
try {
    $stmt = $pdo->prepare('SELECT id, nome, bio, pgp_key, profile_image, avatar FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$viewing_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('profile.php - erro ao buscar usuário: ' . $e->getMessage());
    $profile = false;
}

if (!$profile) {
    // evita expor stack trace em produção
    http_response_code(404);
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Usuário não encontrado</title></head><body><h1>Usuário não encontrado.</h1></body></html>';
    exit;
}

// Define avatar: prioriza profile_image (pode ser URL), depois avatar, depois default
$avatar = 'uploads/default.png';
$profile_image_db = trim((string)($profile['profile_image'] ?? ''));
$avatar_db = trim((string)($profile['avatar'] ?? ''));

if ($profile_image_db !== '') {
    if (filter_var($profile_image_db, FILTER_VALIDATE_URL)) {
        $avatar = $profile_image_db;
    } elseif (file_exists(__DIR__ . '/' . $profile_image_db)) {
        $avatar = $profile_image_db;
    } else {
        // não confiar em caminhos arbitrários — mantemos fallback
        $avatar = $profile_image_db; // ainda deixa, caso seja um caminho relativo correto
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

// Pega perguntas do usuário visualizado
try {
    $stmt = $pdo->prepare('SELECT id, titulo, conteudo, created_at FROM questions WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$profile['id']]);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('profile.php - erro ao buscar perguntas: ' . $e->getMessage());
    $perguntas = [];
}

// É próprio perfil?
$is_own_profile = ($viewing_id === (int)$me['id']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Perfil - <?= htmlspecialchars($profile['nome']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript"></script>
<style>
/* CSS mínimo (copie seu CSS real se preferir) */
:root {
  --primary-bg:#0e0e0e; --secondary-bg:#1c1c1c; --accent-color:#b96cff; --text-color:#fff; --text-muted:#aaa; --border-color:#2f2f2f; --input-bg:#121212;
}
body{background:var(--primary-bg);color:var(--text-color);margin:0;font-family:'Libre Franklin',sans-serif;}
main.container{max-width:850px;margin:40px auto;background:var(--secondary-bg);border:1px solid var(--border-color);border-radius:12px;padding:30px;}
.profile-header{text-align:center;margin-bottom:30px;}
.avatar-lg{width:130px;height:130px;border-radius:50%;object-fit:cover;border:3px solid var(--accent-color);}
.btn{background:var(--accent-color);color:var(--text-color);border:none;padding:8px 18px;border-radius:20px;cursor:pointer;font-weight:600;}
.edit-form{display:none;margin-top:20px;background:var(--input-bg);padding:15px;border-radius:8px;border:1px solid var(--border-color);}
.success-msg{background:#162416;color:#00ff88;padding:10px;border-radius:8px;text-align:center;margin-bottom:10px;}
.error-msg{background:#241616;color:#ff8888;padding:10px;border-radius:8px;text-align:center;margin-bottom:10px;}
    
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
    .pgp-section {
    margin-top: 15px;
    background-color: #111;
    border: 1px solid #333;
    padding: 15px;
    border-radius: 8px;
}

.pgp-section h3 {
    color: #9b59b6;
    margin-bottom: 8px;
}

.pgp-key {
    background-color: #000;
    color: #0f0;
    padding: 10px;
    border-radius: 5px;
    font-family: monospace;
    white-space: pre-wrap;
    overflow-x: auto;
}
    
    
</style>
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php">Who?</a>
  <nav>
    <?php if (!empty($me)): ?>
      <a href="ask.php">Fazer pergunta</a> 
      <a href="/src/actions/logout.php">Sair</a>
    <?php else: ?>
      <a href="login.php">Entrar</a>
      <a href="register.php">Registrar</a>
    <?php endif; ?>
  </nav>
</header>

<main class="container">
  <div class="profile-header">
    <div id="message"></div>
    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="avatar-lg" id="avatarPreview" onerror="this.src='uploads/default.png'">
    <h1><?= htmlspecialchars($profile['nome']) ?></h1>
    <p class="handle">@<?= htmlspecialchars(preg_replace('/\s+/', '', mb_strtolower($profile['nome']))) ?></p>
    <p class="bio"><?= htmlspecialchars($profile['bio'] ?? 'Nenhuma bio adicionada ainda.') ?></p>
    <p><strong>Chave PGP:</strong><br><?= nl2br(htmlspecialchars($profile['pgp_key'] ?? 'Nenhuma chave adicionada.')) ?></p>

    <?php if ($is_own_profile): ?>
      <button class="btn" id="editBtn">Editar Perfil</button>

      <form class="edit-form" id="editForm" enctype="multipart/form-data">
        <label>Imagem de Perfil (Cloud upload)</label>
        <div style="display:flex;gap:10px;margin-bottom:12px;">
          <button type="button" class="btn" id="upload_widget_btn">Enviar imagem (Cloud)</button>
          <input type="hidden" name="profile_image" id="profile_image_input" value="<?= htmlspecialchars($profile['profile_image'] ?? '') ?>">
        </div>

        <label>Ou enviar arquivo local (fallback)</label>
        <input type="file" name="profile_image_file" accept="image/*">

        <label>Biografia</label>
        <textarea name="bio" rows="3" style="width:100%;background:#0c0c0c;color:#fff;border:1px solid var(--border-color);padding:8px;border-radius:6px;"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>

      <?php if (!empty($user['pgp_key'])): ?>
    <div class="pgp-section">
        <h3>Chave PGP</h3>
        <pre class="pgp-key"><?php echo htmlspecialchars($user['pgp_key']); ?></pre>
    </div>
<?php endif; ?>


        <button type="submit" class="btn" style="margin-top:10px;">Salvar Alterações</button>
      </form>
    <?php endif; ?>
  </div>

  <h2 style="color:var(--accent-color)">Minhas Perguntas</h2>
  <div class="feed-list">
    <?php if (!empty($perguntas)): ?>
      <?php foreach ($perguntas as $p): 
        $conteudoLimpo = strip_tags($p['conteudo'] ?? '');
        if (mb_strlen($conteudoLimpo) > 200) $conteudoLimpo = mb_substr($conteudoLimpo, 0, 200) . '...';
      ?>
        <article class="question-item" style="margin-bottom:16px;padding:12px;background:var(--input-bg);border-radius:8px;border:1px solid var(--border-color);">
          <h3 style="margin:0 0 6px;color:var(--text-color)"><?= htmlspecialchars($p['titulo']) ?></h3>
          <p style="color:var(--text-muted);margin:0 0 8px"><?= htmlspecialchars($conteudoLimpo) ?></p>
          <div class="footer" style="display:flex;justify-content:space-between;align-items:center;">
            <span class="post-meta" style="color:var(--text-muted)"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
            <a href="questions.php?id=<?= htmlspecialchars($p['id']) ?>" class="btn">Ver mais</a>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Você ainda não fez nenhuma pergunta.</p>
    <?php endif; ?>
  </div>
</main>

<script>
// JS mínimo para edição (igual às versões anteriores)
document.addEventListener('DOMContentLoaded', () => {
  const editBtn = document.getElementById('editBtn');
  const editForm = document.getElementById('editForm');
  const avatarPreview = document.getElementById('avatarPreview');
  const profileImageInput = document.getElementById('profile_image_input');
  const messageDiv = document.getElementById('message');
  const localFileInput = document.querySelector('input[name="profile_image_file"]');
  let currentObjectURL = null;

  if (editBtn && editForm) {
    editBtn.addEventListener('click', () => {
      editForm.style.display = editForm.style.display === 'block' ? 'none' : 'block';
      if (editForm.style.display === 'block') editForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    localFileInput && localFileInput.addEventListener('change', function(){
      const f = this.files[0];
      if (f) {
        if (currentObjectURL) URL.revokeObjectURL(currentObjectURL);
        currentObjectURL = URL.createObjectURL(f);
        avatarPreview.src = currentObjectURL;
        profileImageInput.value = '';
      }
    });

    // Cloudinary widget (se houver)
    const cloudName = "dctvku3xp";
    const uploadPreset = "cryptmedia";
    let widget = null;
    if (typeof cloudinary !== 'undefined') {
      widget = cloudinary.createUploadWidget({
        cloudName, uploadPreset,
        sources: ["local","url","camera","image_search"],
        multiple: false, maxFileSize: 5 * 1024 * 1024, cropping: false, resourceType: "image"
      }, (err, result) => {
        if (err) { console.error(err); showMessage('Erro no upload','error'); return; }
        if (result && result.event === 'success') {
          profileImageInput.value = result.info.secure_url;
          avatarPreview.src = result.info.secure_url;
          showMessage('Upload concluído', 'success');
        }
      });
    }

    document.getElementById('upload_widget_btn')?.addEventListener('click', () => {
      if (widget) widget.open();
      else showMessage('Widget não inicializado','error');
    });

    // Submissão via fetch
    editForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(editForm);
      try {
        const res = await fetch('src/actions/profile_update.php', { method:'POST', body: fd });
        const json = await res.json();
        if (json.success) {
          showMessage(json.message || 'Salvo', 'success');
          // atualiza campos sem reload
          if (json.avatar_url) avatarPreview.src = json.avatar_url;
          if (json.bio) document.querySelector('.bio').textContent = json.bio;
          if (json.pgp_key) document.querySelector('.profile-header p:nth-of-type(3)').innerHTML = '<strong>Chave PGP:</strong><br>' + json.pgp_key.replace(/\n/g,'<br>');
          editForm.style.display = 'none';
        } else {
          showMessage(json.message || 'Erro', 'error');
        }
      } catch (err) {
        console.error(err);
        showMessage('Erro ao salvar', 'error');
      }
    });
  }

  function showMessage(text, type) {
    messageDiv.innerHTML = `<div class="${type === 'success' ? 'success-msg' : 'error-msg'}">${text}</div>`;
    setTimeout(()=> messageDiv.innerHTML = '', 4000);
  }

  avatarPreview.addEventListener('error', () => avatarPreview.src = 'uploads/default.png');
});
</script>
</body>
</html>
