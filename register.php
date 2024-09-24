<?php
    include_once("includes/head.php");

    $message = ""; // Variável para armazenar mensagens de sucesso ou erro

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Conectar ao banco de dados
        include 'includes/db_connect.php';

        // Coletar e sanitizar os dados do formulário
        $nome_completo = mysqli_real_escape_string($conn, $_POST['nome_completo']);
        $cpf = mysqli_real_escape_string($conn, $_POST['cpf']);
        $data_nasc = mysqli_real_escape_string($conn, $_POST['data_nasc']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $contato = mysqli_real_escape_string($conn, $_POST['contato']);
        $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT); // Criptografa a senha
        $type = mysqli_real_escape_string($conn, $_POST['type']);

        // Inserir os dados no banco de dados
        $sql = "INSERT INTO funcionarios (nome_completo, cpf, data_nasc, email, contato, senha, type) 
                VALUES ('$nome_completo', '$cpf', '$data_nasc', '$email', '$contato', '$senha', '$type')";

        if (mysqli_query($conn, $sql)) {
            $message = "Registro bem-sucedido!";
        } else {
            $message = "Erro ao registrar: " . mysqli_error($conn);
        }

        // Fechar conexão
        mysqli_close($conn);
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar-se</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #181818;
            color: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #282828;
            border-radius: 10px;
            padding: 20px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 30px;
            background-color: #1E1E1E;
            color: #ffffff;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            border: none;
            border-radius: 30px;
            background-color: #00A86B;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #007f55;
        }

        .message {
            margin-top: 20px;
            font-size: 14px;
            color: #FFD700;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrar-se</h1>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="nome_completo" placeholder="Nome Completo" required><br>
            <input type="text" name="cpf" placeholder="CPF" required><br>
            <input type="date" name="data_nasc" placeholder="Data de Nascimento" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="text" name="contato" placeholder="Contato (Telefone)" required><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <select name="type" required>
                <option value="0">Funcionário</option>
                <option value="1">Admin</option>
            </select><br>
            <button type="submit">Registrar</button>
        </form>
    </div>
</body>
</html>
