<?php
session_start();
require "conexao.php";

// 1. VERIFICAÇÃO DE LOGIN E PERMISSÃO
if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['perm']) && $_SESSION['perm'] == "Visitante") {
    $_SESSION['erro'] = "Acesso negado. Você não tem permissão para acessar esta página.";
    header("Location: index.php");
    exit();
}

// BUSCA OS LOCAIS DISPONÍVEIS NO BANCO
try {
    $locais_stmt = $conn->query("SELECT * FROM locais ORDER BY bloco, sala");
    $locais = $locais_stmt->fetchAll();
} catch (PDOException $e) {
    $locais = [];
    $_SESSION['mensagem'] = "Erro ao carregar os locais: " . $e->getMessage();
}

// Processa o formulário quando ele é enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'] ?? null;
    $data = $_POST['data'] ?? null;
    $local = $_POST['local'] ?? null;
    $max_pessoas = $_POST['max_pessoas'] ?? null;
    // NOVO CAMPO
    $limite_por_usuario = $_POST['limite_por_usuario'] ?? null;
    $origem = $_POST['origem'] ?? null;
    $descricao_completa = $_POST['descricao_completa'] ?? null;
    $organizador = $_SESSION['usuario'];

    // Lógica de Upload de Imagem
    $imagem_path = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $target_dir = "img/eventos/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = uniqid() . basename($_FILES["imagem"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
            $imagem_path = $target_file;
        }
    }

    if (empty($nome) || empty($data) || empty($local) || empty($max_pessoas) || empty($limite_por_usuario) || empty($origem)) {
        $_SESSION['mensagem'] = "Erro: Por favor, preencha todos os campos obrigatórios.";
    } else {
        try {
            // ATUALIZAÇÃO DA QUERY SQL
            $sql = "INSERT INTO eventos (nome, data, local, max_pessoas, limite_por_usuario, origem, descricao_completa, organizador, imagem) 
                    VALUES (:nome, :data, :local, :max_pessoas, :limite_por_usuario, :origem, :descricao_completa, :organizador, :imagem)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":nome", $nome);
            $stmt->bindParam(":data", $data);
            $stmt->bindParam(":local", $local);
            $stmt->bindParam(":max_pessoas", $max_pessoas, PDO::PARAM_INT);
            // BIND DO NOVO PARÂMETRO
            $stmt->bindParam(":limite_por_usuario", $limite_por_usuario, PDO::PARAM_INT);
            $stmt->bindParam(":origem", $origem);
            $stmt->bindParam(":descricao_completa", $descricao_completa);
            $stmt->bindParam(":organizador", $organizador);
            $stmt->bindParam(":imagem", $imagem_path);

            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Evento cadastrado com sucesso!";
            } else {
                $_SESSION['mensagem'] = "Erro ao cadastrar o evento.";
            }

        } catch (PDOException $e) {
            $_SESSION['mensagem'] = "Erro no banco de dados: " . $e->getMessage();
        }
    }
    
    header("Location: cadastrar_evento.php");
    exit();
}

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">

<main class="form-container">
    <div class="form-card" style="max-width: 900px; display: flex; flex-wrap: wrap; gap: 2rem;">
        <div style="flex: 1; min-width: 300px;">
            <h2>Cadastrar Novo Evento</h2>
            <form method="POST" enctype="multipart/form-data">
                
                <label for="nome">Nome do Evento:</label>
                <input type="text" id="nome" name="nome" required onkeyup="updatePreview()">
                
                <label for="imagem">Imagem do Evento:</label>
                <input type="file" id="imagem" name="imagem" accept="image/*" onchange="updatePreviewImage(this)">

                <label for="data">Data e Hora:</label>
                <input type="datetime-local" id="data" name="data" required onchange="updatePreview()">
                
                <label for="local">Local:</label>
                <select id="local" name="local" required onchange="updatePreview()">
                    <option value="">-- Selecione um local --</option>
                    <?php if (count($locais) > 0): ?>
                        <?php foreach ($locais as $local_item): ?>
                            <?php 
                                $valor_opcao = htmlspecialchars($local_item['sala']) . ' - Bloco ' . htmlspecialchars($local_item['bloco']);
                            ?>
                            <option value="<?= $valor_opcao ?>"><?= $valor_opcao ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Nenhum local cadastrado</option>
                    <?php endif; ?>
                </select>

                <div style="display: flex; gap: 1rem;">
                    <div style="flex: 1;">
                        <label for="max_pessoas">Lotação Máxima:</label>
                        <input type="number" id="max_pessoas" name="max_pessoas" min="1" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="limite_por_usuario">Limite por Usuário:</label>
                        <input type="number" id="limite_por_usuario" name="limite_por_usuario" min="1" value="10" required>
                    </div>
                </div>

                <label for="origem">Origem:</label>
                <select id="origem" name="origem" required onchange="updatePreview()">
                    <option value="">-- Selecione a origem --</option>
                    <option value="SESI">SESI</option>
                    <option value="SENAI">SENAI</option>
                </select>
                
                <label for="descricao_completa">Descrição Completa:</label>
                <textarea id="descricao_completa" name="descricao_completa" rows="4"></textarea>

                <button type="submit" class="submit-button">Cadastrar Evento</button>
            </form>
        </div>
        <div style="flex: 1; min-width: 300px;">
            <h3>Pré-visualização do Card</h3>
            <div id="event-card-preview" class="event-card card">
                <img id="preview-image" src="img/placeholder.jpg" alt="Imagem do Evento" class="event-card-image">
                <div class="event-card-content">
                    <h3 id="preview-nome">Nome do Evento</h3>
                    <p class="event-info"><strong>Origem:</strong> <span id="preview-origem">Origem</span></p>
                    <p class="event-info"><strong>Data:</strong> <span id="preview-data">DD/MM/AAAA, HH:MM</span></p>
                    <p class="event-info"><strong>Local:</strong> <span id="preview-local">Local do Evento</span></p>
                    <a href="#" class="btn-primary btn-details" onclick="return false;">Saiba Mais</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function updatePreview() {
    // Atualiza o Nome
    document.getElementById('preview-nome').innerText = document.getElementById('nome').value || 'Nome do Evento';

    // Atualiza a Origem
    const origemSelect = document.getElementById('origem');
    const origemValue = origemSelect.options[origemSelect.selectedIndex].text;
    document.getElementById('preview-origem').innerText = origemValue.startsWith('--') ? 'Origem' : origemValue;

    // Atualiza o Local
    const localSelect = document.getElementById('local');
    const localValue = localSelect.options[localSelect.selectedIndex].text;
    document.getElementById('preview-local').innerText = localValue.startsWith('--') ? 'Local do Evento' : localValue;

    // Formata e Atualiza a Data
    const dataValue = document.getElementById('data').value;
    if (dataValue) {
        const date = new Date(dataValue);
        const formattedDate = date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ', ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('preview-data').innerText = formattedDate;
    } else {
        document.getElementById('preview-data').innerText = 'DD/MM/AAAA, HH:MM';
    }
}

function updatePreviewImage(input) {
    const previewImage = document.getElementById('preview-image');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            previewImage.setAttribute('src', e.target.result);
            previewImage.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        previewImage.setAttribute('src', 'img/placeholder.jpg'); // Volta para o placeholder se nenhuma imagem for selecionada
    }
}

// Chamar a função uma vez no início para garantir que o preview esteja sincronizado
document.addEventListener('DOMContentLoaded', updatePreview);
</script>

<?php
if (isset($_SESSION['mensagem']) || isset($_SESSION['erro'])) {
    $mensagem = $_SESSION['mensagem'] ?? $_SESSION['erro'];
    echo "<script>alert('" . addslashes($mensagem) . "');</script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['erro']);
}
?>

</body>
</html>