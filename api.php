<?php
// Define a chave secreta da API
$api_key = "batata";

// Configura o cabeçalho para retorno em JSON
header('Content-Type: application/json');

// Conexão com o banco de dados (substitua as credenciais pelos seus dados)
$servername = "sql209.infinityfree.com"; // Ou o endereço do seu servidor MySQL
$username = "if0_37357353"; // Seu nome de usuário MySQL
$password = "AB2x17BoH5GZxQ"; // Sua senha MySQL
$dbname = "if0_37357353_chat_db"; // Nome do banco de dados


$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão com o banco de dados
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Erro na conexão com o banco de dados: " . $conn->connect_error
    ]);
    exit;
}

// Verifica se a chave da API foi fornecida
if (!isset($_GET['key']) || $_GET['key'] !== $api_key) {
    echo json_encode([
        "success" => false,
        "message" => "Chave da API inválida ou não fornecida."
    ]);
    exit;
}

// Verifica se os parâmetros necessários foram fornecidos
if (!isset($_GET['phone_number']) || !isset($_GET['message'])) {
    echo json_encode([
        "success" => false,
        "message" => "Parâmetros 'phone_number' e 'message' são obrigatórios."
    ]);
    exit;
}

// Recebe os parâmetros
$phone_number = $_GET['phone_number'];
$message = $_GET['message'];

// Usa o número de telefone como nome de contato
$contact_name = $phone_number;

// Direção sempre será 'sent'
$direction = "sent";

// Prepara e executa a query de inserção
$stmt = $conn->prepare("INSERT INTO messages (contact_name, phone_number, message, direction) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $contact_name, $phone_number, $message, $direction);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Mensagem enviada com sucesso!"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao enviar mensagem: " . $stmt->error
    ]);
}

// Fecha a conexão com o banco de dados
$stmt->close();
$conn->close();
?>
