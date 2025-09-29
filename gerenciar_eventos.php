<?php
session_start();
require_once 'conexao.php';

// PERMISSÃO: Apenas Administrador ou Organizador
if (!isset($_SESSION['perm']) || !in_array($_SESSION['perm'], ['Administrador', 'Organizador'])) {
    $_SESSION['erro'] = "Acesso negado."; header("Location: index.php"); exit();
}

// Lógica para deletar evento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id_para_deletar = $_POST['delete_id'];
    try {
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM inscricoes WHERE id_evento = ?");
        $stmt_check->execute([$id_para_deletar]);
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['erro'] = "Não é possível excluir este evento, pois ele já possui participantes inscritos.";
        } else {
            $stmt_delete = $conn->prepare("DELETE FROM eventos WHERE id = ?");
            $stmt_delete->execute([$id_para_deletar]);
            $_SESSION['mensagem'] = "Evento excluído com sucesso!";
        }
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao excluir evento: " . $e->getMessage();
    }
    header("Location: gerenciar_eventos.php");
    exit();
}

$eventos = $conn->query("SELECT id, nome, data, local FROM eventos ORDER BY data DESC")->fetchAll(PDO::FETCH_ASSOC);

include_once("templates/header.php");
if (isset($_SESSION['mensagem']) || isset($_SESSION['erro'])) {
    $mensagem = $_SESSION['mensagem'] ?? $_SESSION['erro'];
    echo "<script>alert('" . addslashes($mensagem) . "');</script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['erro']);
}
?>
<link rel="stylesheet" href="css/style.css">
<main class="form-container">
    <div class="form-card" style="max-width: 900px;">
        <h2>Gerenciar Eventos</h2>
        <a href="cadastrar_evento.php" class="submit-button" style="display: inline-block; width: auto; text-decoration: none; margin-bottom: 1rem;">Adicionar Novo Evento</a>
        <div class="table-container">
            <table class="styled-table">
                <thead><tr><th>Nome</th><th>Data</th><th>Local</th><th style="width: 220px;">Ações</th></tr></thead>
                <tbody>
                    <?php if (!empty($eventos)): ?>
                        <?php foreach ($eventos as $evento): ?>
                            <tr>
                                <td><?= htmlspecialchars($evento['nome']) ?></td>
                                <td><?= (new DateTime($evento['data']))->format('d/m/Y H:i') ?></td>
                                <td><?= htmlspecialchars($evento['local']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="editar_evento.php?id=<?= $evento['id'] ?>" class="btn-editar">Editar</a>
                                        <form method="POST" onsubmit="return confirm('Tem certeza?');">
                                            <input type="hidden" name="delete_id" value="<?= $evento['id'] ?>">
                                            <button type="submit" class="btn-excluir">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Nenhum evento cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include_once("templates/footer.php"); ?>