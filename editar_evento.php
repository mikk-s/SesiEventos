<?php
session_start();
require_once 'conexao.php';

// PERMISSÃO: Apenas Administrador ou Organizador
if (!isset($_SESSION['perm']) || !in_array($_SESSION['perm'], ['Administrador', 'Organizador'])) {
    $_SESSION['erro'] = "Acesso negado."; header("Location: index.php"); exit();
}

$$id_evento = $_GET['id'] ?? null;
if (!$id_evento) { header("Location: gerenciar_eventos.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome']; 
    $data = $_POST['data']; 
    $local = $_POST['local'];
    $max_pessoas = $_POST['max_pessoas']; 
    // NOVO CAMPO
    $limite_por_usuario = $_POST['limite_por_usuario'];
    $origem = $_POST['origem']; 
    $descricao = $_POST['descricao_completa'];
    $imagem_antiga = $_POST['imagem_antiga'];

    // Lógica de Upload de Nova Imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $target_dir = "img/eventos/";
        $image_name = uniqid() . basename($_FILES["imagem"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
            $imagem_path = $target_file; // Atualiza para a nova imagem
            // Opcional: deletar a imagem antiga do servidor
            if ($imagem_antiga && file_exists($imagem_antiga)) {
                unlink($imagem_antiga);
            }
        }
    }

   
    try {
        // ATUALIZAÇÃO DA QUERY SQL
        $sql = "UPDATE eventos SET nome = ?, data = ?, local = ?, max_pessoas = ?, limite_por_usuario = ?, origem = ?, descricao_completa = ?, imagem = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        // BIND DO NOVO PARÂMETRO
        $stmt->execute([$nome, $data, $local, $max_pessoas, $limite_por_usuario, $origem, $descricao, $imagem_path, $id_evento]);
        $_SESSION['mensagem'] = "Evento atualizado com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao atualizar evento: " . $e->getMessage();
    }
    header("Location: gerenciar_eventos.php");
    exit();
}
// Busca dados do evento e locais para preencher o form
$evento_stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
$evento_stmt->execute([$id_evento]);
$evento = $evento_stmt->fetch();
$locais = $conn->query("SELECT sala, bloco FROM locais ORDER BY bloco, sala")->fetchAll();


include_once("templates/header.php");
?><link rel="stylesheet" href="css/style.css">
<main class="form-container">
    <div class="form-card" style="max-width: 900px; display: flex; flex-wrap: wrap; gap: 2rem;">
        <div style="flex: 1; min-width: 300px;">
            <h2>Editar Evento</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="imagem_antiga" value="<?= htmlspecialchars($evento['imagem'] ?? '') ?>">

                <label for="nome">Nome do Evento:</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($evento['nome']) ?>" required onkeyup="updatePreview()">
                
                <label for="imagem">Nova Imagem (deixe em branco para manter a atual):</label>
                <input type="file" id="imagem" name="imagem" accept="image/*" onchange="updatePreviewImage(this)">

                <label for="data">Data e Hora:</label>
                <input type="datetime-local" id="data" name="data" value="<?= (new DateTime($evento['data']))->format('Y-m-d\TH:i') ?>" required onchange="updatePreview()">
                
                <label for="local">Local:</label>
                <select id="local" name="local" required onchange="updatePreview()">
                    <?php foreach ($locais as $local_item): 
                        $valor_opcao = htmlspecialchars($local_item['sala']) . ' - Bloco ' . htmlspecialchars($local_item['bloco']);
                    ?>
                        <option value="<?= $valor_opcao ?>" <?= ($evento['local'] == $valor_opcao) ? 'selected' : '' ?>><?= $valor_opcao ?></option>
                    <?php endforeach; ?>
                </select>

                <div style="display: flex; gap: 1rem;">
                    <div style="flex: 1;">
                        <label for="max_pessoas">Lotação Máxima:</label>
                        <input type="number" id="max_pessoas" name="max_pessoas" value="<?= htmlspecialchars($evento['max_pessoas']) ?>" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="limite_por_usuario">Limite por Usuário:</label>
                        <input type="number" id="limite_por_usuario" name="limite_por_usuario" value="<?= htmlspecialchars($evento['limite_por_usuario']) ?>" required>
                    </div>
                </div>

                <label for="origem">Origem:</label>
                <select id="origem" name="origem" required onchange="updatePreview()">
                    <option value="SESI" <?= ($evento['origem'] == 'SESI') ? 'selected' : '' ?>>SESI</option>
                    <option value="SENAI" <?= ($evento['origem'] == 'SENAI') ? 'selected' : '' ?>>SENAI</option>
                </select>
                
                <label for="descricao_completa">Descrição Completa:</label>
                <textarea id="descricao_completa" name="descricao_completa" rows="4"><?= htmlspecialchars($evento['descricao_completa']) ?></textarea>
                
                <button type="submit" class="submit-button">Salvar Alterações</button>
                <a href="gerenciar_eventos.php" style="text-align: center; display: block; margin-top: 1rem;">Cancelar</a>
            </form>
        </div>
        <div style="flex: 1; min-width: 300px;">
            <h3>Pré-visualização do Card</h3>
            <div id="event-card-preview" class="event-card card">
                <img id="preview-image" src="<?= $evento['imagem'] ? $BASE_URL . $evento['imagem'] : $BASE_URL . 'img/placeholder.jpg' ?>" alt="Imagem do Evento" class="event-card-image">
                <div class="event-card-content">
                    <h3 id="preview-nome"></h3>
                    <p class="event-info"><strong>Origem:</strong> <span id="preview-origem"></span></p>
                    <p class="event-info"><strong>Data:</strong> <span id="preview-data"></span></p>
                    <p class="event-info"><strong>Local:</strong> <span id="preview-local"></span></p>
                    <a href="#" class="btn-primary btn-details" onclick="return false;">Saiba Mais</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Script de preview (idêntico ao de cadastrar_evento.php)
function updatePreview() {
    document.getElementById('preview-nome').innerText = document.getElementById('nome').value || 'Nome do Evento';
    const origemSelect = document.getElementById('origem');
    document.getElementById('preview-origem').innerText = origemSelect.options[origemSelect.selectedIndex].text;
    const localSelect = document.getElementById('local');
    document.getElementById('preview-local').innerText = localSelect.options[localSelect.selectedIndex].text;
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
        };
        reader.readAsDataURL(input.files[0]);
    }
}
// Chamar a função uma vez no início para popular o preview com os dados existentes
document.addEventListener('DOMContentLoaded', updatePreview);
</script>

<?php include_once("templates/footer.php"); ?>