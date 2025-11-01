<?php
require_once 'src/config.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazer Pergunta - Who?</title>
	<link rel="stylesheet" href="css/global.css">
    <!-- Quill -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
<style>
/* ===== WHO? | Estilo aprimorado para página de perguntas ===== */

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', sans-serif;
}

/* ===== Corpo ===== */
body {
  background: radial-gradient(circle at top, #1b002f, #0a0a0a 60%);
  color: #f5f5f5;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
}

/* ===== Header ===== */
header {
  width: 100%;
  background: rgba(20, 20, 20, 0.85);
  backdrop-filter: blur(8px);
  padding: 15px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(160, 102, 255, 0.2);
  position: sticky;
  top: 0;
  z-index: 10;
}

header .brand {
  font-size: 1.7rem;
  color: #a066ff;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-decoration: none;
  transition: 0.3s ease;
}

header .brand:hover {
  color: #c49eff;
  text-shadow: 0 0 8px rgba(196, 158, 255, 0.5);
}

header nav a {
  color: #dcdcdc;
  text-decoration: none;
  margin-left: 25px;
  font-size: 0.95rem;
  transition: 0.3s ease;
}

header nav a:hover {
  color: #a066ff;
  text-shadow: 0 0 6px rgba(160, 102, 255, 0.4);
}

/* ===== Container Principal ===== */
main {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding: 60px 15px;
}

.form-container {
  background: linear-gradient(145deg, #131313, #0f0f0f);
  border: 1px solid rgba(160, 102, 255, 0.15);
  border-radius: 16px;
  padding: 40px;
  width: 100%;
  max-width: 600px;
  box-shadow: 0 0 25px rgba(160, 102, 255, 0.1);
  transition: 0.4s ease;
}

.form-container:hover {
  box-shadow: 0 0 30px rgba(160, 102, 255, 0.25);
}

/* ===== Título ===== */
.form-container h1 {
  text-align: center;
  margin-bottom: 30px;
  color: #fff;
  font-weight: 700;
  font-size: 1.8rem;
  text-shadow: 0 0 12px rgba(160, 102, 255, 0.3);
}

/* ===== Campos ===== */
label {
  display: block;
  font-size: 0.9rem;
  margin-bottom: 6px;
  color: #ccc;
}

input[type="text"],
textarea {
  width: 100%;
  padding: 12px 14px;
  background: #121212;
  color: #fff;
  border: 1px solid #2a2a2a;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 0.95rem;
  transition: 0.3s ease;
}

input[type="text"]:focus,
textarea:focus {
  border-color: #a066ff;
  box-shadow: 0 0 6px rgba(160, 102, 255, 0.4);
  outline: none;
}

/* ===== Botão ===== */
button,
input[type="submit"] {
  width: 100%;
  background: linear-gradient(90deg, #a066ff, #7c4dff);
  color: white;
  border: none;
  padding: 14px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  letter-spacing: 0.4px;
  transition: 0.3s ease;
  box-shadow: 0 0 10px rgba(160, 102, 255, 0.25);
}

button:hover,
input[type="submit"]:hover {
  background: linear-gradient(90deg, #b67cff, #9b4dff);
  box-shadow: 0 0 20px rgba(182, 124, 255, 0.35);
  transform: translateY(-1px);
}

/* ===== Footer ===== */
footer {
  background-color: #0f0f0f;
  padding: 20px;
  text-align: center;
  font-size: 0.9rem;
  color: #999;
  border-top: 1px solid rgba(160, 102, 255, 0.15);
  margin-top: auto;
}

footer a {
  color: #a066ff;
  text-decoration: none;
}

footer a:hover {
  text-decoration: underline;
}

/* ===== Responsividade ===== */
@media (max-width: 768px) {
  header {
    flex-direction: column;
    align-items: flex-start;
  }

  header nav a {
    margin: 10px 10px 0 0;
  }

  .form-container {
    padding: 25px;
  }

  .form-container h1 {
    font-size: 1.5rem;
  }
}


    </style>
    
</head>
<body>
    <div class="container">
       
        <h2>Fazer uma Pergunta</h2>
<br>
        <form id="askForm" action="src/actions/ask_process.php" method="POST">
            <label for="titulo">Título da Pergunta:</label>
            <input type="text" id="titulo" name="titulo" required placeholder="Digite o título da sua pergunta...">

            <label for="conteudo">Conteúdo da Pergunta:</label>
            <div id="editor"></div>

            <!-- Campo oculto que vai receber o conteúdo do Quill -->
            <input type="hidden" name="conteudo" id="conteudo">

            <br>
            
            <button type="submit">Publicar Pergunta</button>
        </form>
    </div>

    <script>
        // Inicializa o Quill
        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Escreva sua pergunta aqui...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    [{ 'align': [] }], // dropdown de alinhamento
                    ['clean']
                ]
            }
        });

        // Sincroniza Quill com o campo hidden antes do submit
        const form = document.getElementById('askForm');
        form.addEventListener('submit', function(e) {
            document.getElementById('conteudo').value = quill.root.innerHTML;
        });
    </script>
</body>
</html>
