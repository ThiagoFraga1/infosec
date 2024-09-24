<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['cliente_id'])) {
    header("Location: ../login.php");
    exit();
}

$funcionario_id = $_SESSION['user_id'];
$cliente_id = $_POST['cliente_id'];

// Atualize o cliente para atribuí-lo ao funcionário
$update_query = "UPDATE clientes SET funcionario_id = $funcionario_id, status = 2 WHERE id = $cliente_id";
if (mysqli_query($conn, $update_query)) {
    header("Location: ../dashboard.php#em-andamento");
} else {
    echo "Erro ao atualizar cliente: " . mysqli_error($conn);
}
?>

