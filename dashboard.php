<?php
include_once("includes/head.php");

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

$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM funcionarios WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 0) {
    die("Usuário não encontrado.");
}

$user = $user_result->fetch_assoc();

// Consultas para as seções
$descoberta_query = "SELECT * FROM clientes WHERE funcionario_id IS NULL";
$em_andamento_query = "SELECT * FROM clientes WHERE funcionario_id = ? AND status = 2";
$encerrado_query = "SELECT * FROM clientes WHERE status = 3";

$descoberta_result = $conn->query($descoberta_query);

$stmt = $conn->prepare($em_andamento_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$em_andamento_result = $stmt->get_result();

$encerrado_result = $conn->query($encerrado_query);

if (!$descoberta_result || !$em_andamento_result || !$encerrado_result) {
    die("Erro na consulta: " . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    display: flex;
    min-height: 100vh;
    background-color: #1E1E1E;
    color: #ffffff;
}
a {
    color:white;
    text-decoration:none;
}
/* Estilo da navbar */
.navbar {
    background-color: #1E1E1E;
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: -250px; /* Esconde a navbar fora da tela inicialmente */
    transition: left 0.3s ease;
    overflow: hidden;
    z-index: 10;
}

.navbar.open {
    left: 0; /* Mostra a navbar */
}

.navbar .nav-header {
    padding: 20px;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    background-color: #282828;
}

.navbar ul {
    list-style-type: none;
    padding: 0;
}

.navbar ul li {
    padding: 15px;
    text-align: center;
}

.navbar ul li a {
    color: #ffffff;
    text-decoration: none;
    font-size: 18px;
    display: block;
    transition: background-color 0.3s ease;
}

.navbar ul li a:hover {
    background-color: #007bff;
}

/* Estilo do botão para abrir/fechar a navbar */
.navbar-toggle {
    background-color: #007bff;
    color: white;
    padding: 10px;
    text-align: center;
    cursor: pointer;
    position: fixed;
    top: 20px;
    left: 20px;
    transition: left 0.3s ease;
    z-index: 20; /* Certifica-se de que o botão fique acima da navbar */
    opacity:1%;
}
.navbar-toggle:hover {
    opacity:100%;
}

.navbar.open ~ .navbar-toggle {
    left: 270px; /* Move o botão para a direita quando a navbar estiver aberta */
}

/* Estilo do conteúdo principal */
.main-content {
    margin-left: 20px;
    padding: 20px;
    width: calc(100% - 40px);
    transition: margin-left 0.3s ease, width 0.3s ease;
}

.navbar.open ~ .main-content {
    margin-left: 270px;
    width: calc(100% - 270px);
}

.section {
    display: none;
}

.section.active {
    display: block;
}
/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 100;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow: auto;
}

.modal-content {
    background-color: #282828;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    color: #fff;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

/* Responsividade */
@media (max-width: 768px) {
    .navbar {
        width: 200px;
    }
    .navbar ul li a {
        font-size: 14px;
    }
    .navbar-toggle {
        left: 10px;

    }
    .navbar.open ~ .navbar-toggle {
        left: 210px;
    }
    .main-content {
        margin-left: 10px;
        width: calc(100% - 20px);
    }
    .navbar.open ~ .main-content {
        margin-left: 210px;
        width: calc(100% - 210px);
    }
}
        .client-card {
            border: 1px solid #333;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #282828;
        }
        .form-container {
            background-color: #282828;
            padding: 20px;
            border-radius: 5px;
        }
        .search-box {
            width: 300px;
            height:35px;
            color:#ffff;
            text-align:center;
            background-color:#282828;
        }
        .container-descoberta-lista {
            height:80vh;
            height:500px;
            overflow:auto;
        }
    </style>
</head>
<body>
    <!-- Navbar lateral -->
    <div class="navbar" id="navbar">
        <div class="nav-header">InfoSec</div>
        <ul>
            <li><a href="#home" onclick="changeSection('home')">Home</a></li>
            <li><a href="#descoberta" onclick="changeSection('descoberta')">Descoberta</a></li>
            <li><a href="#em-andamento" onclick="changeSection('em-andamento')">Em Andamento</a></li>
            <li><a href="#encerrado" onclick="changeSection('encerrado')">Encerrado</a></li>
            <li><a href="whatsapp.php" onclick="changeSection('whatsapp')">Whatsapp</a></li>
            <li><a href="#configuracoes" onclick="changeSection('configuracoes')">Configurações</a></li>
            <li><a href="#sobre" onclick="changeSection('sobre')">Quem Somos</a></li>
            <li><a href="includes/logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Botão para abrir/fechar navbar -->
    <div class="navbar-toggle" id="toggleBtn" onclick="toggleNavbar()">☰</div>

    <!-- Conteúdo principal -->
    <div class="main-content" id="mainContent">
        <section id="home" class="section active">
            <h1>Home</h1>
            <p>Bem-vindo ao painel InfoSec, <?php echo htmlspecialchars($user['nome_completo']); ?>!</p>
        </section>

        <section id="descoberta" class="section">
        <br>
    <h1>Descoberta</h1>
    <br>
    <center>
    <input type="text" class="search-box" id="search-box" placeholder="Buscar cliente..." onkeyup="filterResults()"></center>
    <br>
    <div class="container-descoberta-lista">
    <!-- O resto do código continua igual -->
    <?php
    if (mysqli_num_rows($descoberta_result) > 0) {
        while ($row = mysqli_fetch_assoc($descoberta_result)) {
            echo "<div class='client-card'>";
            echo "<p><a onclick='openModal(" . $row['id'] . ")'>" . htmlspecialchars($row['owner']) . "</a></p>";
            echo "</div>";

            // Modal content for each client
            echo "<div id='modal-" . $row['id'] . "' class='modal'>";
            echo "<div class='modal-content'>";
            echo "<span class='close' onclick='closeModal(" . $row['id'] . ")'>&times;</span>";
            echo "<h2>Detalhes do Cliente</h2>";
            echo "<p>Owner: " . htmlspecialchars($row['owner']) . "</p>";
            echo "<p>Owner ID: " . htmlspecialchars($row['ownerid']) . "</p>";
            echo "<p>Person: " . htmlspecialchars($row['person']) . "</p>";
            echo "<p>Email: " . htmlspecialchars($row['email']) . "</p>";
            echo "<p>Domain: <a href='http://" . htmlspecialchars($row['domain']) . "'>" . htmlspecialchars($row['domain']) . "</a></p>";
            echo "<p>Telefones encontrados: " . htmlspecialchars($row['telefones']) . "</p>";
            echo "<p>Emails encontrados: " . htmlspecialchars($row['emails']) . "</p>";
            echo "<p>Google Reference: " . htmlspecialchars($row['google_reference']) . "</p>";
            echo "<form action='includes/update_status.php' method='post'>";
            echo "<input type='hidden' name='cliente_id' value='" . $row['id'] . "'>";
            echo "<label><input type='checkbox' name='status' onchange='this.form.submit()'> Marcar como Em Andamento</label>";
            echo "</form>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p>Nenhuma descoberta encontrada.</p>";
    }
    ?>
    </div>
</section>
<script>
function filterResults() {
    // Pegue o valor do input
    var input = document.getElementById('search-box').value.toUpperCase();
    
    // Pegue todas as divs de clientes
    var clientCards = document.getElementsByClassName('client-card');
    
    // Percorra todos os cards e filtre os que não correspondem à busca
    for (var i = 0; i < clientCards.length; i++) {
        var clientName = clientCards[i].getElementsByTagName("p")[0].textContent || clientCards[i].getElementsByTagName("p")[0].innerText;
        
        // Se o nome do cliente corresponde à busca, exibe o card
        if (clientName.toUpperCase().indexOf(input) > -1) {
            clientCards[i].style.display = "";
        } else {
            clientCards[i].style.display = "none";
        }
    }
}
</script>



<section id="em-andamento" class="section">
<br>
    <h1>Em Andamento</h1>
    <br>
    <div class="container-descoberta-lista">
    <?php
    if (mysqli_num_rows($em_andamento_result) > 0) {
        while ($row = mysqli_fetch_assoc($em_andamento_result)) {
            echo "<div class='client-card'>";
            echo "<p><a onclick='openModal(" . $row['id'] . ")'>" . htmlspecialchars($row['owner']) . "</a></p>";
            echo "</div>";

            // Modal para cada cliente "Em Andamento"
            echo "<div id='modal-" . $row['id'] . "' class='modal'>";
            echo "<div class='modal-content'>";
            echo "<span class='close' onclick='closeModal(" . $row['id'] . ")'>&times;</span>";
            echo "<h2>Detalhes do Cliente</h2>";
            echo "<p>Owner: " . htmlspecialchars($row['owner']) . "</p>";
            echo "<p>Owner ID: " . htmlspecialchars($row['ownerid']) . "</p>";
            echo "<p>Person: " . htmlspecialchars($row['person']) . "</p>";
            echo "<p>Email: " . htmlspecialchars($row['email']) . "</p>";
            echo "<p>Domain: " . htmlspecialchars($row['domain']) . "</p>";
            echo "<p>Telefones encontrados: " . htmlspecialchars($row['telefones']) . "</p>";
            echo "<p>Emails encontrados: " . htmlspecialchars($row['emails']) . "</p>";
            echo "<p>Google Reference: <a href='" . htmlspecialchars($row['google_reference']) . "'>Link</a></p>";

            // Form para marcar como Encerrado
            echo "<form action='includes/final_status.php' method='post'>";
            echo "<input type='hidden' name='cliente_id' value='" . $row['id'] . "'>";
            echo "<label><input type='checkbox' name='status_encerrado' onchange='this.form.submit()'> Marcar como Encerrado</label>";
            echo "</form>";

            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p>Nenhuma tarefa em andamento encontrada.</p>";
    }
    ?>
    </div>
</section>


<!-- Modal para exibir informações detalhadas -->
<!-- Modal para exibir informações detalhadas -->
<div id="clientModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalOwner"></h2>
        <p id="modalOwnerID"></p>
        <p id="modalPerson"></p>
        <p id="modalEmail"></p>
        <p id="modalDomain"></p>
        <p id="modalTelefones"></p>
        <p id="modalEmails"></p>
        <p id="modalGoogleReference"></p>
        
        <!-- Checkboxes para marcar como em andamento ou encerrado -->
        <form id="statusForm" action="update_status.php" method="post">
            <input type="hidden" id="clientId" name="cliente_id">
            <label for="statusAndamento">Marcar como em andamento</label>
            <input type="checkbox" id="statusAndamento" name="status_andamento" onchange="submitStatusForm()">
            <br>
            <label for="statusEncerrado">Marcar como encerrado</label>
            <input type="checkbox" id="statusEncerrado" name="status_encerrado" onchange="submitStatusForm()">
        </form>
    </div>
</div>

<script>
function openModal(clientId, owner, ownerID, person, email, domain, telefones, emails, googleReference) {
    // Preenche os detalhes do modal
    document.getElementById('modalOwner').innerText = "Owner: " + owner;
    document.getElementById('modalOwnerID').innerText = "Owner ID: " + ownerID;
    document.getElementById('modalPerson').innerText = "Person: " + person;
    document.getElementById('modalEmail').innerText = "Email: " + email;
    document.getElementById('modalDomain').innerText = "Domain: " + domain;
    document.getElementById('modalTelefones').innerText = "Telefones encontrados: " + telefones;
    document.getElementById('modalEmails').innerText = "Emails encontrados: " + emails;
    document.getElementById('modalGoogleReference').innerText = "Google Reference: " + googleReference;

    // Define o ID do cliente no formulário
    document.getElementById('clientId').value = clientId;

    // Exibe o modal
    document.getElementById('clientModal').style.display = "block";
}

function closeModal() {
    document.getElementById('clientModal').style.display = "none";
}

function submitStatusForm() {
    document.getElementById('statusForm').submit();
}
</script>


        <section id="encerrado" class="section">
        <br>
            <h1>Encerrado</h1>
            <br>
            <div class="container-descoberta-lista">
            <?php
            if (mysqli_num_rows($encerrado_result) > 0) {
                while ($row = mysqli_fetch_assoc($encerrado_result)) {
                    echo "<div class='client-card'>";
                    echo "<p>Owner: " . htmlspecialchars($row['owner']) . "</p>";
                    echo "<p>Owner ID: " . htmlspecialchars($row['ownerid']) . "</p>";
                    echo "<p>Person: " . htmlspecialchars($row['person']) . "</p>";
                    echo "<p>Email: " . htmlspecialchars($row['email']) . "</p>";
                    echo "<p>Domain: " . htmlspecialchars($row['domain']) . "</p>";
                    echo "<p>Telefones encontrados: " . htmlspecialchars($row['telefones']) . "</p>";
                    echo "<p>Emails encontrados: " . htmlspecialchars($row['emails']) . "</p>";
                    echo "<p>Google Reference: <a href='" . htmlspecialchars($row['google_reference']) . "'>Link</a></p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Nenhuma tarefa encerrada encontrada.</p>";
            }
            ?>
            </div>
        </section>
        <section id="whatsapp" class="section">
        <br>
            <h1>Whatsapp Integration - Redirecionando...</h1>
            <br>
            <div class="form-container">
                <h2>whatsapp</h2>
                <br>
                
                <p>Desenvolvido por Gabriel Machado</p>
            </div>
        </section>
        <section id="configuracoes" class="section">
        <br>
            <h1>Configurações</h1>
            <br>
            <div class="form-container">
                <h2>Upload de Arquivos</h2>
                <br>
                <form action="includes/upload.php" method="post" enctype="multipart/form-data">
                    <label for="fileUpload">Escolha um arquivo:</label>
                    <input type="file" id="fileUpload" name="fileUpload" required>
                    <input type="submit" value="Enviar">
                </form>
            </div>
        </section>
        <section id="sobre" class="section">
        <br>
    <h1>Quem Somos</h1>
    <br>
    <div class="container-descoberta-lista">
    <div class="form-container">
        <center>
            <h1>SECURITYNET BRASIL</h1>
            <h3>Gabriel Moura Machado</h3>
        </center>

        <div class="servicos">
            <h2>Serviços</h2>
            <ul>
                <li><strong>Identidade Visual:</strong> Criação e desenvolvimento de logotipos e identidade de marca.</li>
                <li><strong>Marketing Digital:</strong> Estratégias de marketing focadas em SEO, mídias sociais e publicidade online.</li>
                <li><strong>Análise de Segurança:</strong> Avaliação de vulnerabilidades e implementação de medidas de proteção.</li>
                <li><strong>Soluções em Tecnologia:</strong> Consultoria e implementação de soluções tecnológicas sob medida.</li>
                <li><strong>Desenvolvimento de Aplicativos:</strong> Criação de aplicativos personalizados para diversas plataformas.</li>
            </ul>
        </div>

        
<br><hr>

        <div class="plano-captura">
            <h2>Plano de Captura de Clientes</h2>
            <p>
                Nosso plano utiliza um sistema automatizado que realiza pesquisas avançadas via Google Dorking para identificar sites e servidores vulneráveis. As vulnerabilidades incluem SQL Injection (SQLi), Cross-Site Scripting (XSS), Remote Code Execution (RCE), Local File Inclusion (LFI), Remote File Inclusion (RFI) e Insecure Direct Object Reference (IDOR).
            </p>
            <p>
                O bot coleta informações via WHOIS e web scraping, gerando um relatório detalhado para os representantes comerciais, que utilizam essas informações para contatar potenciais clientes.
            </p>
        </div>

        
<br><hr>

        <div class="roteiro-abordagem">
            <h2>Roteiro de Abordagem</h2>
            <a href="roteiro.html"><p>Olá, eu sou o <strong>$REPRESENTANTE</strong>, representante comercial da SecurityNet Brasil...</p></a>
        </div>

        
<br><hr>

        <div class="colaboradores">
            <h2>Colaboradores e Funções</h2>
            <ul>
                <li><strong>Gabriel Moura:</strong> Administrador Geral - Analista de Segurança e Desenvolvedor Back End</li>
                <li><strong>Itallo Fernando:</strong> DBA - Dev suporte</li>
                <li><strong>Thomas:</strong> Representante Comercial / Vendedor</li>
                <li><strong>William Vasconcellos:</strong> Gestor de Mídias Sociais</li>
            </ul>
        </div>

        
<br><hr>
        <div class="objetivo">
            <h2>Objetivo</h2>
            <p>
                Nosso principal objetivo é construir uma base sólida de empresas-clientes que contribuam com uma mensalidade, visando o crescimento contínuo dessa lista. Isso garantirá uma receita estável e recorrente, proporcionando uma renda mensal consistente para todos os colaboradores.
            </p>
        </div>
    </div>

        </div>
</section>

    </div>

    <script>
        // Função para ativar a seção com base no fragmento da URL
        function activateSectionFromHash() {
        // Obtém o fragmento da URL (por exemplo, "#em-andamento")
        const hash = window.location.hash;

        // Remove a classe 'active' de todas as seções
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => {
            section.classList.remove('active');
        });

        // Adiciona a classe 'active' à seção com o ID correspondente ao fragmento
        if (hash) {
            const targetSection = document.querySelector(hash);
            if (targetSection) {
                targetSection.classList.add('active');
            }
        } else {
            // Se não houver fragmento, ativa a seção padrão (pode ser a 'home')
            document.getElementById('home').classList.add('active');
        }
    }

    // Ativa a seção correta quando a página é carregada
    window.addEventListener('load', activateSectionFromHash);

    // Ativa a seção correta quando o fragmento da URL muda (caso o usuário use navegação no navegador)
    window.addEventListener('hashchange', activateSectionFromHash);

    // Função para abrir/fechar navbar
    function toggleNavbar() {
        const navbar = document.getElementById('navbar');
        navbar.classList.toggle('open');
    }

    // Função para mudar de seção sem recarregar a página
    function changeSection(sectionId) {
        // Esconder todas as seções
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
        });

        // Mostrar a seção clicada
        document.getElementById(sectionId).classList.add('active');
    }
    // Função para abrir o modal
function openModal(clientId) {
    document.getElementById('modal-' + clientId).style.display = "block";
}

// Função para fechar o modal
function closeModal(clientId) {
    document.getElementById('modal-' + clientId).style.display = "none";
}

    </script>
</body>
</html>
