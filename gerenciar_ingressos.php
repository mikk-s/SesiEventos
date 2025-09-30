<?php
session_start();
require_once 'conexao.php';

// PERMISSÃO: Apenas Administrador ou Organizador
if (!isset($_SESSION['perm']) || !in_array($_SESSION['perm'], ['Administrador', 'Organizador'])) {
    $_SESSION['erro'] = "Acesso negado."; 
    header("Location: index.php"); 
    exit();
}

// Lógica para revogar ingressos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Revogar uma inscrição específica
        if (isset($_POST['revogar_inscricao'])) {
            $id_inscricao = $_POST['id_inscricao'];
            $stmt = $conn->prepare("DELETE FROM inscricoes WHERE id = ?");
            $stmt->execute([$id_inscricao]);
            $_SESSION['mensagem'] = "Ingresso revogado com sucesso!";
        }
        // Revogar todos os ingressos de um evento
        elseif (isset($_POST['revogar_evento'])) {
            $id_evento = $_POST['id_evento'];
            $stmt = $conn->prepare("DELETE FROM inscricoes WHERE id_evento = ?");
            $stmt->execute([$id_evento]);
            $_SESSION['mensagem'] = "Todos os ingressos do evento foram revogados!";
        }
        // Revogar TODOS os ingressos (apenas Admin)
        elseif (isset($_POST['revogar_todos']) && $_SESSION['perm'] == 'Administrador') {
            $conn->query("DELETE FROM inscricoes");
            $_SESSION['mensagem'] = "TODOS OS INGRESSOS DE TODOS OS EVENTOS FORAM REVOGADOS!";
        }
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao revogar ingresso(s): " . $e->getMessage();
    }
    header("Location: gerenciar_ingressos.php");
    exit();
}

// Busca de Ingressos
try {
    $sql = "SELECT i.id as id_inscricao, u.nome as nome_usuario, e.nome as nome_evento, e.id as id_evento, i.quantidade
            FROM inscricoes i
            JOIN usuarios u ON i.id_usuario = u.id
            JOIN eventos e ON i.id_evento = e.id";
    
    // Se for organizador, filtra pelos seus eventos
    if ($_SESSION['perm'] == 'Organizador') {
        $sql .= " WHERE e.organizador = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['usuario']]);
    } else {
        $stmt = $conn->query($sql);
    }
    $inscricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupa as inscrições por evento para o botão "Revogar Todos do Evento"
    $eventos_com_ingressos = [];
    foreach ($inscricoes as $inscricao) {
        $eventos_com_ingressos[$inscricao['id_evento']] = $inscricao['nome_evento'];
    }

} catch (PDOException $e) {
    $erro_busca = "Erro ao carregar ingressos: " . $e->getMessage();
}

if (isset($_SESSION['mensagem']) || isset($_SESSION['erro'])) {
    $mensagem = $_SESSION['mensagem'] ?? $_SESSION['erro'];
    echo "<script>alert('" . addslashes($mensagem) . "');</script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['erro']);
}
include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<main class="form-container">
    <div class="form-card" style="max-width: 1000px;">
        <h2>Gerenciar Ingressos</h2>
        
        <?php if ($_SESSION['perm'] == 'Administrador'): ?>
        <div class="admin-actions" style="border: 2px solid #dc3545; padding: 1rem; margin-bottom: 2rem; border-radius: 8px;">
            <h4>Ações de Administrador</h4>
            <form method="POST" onsubmit="return confirm('ATENÇÃO: ISSO REVOGARÁ TODOS OS INGRESSOS DE TODOS OS EVENTOS. Deseja continuar?');">
                <button type="submit" name="revogar_todos" class="btn-excluir">Revogar Todos os Ingressos do Sistema</button>
            </form>
            <hr style="margin: 1rem 0;">
            <form method="POST" onsubmit="return confirm('Tem certeza que deseja revogar TODOS os ingressos deste evento?');">
                <select name="id_evento" required>
                    <option value="">Selecione um evento para revogar...</option>
                    <?php foreach ($eventos_com_ingressos as $id => $nome): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($nome) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="revogar_evento" class="btn-excluir">Revogar Ingressos do Evento Selecionado</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Evento</th>
                        <th>Qtd. Ingressos</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inscricoes)): ?>
                        <?php foreach ($inscricoes as $inscricao): ?>
                            <tr>
                                <td><?= htmlspecialchars($inscricao['nome_usuario']) ?></td>
                                <td><?= htmlspecialchars($inscricao['nome_evento']) ?></td>
                                <td><?= htmlspecialchars($inscricao['quantidade']) ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Deseja revogar este ingresso?');">
                                        <input type="hidden" name="id_inscricao" value="<?= $inscricao['id_inscricao'] ?>">
                                        <button type="submit" name="revogar_inscricao" class="btn-excluir">Revogar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Nenhum ingresso encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include_once("templates/footer.php"); ?>