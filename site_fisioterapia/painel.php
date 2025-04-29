<?php
session_start();

function validateDate($date) {
    $d = DateTime::createFromFormat('d/m/Y', $date);
    return $d && $d->format('d/m/Y') === $date && $d->getTimestamp() <= time();
}

function validateCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }

    return true;
}

$erro = '';
$sucesso = '';
$nome = $email = $telefone = $endereco = $data_nascimento = $cpf = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'db/conn.php';

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $senha_confirma = $_POST['senha_confirma'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $data_nascimento = $_POST['data_nascimento'];
    $cpf = preg_replace('/\D/', '', $_POST['cpf']);

    if ($senha !== $senha_confirma) {
        $erro = "As senhas não coincidem.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $senha)) {
        $erro = "A senha deve ter ao menos 8 caracteres, uma letra maiúscula, um número e um caractere especial.";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $nome)) {
        $erro = "O nome deve conter apenas letras e espaços.";
    } elseif (!validateDate($data_nascimento)) {
        $erro = "A data de nascimento deve estar no formato dd/mm/yyyy e não pode ser no futuro.";
    } elseif (!validateCPF($cpf)) {
        $erro = "CPF inválido.";
    } else {
        $stmt = $conn->prepare("SELECT email, cpf FROM usuario1 WHERE email = ? OR cpf = ?");
        $stmt->execute([$email, $cpf]);
        $existe = $stmt->fetch();

        if ($existe) {
            if ($existe['email'] === $email) {
                $erro = "Este e-mail já está cadastrado.";
            } else {
                $erro = "Este CPF já está cadastrado.";
            }
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuario1 (nome, email, senha, telefone, endereco, data_nascimento, cpf) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha_hash, $telefone, $endereco, $data_nascimento, $cpf]);
            $sucesso = "Cadastro realizado com sucesso! <a href='login.php'>Faça login</a>";
            $nome = $email = $telefone = $endereco = $data_nascimento = $cpf = '';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - Clínica Lucas Menezes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            margin: 50px auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type=text], input[type=email], input[type=password] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button[type=submit] {
            background: #4CAF50;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
        .erro {
            color: red;
            margin-bottom: 10px;
        }
        .sucesso {
            color: green;
            margin-bottom: 10px;
        }
        a {
            color: #007bff;
        }
    </style>
    <script>
        // Função de máscara para CPF
        function mascaraCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            return cpf;
        }

        // Função de máscara para a data de nascimento
        function mascaraData(data) {
            data = data.replace(/\D/g, '');
            data = data.replace(/(\d{2})(\d)/, '$1/$2');
            data = data.replace(/(\d{2})(\d{1,2})$/, '$1/$2');
            return data;
        }

        // Atualiza o campo CPF com a máscara
        function atualizarCPF() {
            var cpf = document.getElementById("cpf").value;
            document.getElementById("cpf").value = mascaraCPF(cpf);
        }

        // Atualiza o campo data de nascimento com a máscara
        function atualizarDataNascimento() {
            var data = document.getElementById("data_nascimento").value;
            document.getElementById("data_nascimento").value = mascaraData(data);
        }

        // Função de validação no front-end para CPF e data
        function validarFormulario() {
            var cpf = document.getElementById("cpf").value;
            var dataNascimento = document.getElementById("data_nascimento").value;

            if (cpf.length < 14) {
                alert("Por favor, insira um CPF válido.");
                return false;
            }

            if (dataNascimento.length < 10) {
                alert("Por favor, insira uma data válida.");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Cadastro de Paciente</h2>

    <?php if ($erro): ?>
        <p class="erro"><?php echo $erro; ?></p>
    <?php elseif ($sucesso): ?>
        <p class="sucesso"><?php echo $sucesso; ?></p>
    <?php endif; ?>

    <form method="POST" action="" onsubmit="return validarFormulario()">
        <div class="form-group">
            <input type="text" name="nome" placeholder="Nome completo" value="<?php echo htmlspecialchars($nome); ?>" required>
        </div>

        <div class="form-group">
            <input type="email" name="email" placeholder="E-mail" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div class="form-group">
            <input type="password" name="senha" placeholder="Senha" required>
        </div>

        <div class="form-group">
            <input type="password" name="senha_confirma" placeholder="Confirme a senha" required>
        </div>

        <div class="form-group">
            <input type="text" name="telefone" placeholder="Telefone" value="<?php echo htmlspecialchars($telefone); ?>" required>
        </div>

        <div class="form-group">
            <input type="text" name="endereco" placeholder="Endereço" value="<?php echo htmlspecialchars($endereco); ?>" required>
        </div>

        <div class="form-group">
            <input type="text" id="data_nascimento" name="data_nascimento" placeholder="Data de nascimento (dd/mm/yyyy)" value="<?php echo htmlspecialchars($data_nascimento); ?>" onkeyup="atualizarDataNascimento()" required>
        </div>

        <div class="form-group">
            <input type="text" id="cpf" name="cpf" placeholder="CPF" value="<?php echo htmlspecialchars($cpf); ?>" onkeyup="atualizarCPF()" required>
        </div>

        <button type="submit">Cadastrar</button>
    </form>

    <p>Já tem uma conta? <a href="login.php">Entrar</a></p>
</div>
</body>
</html>
