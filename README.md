# 🕵️‍♂️ Who? — Plataforma de Perguntas e Respostas

**Who?** é um site de perguntas e respostas anônimo, inspirado no estilo underground e minimalista.  
Usuários podem criar contas, fazer perguntas, responder outras pessoas e curtir postagens.  
Administradores possuem um **painel de controle** completo para gerenciar o conteúdo da comunidade.

---

## 🚀 Funcionalidades

### 👤 Usuários
- Registro e login com proteção por **reCAPTCHA**  
- Edição de perfil com **bio** e chave PGP (opcional)  
- Sistema de perguntas e respostas  
- Curtidas em perguntas e respostas  
- Chat interno entre usuários  

### 🧠 Administradores
- Painel administrativo com estatísticas:  
  - Total de usuários  
  - Total de perguntas  
  - Total de respostas  
  - Total de curtidas  
- Tabelas com os usuários, perguntas e respostas recentes  
- Botões para **visualizar** e **deletar** cada item  
- Sistema de permissão baseado na coluna `is_admin`  

---

## 🗄️ Estrutura do Banco de Dados

### Tabela `users`
| Campo | Tipo | Descrição |
|-------|------|------------|
| id | INT | Identificador do usuário |
| nome | VARCHAR(100) | Nome do usuário |
| email | VARCHAR(255) | Email de login |
| senha | VARCHAR(255) | Senha criptografada |
| bio | TEXT | Biografia (opcional) |
| pgp_key | TEXT | Chave PGP (opcional) |
| is_admin | TINYINT(1) | Define se o usuário é administrador |
| created_at | DATETIME | Data de criação da conta |

### Tabela `questions`
| Campo | Tipo | Descrição |
|-------|------|------------|
| id | INT | Identificador da pergunta |
| user_id | INT | ID do autor |
| titulo | VARCHAR(255) | Título da pergunta |
| conteudo | TEXT | Corpo da pergunta |
| created_at | DATETIME | Data da publicação |

### Tabela `answers`
| Campo | Tipo | Descrição |
|-------|------|------------|
| id | INT | Identificador da resposta |
| user_id | INT | ID do autor |
| question_id | INT | Pergunta associada |
| conteudo | TEXT | Corpo da resposta |
| created_at | DATETIME | Data da publicação |

### Tabela `likes`
| Campo | Tipo | Descrição |
|-------|------|------------|
| id | INT | Identificador |
| user_id | INT | Usuário que curtiu |
| question_id | INT | Pergunta curtida (pode ser `NULL`) |
| answer_id | INT | Resposta curtida (pode ser `NULL`) |

---

## ⚙️ Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/seuusuario/who.git
   cd who
