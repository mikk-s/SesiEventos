<?php
session_start();
require_once 'conexao.php';

// PERMISSÃO: Apenas Administrador ou Organizador
if (!isset($_SESSION['perm']) || !in_array($_SESSION['perm'], ['Administrador', 'Organizador'])) {
    $_SESSION['erro'] = "Acesso negado."; header("Location: index.php"); exit();
}

$id_evento = $_GET['id'] ?? null;
if (!$id_evento) { header("Location: gerenciar_eventos.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta dos dados
    $nome = $_POST['nome']; $data = $_POST['data']; $local = $_POST['local'];
    $max_pessoas = $_POST['max_pessoas']; $origem = $_POST['origem']; $descricao = $_POST['descricao_completa'];
    try {
        $sql = "UPDATE eventos SET nome = ?, data = ?, local = ?, max_pessoas = ?, origem = ?, descricao_completa = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nome, $data, $local, $max_pessoas, $origem, $descricao, $id_evento]);
        $_SESSION['mensagem'] = "Evento atualizado com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao atualizar evento: " . $e->getMessage();
    }
    header("Location: gerenciar_eventos.php");
    exit();
}

// Busca dados do evento e locais para preencher o form
$evento = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
$evento->execute([$id_evento]);
$evento = $evento->fetch();
$locais = $conn->query("SELECT sala, bloco FROM locais ORDER BY bloco, sala")->fetchAll();

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<main class="form-container">
    <div class="form-card">
        <h2>Editar Evento</h2>
        <form method="POST">
            <label for="nome">Nome do Evento:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($evento['nome']) ?>" required>
            <label for="data">Data e Hora:</label>
            <input type="datetime-local" name="data" value="<?= (new DateTime($evento['data']))->format('Y-m-d\TH:i') ?>" required>
            <label for="local">Local:</label>
            <select name="local" required>
                <?php foreach ($locais as $local_item): 
                    $valor_opcao = htmlspecialchars($local_item['sala']) . ' - Bloco ' . htmlspecialchars($local_item['bloco']);
                ?>
                    <option value="<?= $valor_opcao ?>" <?= ($evento['local'] == $valor_opcao) ? 'selected' : '' ?>><?= $valor_opcao ?></option>
                <?php endforeach; ?>
            </select>
            <label for="max_pessoas">Lotação Máxima:</label>
            <input type="number" name="max_pessoas" value="<?= htmlspecialchars($evento['max_pessoas']) ?>" required>
            <label for="origem">Origem:</label>
            <select name="origem" required>
                <option value="SESI" <?= ($evento['origem'] == 'SESI') ? 'selected' : '' ?>>SESI</option>
                <option value="SENAI" <?= ($evento['origem'] == 'SENAI') ? 'selected' : '' ?>>SENAI</option>
            </select>
            <label for="descricao_completa">Descrição Completa:</label>
            <textarea name="descricao_completa" rows="4"><?= htmlspecialchars($evento['descricao_completa']) ?></textarea>
            <button type="submit" class="submit-button">Salvar Alterações</button>
            <a href="gerenciar_eventos.php" style="text-align: center; display: block; margin-top: 1rem;">Cancelar</a>
        </form>
    </div>
</main>
<?php include_once("templates/footer.php"); ?>