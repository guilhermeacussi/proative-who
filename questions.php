<?php
require_once '../config.php';
session_start();

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT q.titulo, q.conteudo, q.data_criacao, u.nome AS autor 
                        FROM perguntas q 
                        JOIN usuarios u ON q.user_id = u.id 
                        WHERE q.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pergunta = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pergunta['titulo']) ?> - Who?</title>
  <link rel="stylesheet" href="css/global.css">

  <!-- TinyMCE -->
  <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    tinymce.init({
      selector: '#resposta',
      height: 200,
      menubar: false,
      skin: "oxide-dark",
      content_css: "dark",
      placeholder: "Escreva sua resposta aqui..."
    });
  </script>

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
    margin: 0;
    font-family: 'Libre Franklin', sans-serif; 
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

main.container {
    background: var(--secondary-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 40px;
    width: 100%;
    max-width: 400px; 
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5); 
}

.container h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 1.8rem;
    color: var(--accent-color);
    font-weight: 700;
}

form {
    display: flex;
    flex-direction: column;
}

label {
    font-size: 0.95rem;
    color: var(--text-color);
    margin-bottom: 8px;
    margin-top: 15px;
    font-weight: 600;
}

input[type="email"],
input[type="password"],
input[name="nome"]{
    width: 93%;
    padding: 12px;
    background: var(--input-bg);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    margin-bottom: 10px;
    font-size: 1rem;
    transition: 0.3s;
}

input[type="email"]:focus,
input[type="password"]:focus,
input[name="nome"]:focus{
    border-color: var(--accent-color);
    box-shadow: 0 0 5px rgba(185, 108, 255, 0.5);
    outline: none;
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
    margin-top: 30px;
    transition: background 0.3s, transform 0.1s;
}

.btn:hover {
    background: #a34de7; 
    transform: translateY(-1px); 
}

.container p {
    text-align: center;
    margin-top: 25px;
    font-size: 0.95rem;
    color: var(--text-muted);
}

.container p a {
    color: var(--accent-color);
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
}

.container p a:hover {
    color: #c49eff;
    text-decoration: underline;
}


  </style>
</head>
<body>

  <header>
    <h1>Who? - Perguntas & Respostas</h1>
  </header>

  <div class="container">
    <h2><?= htmlspecialchars($pergunta['titulo']) ?></h2>
    <p class="meta">Por <strong><?= htmlspecialchars($pergunta['autor']) ?></strong> — <?= date("d/m/Y H:i", strtotime($pergunta['data_criacao'])) ?></p>

    <div class="content">
      <?= nl2br(htmlspecialchars($pergunta['conteudo'])) ?>
    </div>

    <button class="btn" id="btnResponder">Responder</button>

    <div id="responder-container">
      <form method="POST" action="responder.php">
        <textarea id="resposta" name="resposta"></textarea>
        <input type="hidden" name="pergunta_id" value="<?= $id ?>">
        <button type="submit" class="btn" style="margin-top: 15px;">Enviar Resposta</button>
      </form>
    </div>
  </div>

  <footer>
    <p>Who? © <?= date('Y') ?> — Compartilhe conhecimento.</p>
  </footer>

  <script>
    document.getElementById('btnResponder').addEventListener('click', () => {
      const box = document.getElementById('responder-container');
      box.style.display = box.style.display === 'none' ? 'block' : 'none';
    });
  </script>

</body>
</html>
