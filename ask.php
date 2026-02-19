<?php
require __DIR__ . '/src/init.php';
$me = current_user($pdo);

// Verifica se o usuário está logado
if (empty($me)) {
    header("Location: login.php");
    exit;
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fazer Pergunta - Who?</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="icons/index.png">
    
    <!-- Quill -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>

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

        /* --- FORMULÁRIO DE PERGUNTA --- */
        .ask-form {
          padding: 15px 20px;
          border-bottom: 1px solid var(--border-color);
          background: var(--primary-bg);
        }

        .ask-form label {
          display: block;
          font-size: 0.9rem;
          margin-bottom: 6px;
          color: var(--text-color);
        }

        .ask-form input[type="text"] {
          width: 100%;
          padding: 12px 14px;
          background: var(--secondary-bg);
          color: var(--text-color);
          border: 1px solid var(--border-color);
          border-radius: 6px;
          margin-bottom: 20px;
          font-size: 1rem;
          transition: 0.3s;
          box-sizing: border-box; /* Evita overflow */
        }

        .ask-form input[type="text"]:focus {
          border-color: var(--accent-color);
          box-shadow: 0 0 5px rgba(185, 108, 255, 0.5);
          outline: none;
        }

        /* Estilos para o editor Quill */
        .ask-form #editor {
          margin-bottom: 20px;
          border: 1px solid var(--border-color);
          border-radius: 6px;
          background: var(--secondary-bg);
          color: var(--text-color);
          min-height: 200px; /* Altura mínima para melhor visualização */
        }

        .ask-form #editor .ql-toolbar {
          border: none;
          border-bottom: 1px solid var(--border-color);
          background: var(--primary-bg);
        }

        .ask-form #editor .ql-container {
          border: none;
          font-size: 1rem;
          line-height: 1.5;
        }

        .ask-form #editor .ql-editor {
          padding: 12px 14px;
          color: var(--text-color);
        }

        .ask-form #editor .ql-editor.ql-blank::before {
          color: var(--text-muted);
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
        <a href="users.php">Descobrir</a>
        <a href="profile.php">Meu perfil</a>
        <a href="/src/actions/logout.php">Sair</a>
    </nav>
</header>


<main class="main-layout main-container">
    <aside class="sidebar">
        <div class="trending-box">
            <main class="main-layout main-container">
    <aside class="sidebar">
        <div class="trending-box">
            <h3>Regras do Site</h3>
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
        
      
    </aside>

    <div class="feed-content">
        <h2>Fazer uma Pergunta</h2>
        
        <section class="ask-form">
            <form id="askForm" action="src/actions/ask_process.php" method="POST">
                <label for="titulo">Título da Pergunta:</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Digite o título da sua pergunta...">

                <label for="conteudo">Conteúdo da Pergunta:</label>
                <div id="editor"></div>

                <!-- Campo oculto que vai receber o conteúdo do Quill -->
                <input type="hidden" name="conteudo" id="conteudo">

                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Publicar Pergunta</button>
            </form>
        </section>
    </div>
</main>

 <!--
POSSIVEL CODIGO FUTURO 

<nav class="mobile-nav-bar"> 

    <a href="index.php"><i class="fas fa-home"></i></a>
    <a href="explore.php"><i class="fas fa-search"></i></a>
    <a href="notifications.php"><i class="fas fa-bell"></i></a>
    <a href="messages.php"><i class="fas fa-envelope"></i></a>
</nav>

 -->
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

</body>
</html>
