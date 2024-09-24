<?php

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Conectar ao banco de dados principal
$servername = "sql209.infinityfree.com";
$username = "if0_37357353";
$password = "AB2x17BoH5GZxQ";
$dbname = "if0_37357353_chat_db"; // Nome do banco de dados principal

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão com chat_db: " . $conn->connect_error);
}

// Conectar ao banco de dados secundário
$servername2 = "sql209.infinityfree.com";
$username2 = "if0_37357353";
$password2 = "AB2x17BoH5GZxQ";
$dbname2 = "if0_37357353_infosec"; // Nome do banco de dados secundário

$conn2 = new mysqli($servername2, $username2, $password2, $dbname2);

// Verificar a conexão
if ($conn2->connect_error) {
    die("Falha na conexão com infosec: " . $conn2->connect_error);
}

// Função para buscar todos os contatos
function getContacts($conn) {
    $sql = "SELECT DISTINCT contact_name, phone_number FROM messages";
    $result = $conn->query($sql);
    return $result;
}

// Função para buscar as mensagens de um contato específico
if (isset($_GET['contact'])) {
    $contact = $_GET['contact'];
    $stmt = $conn->prepare("SELECT * FROM messages WHERE contact_name = ?");
    $stmt->bind_param("s", $contact);
    $stmt->execute();
    $messages = $stmt->get_result();
    $message_list = [];
    while ($row = $messages->fetch_assoc()) {
        $message_list[] = $row;
    }
    echo json_encode($message_list);
    exit;
}

// Função para buscar o nome completo do usuário
function getFullName($conn2, $user_id) {
    $stmt = $conn2->prepare("SELECT nome_completo FROM funcionarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['nome_completo'];
}

// Função para enviar uma mensagem
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contact_name = $_POST['contact_name'];
    $phone_number = $_POST['phone_number'];
    $message = $_POST['message'];
    $direction = "sent";  // Direção sempre será 'sent' para este script
    $user_id = $_SESSION['user_id'];
    $full_name = getFullName($conn2, $user_id);
    $nomes = explode(" ", $full_name);
    $msg = '*'.$nomes[0].' Infosec*: '.$message;
    $stmt = $conn->prepare("INSERT INTO messages (contact_name, phone_number, message, direction) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $contact_name, $phone_number, $msg, $direction);

    if ($stmt->execute()) {
        echo "Mensagem enviada com sucesso!";
    } else {
        echo "Erro ao enviar mensagem!";
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link data-default-icon="assets/whats_icon.png" rel="shortcut icon" href="https://static.whatsapp.net/rsrc.php/v3/yP/r/rYZqPCBaG70.png" />
    <title>WhatsApp Secnet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background-color: #f0f0f0;
        }

        /* Estilo para a barra lateral */
        .sidebar {
            width: 250px;
            background-color: #075E54;
            color: white;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .contact {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #128C7E;
            cursor: pointer;
            border-radius: 5px;
        }

        .contact:hover {
            background-color: #25D366;
        }

        /* Estilo da área do chat */
        .chat-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .chat-area h3 {
            margin: 0;
            margin-bottom: 10px;
        }

        .messages {
            flex-grow: 1;
            background-color: white;
            border-radius: 10px;
            padding: 10px;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 10px;
        }

        .message.sent {
            text-align: right;
        }

        .message.received {
            text-align: left;
        }

        .input-area {
            display: flex;
            margin-top: 10px;
        }

        .input-area input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .input-area button {
            padding: 10px 15px;
            background-color: #075E54;
            color: white;
            border: none;
            border-radius: 5px;
            margin-left: 10px;
        }
        a {
            text-decoration:none;
            color:#ffff;
        }
        .contact-list {
            height:400px;
            overflow:auto;
        }
        .buttons-list {
            display:grid;

        }
        .buttons-list button {
    background-color: #075E54; /* Cor de fundo */
    border: none; /* Remove a borda padrão */
    border-radius: 30px; /* Bordas arredondadas */
    color: #fff; /* Cor do texto */
    width: calc(fit-content + 30px); /* Largura ajustada */
    height: 40px; /* Altura dos botões */
    margin-top: 10px; /* Margem superior */
    padding: 0 20px; /* Espaçamento interno horizontal */
    font-size: 16px; /* Tamanho da fonte */
    font-weight: bold; /* Peso da fonte */
    cursor: pointer; /* Cursor de ponteiro ao passar o mouse */
    text-align: center; /* Centraliza o texto */
    line-height: 40px; /* Alinha o texto verticalmente */
    transition: background-color 0.3s, transform 0.2s; /* Transição suave para cor e efeito de transformação */
}

.buttons-list button:hover {
    background-color: #128C7E; /* Cor de fundo ao passar o mouse */
    transform: scale(1.05); /* Aumenta ligeiramente o botão */
}

.buttons-list button:active {
    background-color: #0d4f40; /* Cor de fundo quando clicado */
    transform: scale(0.95); /* Reduz ligeiramente o botão ao clicar */
}

.buttons-list button:focus {
    outline: none; /* Remove o contorno padrão do foco */
    box-shadow: 0 0 0 3px rgba(0, 150, 136, 0.5); /* Adiciona uma sombra ao redor do botão quando em foco */
}
/* modal 1   */
       /* Botão para abrir o modal */


        /* O modal (inicialmente escondido) */
        #modal1 {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        /* Conteúdo do modal */
        #modal1Content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            text-align: center;
            color:#4CAF50;
        }

        /* Botão de fechar */
        .close1 {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close1:hover,
        .close1:focus {
            color: black;
        }

        /* Estilo para o input e textarea */
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }

        /* Botão de enviar */
        #sendBtnmodal {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        #sendBtnmodal:disabled {
            background-color: grey;
        }

        /* fim modal 1  */
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php#descoberta"> <-- </a>
        <h2>Contatos</h2>
        <div class="contact-list">
        <script>
function updateContactList() {
    fetch('includes/get_contacts.php')
        .then(response => response.json())
        .then(data => {
            const contactListDiv = document.querySelector('.contact-list');
            contactListDiv.innerHTML = ''; // Limpar lista anterior

            if (data.length > 0) {
                data.forEach(contact => {
                    const contactDiv = document.createElement('div');
                    contactDiv.classList.add('contact');
                    contactDiv.setAttribute('data-contact-name', contact.contact_name);
                    contactDiv.setAttribute('data-phone-number', contact.phone_number);
                    contactDiv.innerText = contact.contact_name;
                    contactDiv.addEventListener('click', () => {
                        // Função para lidar com o clique em um contato
                        selectedContact = contact.contact_name;
                        selectedPhoneNumber = contact.phone_number;
                        loadMessages(selectedContact);
                    });
                    contactListDiv.appendChild(contactDiv);
                });
            } else {
                contactListDiv.innerHTML = '<p>Nenhum contato encontrado.</p>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
        });
}

// Atualizar lista de contatos a cada 3 segundos (3000 milissegundos)
setInterval(updateContactList, 3000);

// Carregar contatos inicialmente
updateContactList();
</script>

        </div>
        <div class="buttons-list">
            <button id="erase_data">Clean</button>
            <button id="newCallBtn">New Call</button>
               <!-- O modal -->
    <div id="modal1">
        <div id="modal1Content">
            <span class="close1">&times;</span>
            <h2>Enviar Mensagem</h2>
            <input type="text" id="phone_number" placeholder="Número do contato">
            <textarea id="message" placeholder="Digite sua mensagem aqui"></textarea>
            <button id="sendBtnmodal">Enviar</button>
        </div>
    </div>
    <script>
        // Variáveis de referência aos elementos
        const modal = document.getElementById("modal1");
        const newCallBtn = document.getElementById("newCallBtn");
        const closeModalBtn = document.getElementsByClassName("close1")[0];
        const sendBtn = document.getElementById("sendBtnmodal");

        // Abre o modal quando o botão "New Call" for clicado
        newCallBtn.onclick = function() {
            modal.style.display = "block";
        }

        // Fecha o modal quando o botão de fechar for clicado
        closeModalBtn.onclick = function() {
            modal.style.display = "none";
        }

        // Fecha o modal ao clicar fora do conteúdo
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Função para enviar a mensagem via API
        sendBtn.onclick = function() {
            const phoneNumber = document.getElementById("phone_number").value;
            const message = document.getElementById("message").value;

            if (!phoneNumber || !message) {
                alert("Por favor, preencha todos os campos.");
                return;
            }

            // Desabilita o botão para evitar múltiplos cliques
            sendBtn.disabled = true;

            // Realiza a requisição com Fetch API
            fetch(`api.php?key=batata&phone_number=${encodeURIComponent(phoneNumber)}&message=${encodeURIComponent(message)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Mensagem enviada com sucesso!");
                    } else {
                        alert("Erro ao enviar a mensagem: " + data.message);
                    }
                    // Reabilita o botão
                    sendBtn.disabled = false;
                    // Fecha o modal
                    modal.style.display = "none";
                })
                .catch(error => {
                    alert("Erro na requisição: " + error);
                    sendBtn.disabled = false;
                });
        }
    </script>
            <button id="multi_call"><a href="multi_stream.php">Multi Call</a></button>
        </div>
    </div>

    <div class="chat-area">
        <h3>Chat</h3>
        <div class="messages" id="messages">
            <p>Selecione um contato para ver as mensagens.</p>
        </div>

        <div class="input-area">
            <input type="text" id="messageInput" placeholder="Digite sua mensagem">
            <button id="sendButton">Enviar</button>
        </div>
    </div>

    <script>
        let selectedContact = null;
        let selectedPhoneNumber = null;

        // Adicionar evento de clique para os contatos
        document.querySelectorAll('.contact').forEach(contact => {
            contact.addEventListener('click', () => {
                selectedContact = contact.getAttribute('data-contact-name');
                selectedPhoneNumber = contact.getAttribute('data-phone-number');
                loadMessages(selectedContact);
            });
        });

        // Carregar as mensagens de um contato
        function loadMessages(contactName) {
            fetch(`whatsapp.php?contact=${contactName}`)
                .then(response => response.json())
                .then(data => {
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = '';

                    data.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message', message.direction);
                        messageDiv.innerText = message.message;
                        messagesDiv.appendChild(messageDiv);
                    });
                });
        }

        // apagar registros
        document.getElementById('erase_data').addEventListener('click', () => {
    if (confirm("Você tem certeza que deseja deletar todas as mensagens?")) {
        fetch('includes/delete_messages.php', {
            method: 'POST'
        })
        .then(response => response.text())
        .then(result => {
            alert(result);
            // Opcional: Atualizar a interface do usuário ou redirecionar para uma página de sucesso
        })
        .catch(error => {
            console.error('Erro:', error);
        });
    }
});
        // Enviar uma mensagem
        document.getElementById('sendButton').addEventListener('click', () => {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value;

            if (message && selectedContact) {
                const formData = new FormData();
                formData.append('contact_name', selectedContact);
                formData.append('phone_number', selectedPhoneNumber);
                formData.append('message', message);

                fetch('whatsapp.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    loadMessages(selectedContact);
                    messageInput.value = '';
                });
            }
        });

        // Função para atualizar as mensagens periodicamente
        function refreshMessages() {
            if (selectedContact) {
                loadMessages(selectedContact);
            }
        }

        // Atualiza as mensagens a cada 5 segundos
        setInterval(refreshMessages, 5000);
    </script>
</body>
</html>
