<?php
// Conexão com o banco de dados
include 'includes/db_connect.php';

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// URL da API
$url_api = 'http://localhost/api.php';

// Chave de autenticação
$api_key = 'batata';

// Função para enviar uma mensagem para um número de telefone
function enviarMensagem($contact, $message, $url_api, $api_key) {
    $payload = [
        'key' => $api_key,
        'phone_number' => $contact,
        'message' => $message
    ];
    
    $query = http_build_query($payload);
    $response = file_get_contents("$url_api?$query");

    if ($response === FALSE) {
        return "Erro ao enviar mensagem para $contact.";
    } else {
        return "Mensagem enviada para $contact com sucesso!";
    }
}

// Processa o envio das mensagens se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contacts']) && isset($_POST['message'])) {
    $contacts = array_map('trim', explode(',', $_POST['contacts']));
    $message = $_POST['message'];
    $results = [];

    foreach ($contacts as $contact) {
        if (!empty($contact)) {
            $results[] = enviarMensagem($contact, $message, $url_api, $api_key);
        }
    }

    // Exibe os resultados
    $result_output = implode('<br>', $results);
} else {
    $result_output = '';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Mensagens em Massa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f7f7f7;
        }

        .container {
            width: 90%;
            max-width: 800px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
            box-sizing: border-box;
            position: relative; /* Adicionado para o posicionamento absoluto do botão */
        }

        .back-button {
            position: absolute;
            top: 10px;
            right: 10px;
            text-decoration: none;
            color: #4CAF50;
            font-size: 16px;
            font-weight: bold;
            background-color: #f7f7f7;
            border: 2px solid #4CAF50;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
        }

        .back-button:hover {
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            box-sizing: border-box;
            height: 120px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }

        button {
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .result {
            margin-top: 20px;
            text-align: center;
        }

        .result h3 {
            color: #333;
        }

        .result p {
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 600px) {
            textarea {
                height: 80px;
            }

            button {
                width: 100%;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="back-button" href="whatsapp.php">&larr; Voltar</a>
        <h2>Enviar Mensagem para Múltiplos Contatos</h2>
        <form method="POST">
            <textarea name="contacts" placeholder="Digite números de telefone separados por vírgula (e.g., 123456789, 987654321)"></textarea>
            <textarea name="message" placeholder="Digite a mensagem aqui"></textarea>
            <button type="submit">Enviar</button>
        </form>

        <!-- Exibição dos resultados -->
        <div class="result">
            <?php if ($result_output): ?>
                <h3>Resultados:</h3>
                <p><?php echo $result_output; ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
