<?php
$servername = "sql209.infinityfree.com"; // Ou o endereço do seu servidor MySQL
$username = "if0_37357353"; // Seu nome de usuário MySQL
$password = "AB2x17BoH5GZxQ"; // Sua senha MySQL
$dbname = "if0_37357353_infosec"; // Nome do banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
