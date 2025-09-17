<?php
session_start();
require_once 'conexao.php';

// VERIFICAÇÃO DE PERMISSÃO DE ADMINISTRADOR
if (!isset($_SESSION['perm']) || $_SESSION['perm'] != 'Administrador') {
    $_SESSION['erro'] = "Acesso negado.";
    header("Location: index.php");
    exit();
}

// Lógica para deletar usuário (se um ID for enviado via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id_para_deletar = $_POST['delete_id'];
    
    // Proteção para não deixar o admin se auto-deletar
    if ($id_para_deletar == $_SESSION['usuario_id']) {
        $_SESSION['erro'] = "Você não pode excluir sua própria conta.";
    } else {
        try {
            // Verifica se o usuário tem inscrições em eventos
            $stmt_check = $conn->prepare("SELECT COUNT(*) FROM inscricoes WHERE id_usuario = ?");
            $stmt_check->execute([$id_para_deletar]);
            if ($stmt_check->fetchColumn() > 0) {
                 $_SESSION['erro'] = "Não é possível excluir este usuário, pois ele possui ingressos. Remova as inscrições primeiro.";
            } else {
                $stmt_delete = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt_delete->execute([$id_para_deletar]);
                $_SESSION['mensagem'] = "Usuário excluído com sucesso!";
            }
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao excluir usuário: " . $e->getMessage();
        }
    }
    header("Location: gerenciar_usuarios.php");
    exit();
}

// Busca todos os usuários para exibir na tabela
try {
    $usuarios = $conn->query("SELECT id, nome, email, perm FROM usuarios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usuarios = [];
    $erro_busca = "Erro ao carregar usuários.";
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
        <h2>Gerenciar Usuários</h2>
        <a href="adicionar_usuario.php" class="submit-button" style="display: inline-block; width: auto; text-decoration: none; margin-bottom: 1rem;">Adicionar Novo Usuário</a>

        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Permissão</th>
                        <th style="width: 150px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= $usuario['id'] ?></td>
                                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['perm']) ?></td>
                                <td>
                                    <a href="editar_usuario.php?id=<?= $usuario['id'] ?>" class="btn-editar">Editar</a>
                                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');" style="display: inline;">
                                        <input type="hidden" name="delete_id" value="<?= $usuario['id'] ?>">
                                        <button type="submit" class="btn-excluir">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Nenhum usuário encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<style>.btn-editar, .btn-excluir { padding: 5px 10px; text-decoration: none; color: white; border-radius: 4px; border: none; cursor: pointer; } .btn-editar { background-color: #007bff; } .btn-excluir { background-color: #dc3545; }</style>
<?php include_once("templates/footer.php"); ?>