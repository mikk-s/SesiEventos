<?php
session_start();
require_once 'conexao.php';

// PERMISSÃO: Apenas Administrador ou Organizador
if (!isset($_SESSION['perm']) || !in_array($_SESSION['perm'], ['Administrador', 'Organizador'])) {
    $_SESSION['erro'] = "Acesso negado."; 
    header("Location: index.php"); 
    exit();
}

// Lógica para deletar evento (com verificação de propriedade)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id_para_deletar = $_POST['delete_id'];
    try {
        // Primeiro, verifica quem é o dono do evento
        $stmt_check_owner = $conn->prepare("SELECT organizador FROM eventos WHERE id = ?");
        $stmt_check_owner->execute([$id_para_deletar]);
        $evento_organizador = $stmt_check_owner->fetchColumn();
        
        // Nega a exclusão se o usuário não for Admin E não for o dono do evento
        if ($_SESSION['perm'] !== 'Administrador' && $evento_organizador !== $_SESSION['usuario']) {
            throw new Exception("Você não tem permissão para excluir este evento.");
        }

        // Verifica se há inscritos
        $stmt_check_insc = $conn->prepare("SELECT COUNT(*) FROM inscricoes WHERE id_evento = ?");
        $stmt_check_insc->execute([$id_para_deletar]);
        if ($stmt_check_insc->fetchColumn() > 0) {
            throw new Exception("Não é possível excluir este evento, pois já possui inscritos.");
        }

        // Se passou nas verificações, deleta o evento
        $stmt_delete = $conn->prepare("DELETE FROM eventos WHERE id = ?");
        $stmt_delete->execute([$id_para_deletar]);
        $_SESSION['mensagem'] = "Evento excluído com sucesso!";

    } catch (Exception $e) {
        $_SESSION['erro'] = "Erro ao excluir evento: " . $e->getMessage();
    }
    header("Location: gerenciar_eventos.php");
    exit();
}

// --- LÓGICA DE BUSCA DE EVENTOS CORRIGIDA ---
try {
    if ($_SESSION['perm'] == 'Administrador') {
        // O Administrador vê todos os eventos de todos os organizadores.
        $stmt = $conn->query("SELECT id, nome, data, local, organizador FROM eventos ORDER BY data DESC");
    } else {
        // O Organizador vê apenas os eventos onde a coluna 'organizador' corresponde ao seu nome de usuário.
        $stmt = $conn->prepare("SELECT id, nome, data, local FROM eventos WHERE organizador = ? ORDER BY data DESC");
        $stmt->execute([$_SESSION['usuario']]);
    }
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $eventos = [];
    $_SESSION['erro'] = "Erro ao buscar eventos.";
}

include_once("templates/header.php");
// Exibe mensagens de feedback
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
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Data</th>
                        <th>Local</th>
                        <?php if ($_SESSION['perm'] == 'Administrador'): ?>
                            <th>Organizador</th> <?php endif; ?>
                        <th style="width: 220px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($eventos)): ?>
                        <?php foreach ($eventos as $evento): ?>
                            <tr>
                                <td><?= htmlspecialchars($evento['nome']) ?></td>
                                <td><?= (new DateTime($evento['data']))->format('d/m/Y H:i') ?></td>
                                <td><?= htmlspecialchars($evento['local']) ?></td>
                                <?php if ($_SESSION['perm'] == 'Administrador'): ?>
                                    <td><?= htmlspecialchars($evento['organizador']) ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="action-buttons">
                                        <a href="editar_evento.php?id=<?= $evento['id'] ?>" class="btn-editar">Editar</a>
                                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este evento?');">
                                            <input type="hidden" name="delete_id" value="<?= $evento['id'] ?>">
                                            <button type="submit" class="btn-excluir">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Nenhum evento encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include_once("templates/footer.php"); ?>