<?php
session_start();
include 'db_connect.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Função para fazer parsing do conteúdo do arquivo
function parse_client_data($file_content) {
    $clients = [];
    $client_blocks = explode("====================================", $file_content);
    
    foreach ($client_blocks as $block) {
        // Verifica se o bloco não está vazio
        if (trim($block) === '') continue;

        // Extrai os dados com regex ou parsing por linha
        preg_match("/owner:\s+(.+)/", $block, $owner);
        preg_match("/ownerid:\s+(.+)/", $block, $ownerid);
        preg_match("/person:\s+\[(.+)\]/", $block, $person); // Pode não capturar corretamente se o formato mudar
        preg_match("/e-mail:\s+(.+)/", $block, $email);
        preg_match("/domain:\s+(.+)/", $block, $domain);
        preg_match("/Telefones encontrados:\s+(.+)/", $block, $phones);
        preg_match("/Emails encontrados:\s+(.+)/", $block, $found_emails);
        preg_match("/google_reference:\s+(.+)/", $block, $google_reference);

        // Define valores padrão para colunas que podem ser nulas
        $clients[] = [
            'owner' => $owner[1] ?? '',  // Define como string vazia se não encontrado
            'ownerid' => $ownerid[1] ?? '',
            'person' => $person[1] ?? '',  // Aqui definimos como vazio se não capturar
            'email' => $email[1] ?? '',
            'domain' => $domain[1] ?? '',
            'telefones' => $phones[1] ?? '',
            'emails' => $found_emails[1] ?? '',
            'google_reference' => $google_reference[1] ?? ''
        ];
    }
    return $clients;
}

// Verifica se o arquivo foi enviado
if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] == 0) {
    $file_tmp = $_FILES['fileUpload']['tmp_name'];

    // Lê o conteúdo do arquivo
    $file_content = file_get_contents($file_tmp);

    // Faz o parsing do conteúdo para extrair os dados dos clientes
    $clients = parse_client_data($file_content);

    // Insere cada cliente no banco de dados
    foreach ($clients as $client) {
        $sql = "INSERT INTO clientes (owner, ownerid, person, email, domain, telefones, emails, google_reference) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt, 
                "ssssssss", 
                $client['owner'], 
                $client['ownerid'], 
                $client['person'], 
                $client['email'], 
                $client['domain'], 
                $client['telefones'], 
                $client['emails'], 
                $client['google_reference']
            );
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                echo "<p id='status-message'>Dados do cliente {$client['owner']} foram inseridos com sucesso.</p>";
            } else {
                echo "<p id='status-message'>Erro ao inserir dados do cliente {$client['owner']}.</p>";
            }

        }
            mysqli_stmt_close($stmt);
        
    }
    
}
mysqli_close($conn);
header("Location: ../dashboard.php#descoberta");
?>
<script>
    // Função para redirecionar após um delay
    function redirectAfterDelay(url, delay) {
        setTimeout(function() {
            window.location.href = url;
        }, delay);
    }

    // Verifica se a mensagem de status existe na página
    var statusMessage = document.getElementById('status-message');
    if (statusMessage) {
        // Define o URL para o redirecionamento e o delay (em milissegundos)
        var redirectUrl = '../dashboard.php#descoberta';
        var delay = 3000; // 3000 milissegundos = 3 segundos

        // Redireciona após o delay
        redirectAfterDelay(redirectUrl, delay);
    }
</script>