<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['perm']) || $_SESSION['perm'] != 'Administrador') {
    $_SESSION['erro'] = "Acesso negado."; header("Location: index.php"); exit();
}
$id_local = $_GET['id'] ?? null;
if (!$id_local) { header("Location: gerenciar_locais.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sala = $_POST['sala'];
    $bloco = $_POST['bloco'];
    try {
        $stmt = $conn->prepare("UPDATE locais SET sala = ?, bloco = ? WHERE id = ?");
        $stmt->execute([$sala, $bloco, $id_local]);
        $_SESSION['mensagem'] = "Local atualizado com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao atualizar local: " . $e->getMessage();
    }
    header("Location: gerenciar_locais.php");
    exit();
}

$local = $conn->prepare("SELECT sala, bloco FROM locais WHERE id = ?");
$local->execute([$id_local]);
$local = $local->fetch();

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<main class="form-container">
    <div class="form-card">
        <h2>Editar Local</h2>
        <form method="POST">
            <label for="sala">Nome da Sala:</label>
            <input type="text" id="sala" name="sala" value="<?= htmlspecialchars($local['sala']) ?>" required>
            <label for="bloco">Bloco:</label>
            <select id="bloco" name="bloco" required>
                <option value="A" <?= ($local['bloco'] == 'A') ? 'selected' : '' ?>>A</option>
                <option value="B" <?= ($local['bloco'] == 'B') ? 'selected' : '' ?>>B</option>
                <option value="C" <?= ($local['bloco'] == 'C') ? 'selected' : '' ?>>C</option>
                <option value="D" <?= ($local['bloco'] == 'D') ? 'selected' : '' ?>>D</option>
            </select>
            <button type="submit" class="submit-button">Salvar Alterações</button>
            <a href="gerenciar_locais.php" style="text-align: center; display: block; margin-top: 1rem;">Cancelar</a>
        </form>
    </div>
</main>
<?php include_once("templates/footer.php"); ?>