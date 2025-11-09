<?php
require __DIR__ . '/src/init.php'; // $pdo e current_user()
$me = current_user($pdo);

if (!$me) {
    header('Location: login.php');
    exit;
}

// Monta caminho do avatar (igual ao original)
$profile_image_db = $me['profile_image'] ?? '';
if (!empty($profile_image_db) && filter_var($profile_image_db, FILTER_VALIDATE_URL)) {
    $avatar = $profile_image_db;
} elseif (!empty($profile_image_db) && file_exists(__DIR__ . '/' . $profile_image_db)) {
    $avatar = $profile_image_db;
} else {
    $avatar = 'uploads/default.png';
}

// Pega perguntas (igual ao original)
try {
    $stmt = $pdo->prepare('SELECT id, titulo, conteudo, created_at FROM questions WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$me['id']]);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $perguntas = [];
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Perfil - <?= htmlspecialchars($me['nome']) ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript"></script>

<style>
/* Mesmo CSS do original – copie aqui ou inclua de um arquivo separado */
:root {
  --primary-bg: #0e0e0e;
  --secondary-bg: #1c1c1c;
  --accent-color: #b96cff;
  --text-color: #fff;
  --text-muted: #aaa;
  --border-color: #2f2f2f;
  --input-bg: #121212;
}

body {
  background: var(--primary-bg);
  color: var(--text-color);
  margin: 0;
  font-family: 'Libre Franklin', sans-serif;
}

header.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--secondary-bg);
  padding: 15px 30px;
  border-bottom: 1px solid var(--border-color);
}

header.topbar .brand {
  color: var(--accent-color);
  font-size: 1.4rem;
  font-weight: 700;
  text-decoration: none;
}

header.topbar nav a {
  color: var(--text-color);
  margin-left: 20px;
  text-decoration: none;
  font-weight: 500;
}

header.topbar nav a:hover { color: var(--accent-color); }

main.container {
  max-width: 850px;
  margin: 40px auto;
  background: var(--secondary-bg);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.5);
}

.profile-header {
  text-align: center;
  margin-bottom: 30px;
}

.profile-header img.avatar-lg {
  width: 130px;
  height: 130px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--accent-color);
}

.profile-header h1 {
  margin: 15px 0 5px;
  font-size: 1.6rem;
  color: var(--accent-color);
}

.profile-header .handle { color: var(--text-muted); margin-bottom: 10px; }

.profile-header .bio {
  font-size: 0.95rem;
  color: var(--text-muted);
  margin: 10px 0;
  white-space: pre-wrap;
}

.feed-list { margin-top: 25px; }

.question-item {
  background: var(--input-bg);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
}

.question-item h3 {
  margin: 5px 0 10px;
  color: var(--text-color);
}

.question-item p {
  color: var(--text-muted);
  font-size: 0.95rem;
  margin-bottom: 8px;
}

.question-item .footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.question-item .post-meta {
  font-size: 0.85rem;
  color: var(--text-muted);
}

.btn {
  background: var(--accent-color);
  color: var(--text-color);
  border: none;
  padding: 8px 18px;
  border-radius: 20px;
  cursor: pointer;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.2s;
}
.btn:hover { background: #a34de7; }

.edit-form {
  display: none;
  margin-top: 30px;
  background: var(--input-bg);
  padding: 20px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
}
.edit-form label {
  color: var(--text-muted);
  display: block;
  margin-bottom: 5px;
}
.edit-form textarea,
.edit-form input[type="file"],
.edit-form input[type="text"] {
  width: 100%;
  background: #0c0c0c;
  color: #fff;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  padding: 10px;
  margin-bottom: 15px;
}
.success-msg {
  background: #162416;
  color: #00ff88;
  padding: 10px;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 20px;
}
.error-msg {
  background: #241616;
  color: #ff8888;
  padding: 10px;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 20px;
}

/* util */
.hidden { display:none; }
</style>
</head>
<body>

<header class="topbar">
  <a class="brand" href="index.php">Who?</a>
  <nav>
    <a href="ask.php">Fazer pergunta</a>
    <a href="/src/actions/logout.php">Sair</a>
  </nav>
</header>

<main class="container">
  <div class="profile-header">
    <div id="message"></div> <!-- Para mensagens de sucesso/erro -->

    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="avatar-lg" id="avatarPreview" onerror="this.src='uploads/default.png'">

    <h1><?= htmlspecialchars($me['nome']) ?></h1>
    <p class="handle">@<?= htmlspecialchars($me['username'] ?? strtolower(str_replace(' ', '', $me['nome']))) ?></p>
    <p class="bio"><?= htmlspecialchars($me['bio'] ?? 'Nenhuma bio adicionada ainda.') ?></p>

    <p><strong>Chave PGP:</strong><br><?= nl2br(htmlspecialchars($me['pgp_key'] ?? 'Nenhuma chave adicionada.')) ?></p>

    <button class="btn" id="editBtn">Editar Perfil</button>

    <form class="edit-form" id="editForm">
      <label>Imagem de Perfil (opção: Cloudinary)</label>
      <div style="display:flex; gap:10px; margin-bottom:12px;">
        <button type="button" class="btn" id="upload_widget_btn">Enviar imagem (Cloud)</button>
        <input type="hidden" name="profile_image" id="profile_image_input" value="<?= htmlspecialchars($me['profile_image'] ?? '') ?>">
      </div>

      <label>Ou enviar arquivo local (fallback)</label>
      <input type="file" name="profile_image_file" accept="image/*">

      <label>Biografia</label>
      <textarea name="bio" rows="3"><?= htmlspecialchars($me['bio'] ?? '') ?></textarea>

      <label>Chave PGP Pública</label>
      <textarea name="pgp_key" rows="4"><?= htmlspecialchars($me['pgp_key'] ?? '') ?></textarea>

      <button type="submit" class="btn">Salvar Alterações</button>
    </form>
  </div>

  <h2 style="color:var(--accent-color);">Minhas Perguntas</h2>
  <div class="feed-list">
    <?php if (count($perguntas) > 0): ?>
      <?php foreach ($perguntas as $p): ?>
        <?php
          $conteudoLimpo = strip_tags($p['conteudo']);
          if (strlen($conteudoLimpo) > 200) $conteudoLimpo = substr($conteudoLimpo, 0, 200) . '...';
        ?>
        <article class="question-item">
          <h3><?= htmlspecialchars($p['titulo']) ?></h3>
          <p><?= htmlspecialchars($conteudoLimpo) ?></p>
          <div class="footer">
            <span class="post-meta"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
            <a href="questions.php?id=<?= $p['id'] ?>" class="btn">Ver mais</a>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Você ainda não fez nenhuma pergunta.</p>
    <?php endif; ?>
  </div>
</main>

<script>
// Toggle do formulário
// Elementos do DOM
document.addEventListener('DOMContentLoaded', () => {
  const editBtn = document.getElementById('editBtn');
  const editForm = document.getElementById('editForm');
  const avatarPreview = document.getElementById('avatarPreview');
  const profileImageInput = document.getElementById('profile_image_input');
  const messageDiv = document.getElementById('message');
  const localFileInput = document.querySelector('input[name="profile_image_file"]');
  let currentObjectURL = null;

  // Toggle do formulário
  editBtn.addEventListener('click', () => {
    editForm.style.display = (editForm.style.display === 'block') ? 'none' : 'block';
    if (editForm.style.display === 'block') editForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });

  // Preview de arquivo local
  localFileInput.addEventListener('change', function(){
    const f = this.files[0];
    if (f) {
      if (currentObjectURL) URL.revokeObjectURL(currentObjectURL);
      currentObjectURL = URL.createObjectURL(f);
      avatarPreview.src = currentObjectURL;
      profileImageInput.value = ''; // Limpa campo Cloudinary
    }
  });

  // Cloudinary widget
  const cloudName = "dctvku3xp";
  const uploadPreset = "cryptmedia"; // Substitua pelo seu preset unsigned
  let widget = null;
  if (typeof cloudinary !== 'undefined') {
    widget = cloudinary.createUploadWidget({
      cloudName: cloudName,
      uploadPreset: uploadPreset,
      sources: ["local","url","camera","image_search"],
      multiple: false,
      maxFileSize: 5 * 1024 * 1024,
      cropping: false,
      resourceType: "image"
    }, (error, result) => {
      if (error) {
        console.error("Erro no Cloudinary:", error);
        showMessage("Erro ao fazer upload: " + (error.message || "Verifique configuração."), "error");
        return;
      }
      if (result && result.event === "success") {
        const imageUrl = result.info.secure_url;
        avatarPreview.src = imageUrl;
        profileImageInput.value = imageUrl;
        showMessage("Imagem enviada com sucesso!", "success");
      }
    });
  }

  document.getElementById('upload_widget_btn').addEventListener('click', () => {
    if (widget) widget.open();
    else showMessage("Widget não inicializado.", "error");
  });

  // Submissão via AJAX
  editForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(editForm);

    try {
      const response = await fetch('src/actions/profile_update.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        showMessage(result.message, "success");

        // Atualiza avatar, bio e PGP na tela sem recarregar
        if (result.avatar_url) avatarPreview.src = result.avatar_url;
        if (result.bio) document.querySelector('.bio').textContent = result.bio;
        if (result.pgp_key) {
          const pgpElem = document.querySelector('.profile-header p:nth-of-type(3)'); // Ajuste se necessário
          pgpElem.innerHTML = `<strong>Chave PGP:</strong><br>${result.pgp_key.replace(/\n/g, '<br>')}`;
        }

        editForm.style.display = 'none';
      } else {
        showMessage(result.message, "error");
      }
    } catch (err) {
      console.error("Erro na submissão:", err);
      showMessage("Erro ao salvar. Tente novamente.", "error");
    }
  });

  // Função para mostrar mensagens
  function showMessage(text, type) {
    messageDiv.innerHTML = `<div class="${type}-msg">${text}</div>`;
    setTimeout(() => messageDiv.innerHTML = '', 4000);
  }

  // Força recarregar imagem se der erro
  avatarPreview.addEventListener('error', () => avatarPreview.src = "uploads/default.png");
});

</script>

</body>
</html>
