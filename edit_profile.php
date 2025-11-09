<?php
require __DIR__ . '/src/init.php';
$me = current_user($pdo);

// Verifica se o usuário está logado
if (!$me) {
    header('Location: login.php');
    exit;
}

// Diretório de upload
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $bio = trim($_POST['bio']);
    $pgp_key = trim($_POST['pgp_key']);
    // Armazena o caminho da imagem atual (o valor antigo)
    $profile_image = $me['profile_image']; 

    // Upload da imagem de perfil
    if (!empty($_FILES['profile_image']['name'])) {
        $file = $_FILES['profile_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        // VALIDAÇÃO DE SEGURANÇA: Validação de tipo MIME real para mitigar RCE (Remote Code Execution)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($ext, $allowed_exts) && in_array($mime_type, $allowed_mimes)) {
            if ($file['size'] <= 2 * 1024 * 1024) { // Máx. 2MB
                
                // CRIA NOME ÚNICO: Cria um nome de arquivo seguro
                $new_name = uniqid('avatar_', true) . '.' . $ext;
                $dest = $upload_dir . $new_name;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    
                    // 1. Armazena o caminho antigo antes de definir o novo
                    $old_image = $me['profile_image'];
                    
                    // 2. Define o novo caminho para o banco de dados
                    $profile_image = 'uploads/' . $new_name;
                    
                    // 3. LÓGICA PARA EXCLUIR A IMAGEM ANTERIOR (Otimização de Disco) 🗑️
                    // Verifica se a imagem antiga existe e não é a imagem padrão
                    if (!empty($old_image) && $old_image !== 'uploads/default.png') {
                        // Usamos basename para extrair apenas o nome do arquivo, evitando path traversal
                        $old_filename_only = basename($old_image); 
                        $old_path_on_disk = $upload_dir . $old_filename_only;
                        
                        if (file_exists($old_path_on_disk)) {
                            // Exclui o arquivo antigo do disco
                            unlink($old_path_on_disk);
                        }
                    }
                    // Fim da lógica de exclusão

                } else {
                    $erro = 'Erro ao mover o arquivo para o destino. Permissões de pasta?';
                }
            } else {
                $erro = 'A imagem excede o tamanho máximo de 2MB.';
            }
        } else {
            $erro = 'Formato ou tipo MIME inválido. Use JPG, PNG ou GIF.';
        }
    }

    // ATUALIZAÇÃO DO BANCO DE DADOS
    // Apenas executa a atualização se não houver erros de upload/validação
    if (!$erro) {
        try {
            $stmt = $pdo->prepare('UPDATE users SET nome = ?, bio = ?, pgp_key = ?, profile_image = ? WHERE id = ?');
            $stmt->execute([$nome, $bio, $pgp_key, $profile_image, $me['id']]);
            $sucesso = 'Perfil atualizado com sucesso!';
            // Recarrega os dados do usuário para exibição imediata
            $me = current_user($pdo); 
        } catch (PDOException $e) {
            // Reverte para erro se o banco de dados falhar
            $erro = 'Erro interno ao salvar dados do perfil. Tente novamente.';
            // Logar $e->getMessage() para fins de debug e segurança
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Editar Perfil - <?= htmlspecialchars($me['nome']) ?></title>
<link rel="stylesheet" href="style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;600;700&display=swap" rel="stylesheet">
<style>
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
  font-family: 'Libre Franklin', sans-serif;
  margin: 0;
  padding: 0;
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

header.topbar nav a:hover {
  color: var(--accent-color);
}

main.container {
  max-width: 600px;
  margin: 40px auto;
  background: var(--secondary-bg);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.5);
}

h2 {
  text-align: center;
  color: var(--accent-color);
  margin-bottom: 20px;
}

form {
  display: flex;
  flex-direction: column;
}

label {
  margin-top: 15px;
  font-weight: 600;
}

input[type="text"],
textarea {
  width: 100%;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid var(--border-color);
  background: var(--input-bg);
  color: var(--text-color);
  font-size: 1rem;
}

textarea {
  resize: vertical;
  min-height: 100px;
}

input[type="file"] {
  margin-top: 10px;
  color: var(--text-muted);
}

.btn {
  background: var(--accent-color);
  color: var(--text-color);
  border: none;
  padding: 12px;
  border-radius: 25px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 700;
  margin-top: 25px;
  transition: background 0.3s, transform 0.2s;
}

.btn:hover {
  background: #a34de7;
  transform: scale(1.02);
}

.message {
  text-align: center;
  margin-top: 10px;
  font-weight: 600;
}
.message.success { color: #7cff7c; }
.message.error { color: #ff6b6b; }

.avatar-preview {
  display: block;
  margin: 0 auto 20px;
  border-radius: 50%;
  width: 120px;
  height: 120px;
  object-fit: cover;
  border: 3px solid var(--accent-color);
  transition: 0.3s;
}

.avatar-preview:hover {
  transform: scale(1.05);
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

<main class="container">
  <h2>Editar Perfil</h2>

  <?php if ($erro): ?>
    <p class="message error"><?= htmlspecialchars($erro) ?></p>
  <?php elseif ($sucesso): ?>
    <p class="message success"><?= htmlspecialchars($sucesso) ?></p>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" id="formPerfil">
    <img id="preview" src="<?= htmlspecialchars($me['profile_image'] ?? 'default.png') ?>" alt="Avatar" class="avatar-preview">

    <label for="nome">Nome</label>
    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($me['nome']) ?>" required>

    <label for="bio">Bio</label>
    <textarea id="bio" name="bio" placeholder="Fale um pouco sobre você..."><?= htmlspecialchars($me['bio'] ?? '') ?></textarea>

    <label for="pgp_key">Chave PGP Pública</label>
    <textarea id="pgp_key" name="pgp_key" placeholder="Cole aqui sua chave PGP pública"><?= htmlspecialchars($me['pgp_key'] ?? '') ?></textarea>

    <label for="profile_image">Imagem de Perfil</label>
    <input type="file" name="profile_image" id="profile_image" accept="image/*">

    <button type="submit" class="btn">Salvar Alterações</button>
  </form>
</main>

<script>
// Pré-visualização da imagem
document.getElementById('profile_image').addEventListener('change', function(e) {
  const file = e.target.files[0];
  const preview = document.getElementById('preview');
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      alert('A imagem excede o tamanho máximo de 2MB.');
      e.target.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function(ev) {
      preview.src = ev.target.result;
    }
    reader.readAsDataURL(file);
  }
});
</script>
</body>
</html>
