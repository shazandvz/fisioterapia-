<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'db/conn.php';

    // Recebe os dados do formulário
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $senha_confirma = $_POST['senha_confirma'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $data_nascimento = $_POST['data_nascimento'];
    $cpf = preg_replace('/\D/', '', $_POST['cpf']); // Remove caracteres não numéricos

    // Verifica se as senhas coincidem
    if ($senha !== $senha_confirma) {
        $erro = "As senhas não coincidem. Tente novamente.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $senha)) {
        $erro = "A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, um número e um caractere especial.";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $nome)) {
        $erro = "O nome completo deve conter apenas letras e espaços.";
    } elseif (!validateDate($data_nascimento)) {
        $erro = "A data de nascimento não é válida. Use o formato dd/mm/yyyy e certifique-se de que não está no futuro.";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT id FROM usuario1 WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $erro = "Esse email já está cadastrado.";
        } else {
            $stmt = $conn->prepare("INSERT INTO usuario1 (nome, email, senha, telefone, endereco, data_nascimento, cpf) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha_hash, $telefone, $endereco, $data_nascimento, $cpf]);
            $sucesso = "Cadastro realizado com sucesso! <a href='login.php'>Faça login aqui</a>";
        }
    }
}

function validateDate($date) {
    $d = DateTime::createFromFormat('d/m/Y', $date);
    return $d && $d->format('d/m/Y') === $date && $d->getTimestamp() <= time();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - Clínica Dr. Lucas Menezes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .erro {
            color: red;
            font-size: 13px;
        }

        .sucesso {
            color: green;
            font-size: 14px;
        }

        a {
            color: #007bff;
        }
    </style>
</head>
<body>

<main class="container">
    <h2>Preencha os dados abaixo</h2>

    <?php if (isset($erro)) echo "<p class='erro'>$erro</p>"; ?>
    <?php if (isset($sucesso)) echo "<p class='sucesso'>$sucesso</p>"; ?>

    <form method="POST" action="">
        <div class="form-group">
            <input type="text" name="nome" placeholder="Nome completo" required
                   value="<?= isset($nome) ? htmlspecialchars($nome) : '' ?>">
        </div>

        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required
                   value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
        </div>

        <div class="form-group">
            <input type="password" name="senha" placeholder="Senha" required>
        </div>

        <div class="form-group">
            <input type="password" name="senha_confirma" placeholder="Confirme a senha" required>
        </div>

        <div class="form-group">
            <input type="text" name="telefone" placeholder="Telefone" required
                   value="<?= isset($telefone) ? htmlspecialchars($telefone) : '' ?>">
        </div>

        <div class="form-group">
            <input type="text" name="endereco" placeholder="Endereço" required
                   value="<?= isset($endereco) ? htmlspecialchars($endereco) : '' ?>">
        </div>

        <div class="form-group">
            <input type="text" name="data_nascimento" placeholder="Data de nascimento (dd/mm/yyyy)" required
                   value="<?= isset($data_nascimento) ? htmlspecialchars($data_nascimento) : '' ?>">
        </div>

        <div class="form-group">
            <input type="text" name="cpf" placeholder="CPF" required
                   value="<?= isset($cpf) ? htmlspecialchars($cpf) : '' ?>">
        </div>

        <button type="submit">Cadastrar</button>
    </form>

    <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
</main>

</body>
</html>
