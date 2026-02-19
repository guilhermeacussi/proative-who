
USE who_db;

SET GLOBAL event_scheduler = ON;

-- Cria o evento para apagar as msg
CREATE EVENT IF NOT EXISTS apagar_mensagens_4h
ON SCHEDULE EVERY 1 HOUR
DO
  DELETE FROM questions
  WHERE created_at < NOW() - INTERVAL 4 HOUR;
