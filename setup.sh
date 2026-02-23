#!/bin/bash
# -------------------------------
# Script de setup completo Proative-Who
# Autor: Guilherme
# -------------------------------

echo "🚀 Iniciando setup do Proative-Who..."

# 1️⃣ Entrar na pasta do projeto
cd "$(dirname "$0")" || exit
echo "📂 Diretório atual: $(pwd)"

# 2️⃣ Instalar dependências do PHP (se precisar)
echo "🔧 Instalando dependências PHP..."
if [ -f composer.json ]; then
    composer install
else
    echo "⚠️ Nenhum composer.json encontrado, pulando..."
fi

# 3️⃣ Configurar banco de dados
DB_NAME="who_db"
DB_USER="root"
DB_PASS=""

echo "🗄️ Configurando banco de dados MySQL..."
mysql -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
echo "✅ Banco $DB_NAME criado (se não existia)."

# 4️⃣ Criar evento automático para apagar mensagens
echo "⏱ Criando evento MySQL para apagar mensagens com mais de 4h..."
mysql -u $DB_USER -p$DB_PASS $DB_NAME <<MYSQL
DELIMITER $$
CREATE EVENT IF NOT EXISTS apagar_mensagens_4h
ON SCHEDULE EVERY 4 HOUR
DO
  DELETE FROM questions
  WHERE created_at < NOW() - INTERVAL 4 HOUR;
$$
DELIMITER ;
MYSQL
echo "✅ Evento criado."

# 5️⃣ Entrar na pasta frontend e instalar dependências React
cd frontend || { echo "❌ Pasta frontend não encontrada"; exit 1; }
echo "📦 Instalando dependências React..."
npm install

# 6️⃣ Rodar build do React para produção
echo "🏗️ Gerando build do React..."
npm run build

# 7️⃣ Copiar build para pasta pública do PHP
echo "📂 Copiando build para htdocs..."
cd ..
cp -r frontend/build/* ./public/
echo "✅ Build copiado para ./public/"

# 8️⃣ Permissões
echo "🔐 Ajustando permissões..."
chmod -R 755 ./public

echo "🎉 Setup finalizado! Acesse seu site no navegador."
