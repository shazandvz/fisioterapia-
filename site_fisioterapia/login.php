<?php
session_start();
require_once 'db/conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Verificar se o email existe
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuario1 WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Login bem-sucedido, salvar dados na sessão
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];

        // Redirecionar para a página de agendamento ou outra página que preferir
        header("Location: agendar.php");  // Corrigido para a página de agendamento
        exit();
    } else {
        $erro = "Email ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <?php if (isset($erro)) { echo "<p class='erro'>$erro</p>"; } ?>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>
    <p>Não tem uma conta? <a href="register.php">Cadastre-se</a></p>
</div>
</body>
</html>

