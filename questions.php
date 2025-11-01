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
    body {
      background-color: #0b0b0b;
      color: #fff;
      font-family: "Inter", sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }

    header {
      width: 100%;
      padding: 20px 0;
      background: #0f0f0f;
      text-align: center;
      box-shadow: 0 0 15px rgba(128, 0, 255, 0.2);
    }

    header h1 {
      color: #b980ff;
      font-size: 1.8rem;
      margin: 0;
    }

    .container {
      max-width: 800px;
      width: 90%;
      background: #121212;
      border-radius: 16px;
      box-shadow: 0 0 25px rgba(128, 0, 255, 0.15);
      padding: 30px;
      margin: 40px auto;
      transition: 0.3s ease;
    }

    .container:hover {
      box-shadow: 0 0 40px rgba(128, 0, 255, 0.25);
    }

    h2 {
      color: #b980ff;
      font-size: 2rem;
      margin-bottom: 10px;
    }

    .meta {
      color: #aaa;
      font-size: 0.9rem;
      margin-bottom: 20px;
    }

    .content {
      line-height: 1.7;
      color: #ddd;
      background: #1a1a1a;
      padding: 20px;
      border-radius: 10px;
      border: 1px solid #2b2b2b;
      margin-bottom: 25px;
    }

    .btn {
      background-color: #b980ff;
      color: #fff;
      border: none;
      padding: 12px 24px;
      border-radius: 10px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background-color: #9d63ff;
    }

    #responder-container {
      display: none;
      margin-top: 20px;
      animation: fadeIn 0.4s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    footer {
      margin-top: auto;
      text-align: center;
      padding: 20px;
      color: #777;
      border-top: 1px solid #222;
      width: 100%;
    }

    @media (max-width: 600px) {
      .container {
        padding: 20px;
      }
      h2 {
        font-size: 1.6rem;
      }
      .btn {
        width: 100%;
      }
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
