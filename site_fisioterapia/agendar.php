<?php
session_start();  // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header('Location: login.php');  // Redireciona para a página de login caso não esteja logado
    exit();
}

require_once 'db/conn.php'; // Inclui o arquivo de conexão com o banco

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém os dados do formulário de agendamento
    $id_usuario1 = $_SESSION['id'];  // Pega o ID do usuário da sessão
    $data_hora = $_POST['data_hora'];  // Data e hora do agendamento
    $servico = htmlspecialchars($_POST['servico']);  // Serviço desejado
    $observacoes = htmlspecialchars($_POST['observacoes']);  // Observações
    $relato = htmlspecialchars($_POST['relato']);  // Relato do paciente

    // Verifica se o ID do usuário existe na tabela 'usuario1'
    $stmt = $conn->prepare("SELECT id FROM usuario1 WHERE id = ?");
    $stmt->execute([$id_usuario1]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Insere o agendamento no banco de dados
        $stmt = $conn->prepare("INSERT INTO agendamentos (id_usuario1, data_hora, servico, observacoes, relato) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_usuario1, $data_hora, $servico, $observacoes, $relato]);

        // Mensagem de sucesso
        echo "Agendamento realizado com sucesso!";
    } else {
        echo "Erro: Usuário não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agendar Consulta</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h2>Agendar Consulta</h2>
    <form method="POST" action="">
        <label for="data_hora">Data e Hora:</label>
        <input type="datetime-local" name="data_hora" required>

        <label for="servico">Serviço Desejado:</label>
        <input type="text" name="servico" placeholder="Ex: Fisioterapia, Avaliação, etc." required>

        <label for="observacoes">Observações:</label>
        <textarea name="observacoes" placeholder="Observações adicionais..."></textarea>

        <label for="relato">Relato do Paciente:</label>
        <textarea name="relato" placeholder="Descreva o que aconteceu..." required></textarea>

        <button type="submit">Agendar</button>
    </form>

    <a href="logout.php" class="btn">Sair</a>
</div>
</body>
</html>
