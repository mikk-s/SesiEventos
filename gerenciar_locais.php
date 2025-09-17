<?php
session_start();
require_once 'conexao.php';

// VERIFICAÇÃO DE PERMISSÃO DE ADMINISTRADOR
if (!isset($_SESSION['perm']) || $_SESSION['perm'] != 'Administrador') {
    $_SESSION['erro'] = "Acesso negado.";
    header("Location: index.php");
    exit();
}

// Lógica para deletar local
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id_para_deletar = $_POST['delete_id'];
    try {
        // Antes de deletar, verifica se o local está sendo usado em algum evento
        $local_info_stmt = $conn->prepare("SELECT sala, bloco FROM locais WHERE id = ?");
        $local_info_stmt->execute([$id_para_deletar]);
        $local_info = $local_info_stmt->fetch();
        $nome_local = $local_info['sala'] . ' - Bloco ' . $local_info['bloco'];

        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM eventos WHERE local = ?");
        $stmt_check->execute([$nome_local]);
        
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['erro'] = "Não é possível excluir este local, pois ele está sendo usado em um ou mais eventos.";
        } else {
            $stmt_delete = $conn->prepare("DELETE FROM locais WHERE id = ?");
            $stmt_delete->execute([$id_para_deletar]);
            $_SESSION['mensagem'] = "Local excluído com sucesso!";
        }
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao excluir local: " . $e->getMessage();
    }
    header("Location: gerenciar_locais.php");
    exit();
}

// Busca todos os locais para exibir na tabela
$locais = $conn->query("SELECT id, sala, bloco FROM locais ORDER BY bloco, sala")->fetchAll(PDO::FETCH_ASSOC);

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
        <h2>Gerenciar Salas e Locais</h2>
        <a href="adicionar_local.php" class="submit-button" style="display: inline-block; width: auto; text-decoration: none; margin-bottom: 1rem;">Adicionar Novo Local</a>

        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sala</th>
                        <th>Bloco</th>
                        <th style="width: 150px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($locais)): ?>
                        <?php foreach ($locais as $local): ?>
                            <tr>
                                <td><?= $local['id'] ?></td>
                                <td><?= htmlspecialchars($local['sala']) ?></td>
                                <td><?= htmlspecialchars($local['bloco']) ?></td>
                                <td>
                                    <a href="editar_local.php?id=<?= $local['id'] ?>" class="btn-editar">Editar</a>
                                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este local?');" style="display: inline;">
                                        <input type="hidden" name="delete_id" value="<?= $local['id'] ?>">
                                        <button type="submit" class="btn-excluir">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Nenhum local cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<style>.btn-editar, .btn-excluir { padding: 5px 10px; text-decoration: none; color: white; border-radius: 4px; border: none; cursor: pointer; } .btn-editar { background-color: #007bff; } .btn-excluir { background-color: #dc3545; }</style>
<?php include_once("templates/footer.php"); ?>