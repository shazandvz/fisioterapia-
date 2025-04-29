-- Usar o banco
USE aulaphp;

-- Criar a tabela de feedbacks
CREATE TABLE IF NOT EXISTS feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_agendamento INT NOT NULL,
    id_usuario1 INT NOT NULL,
    feedback TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_agendamento) REFERENCES agendamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario1) REFERENCES usuario1(id) ON DELETE CASCADE
);
