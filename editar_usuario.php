<?php
session_start();
require_once 'conexao.php';

// VERIFICAÇÃO DE PERMISSÃO DE ADMINISTRADOR
if (!isset($_SESSION['perm']) || $_SESSION['perm'] != 'Administrador') {
    $_SESSION['erro'] = "Acesso negado.";
    header("Location: index.php");
    exit();
}

$id_usuario = $_GET['id'] ?? null;
if (!$id_usuario) {
    header("Location: gerenciar_usuarios.php");
    exit();
}

// Processa a atualização quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $perm = $_POST['perm'];
    $senha = $_POST['senha']; // Senha nova (opcional)
    
    try {
        // Proteção para impedir que o admin logado altere sua própria permissão para algo que não seja 'Administrador'
        if ($id_usuario == $_SESSION['usuario_id'] && $perm != 'Administrador') {
            throw new Exception("Você não pode remover sua própria permissão de administrador.");
        }
        
        // Se uma nova senha foi fornecida, atualiza. Senão, mantém a antiga.
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nome = ?, email = ?, perm = ?, senha = ? WHERE id = ?";
            $params = [$nome, $email, $perm, $senha_hash, $id_usuario];
        } else {
            $sql = "UPDATE usuarios SET nome = ?, email = ?, perm = ? WHERE id = ?";
            $params = [$nome, $email, $perm, $id_usuario];
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $_SESSION['mensagem'] = "Usuário atualizado com sucesso!";

    } catch (Exception $e) {
        $_SESSION['erro'] = "Erro ao atualizar usuário: " . $e->getMessage();
    }
    
    header("Location: gerenciar_usuarios.php");
    exit();
}


// Busca os dados atuais do usuário para preencher o formulário
try {
    $stmt_user = $conn->prepare("SELECT id, nome, email, perm FROM usuarios WHERE id = ?");
    $stmt_user->execute([$id_usuario]);
    $usuario = $stmt_user->fetch();

    if (!$usuario) {
        $_SESSION['erro'] = "Usuário não encontrado.";
        header("Location: gerenciar_usuarios.php");
        exit();
    }
} catch (PDOException $e) {
    die("Erro ao buscar dados do usuário.");
}

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">

<main class="form-container">
    <div class="form-card">
        <h2>Editar Usuário: <?= htmlspecialchars($usuario['nome']) ?></h2>

        <form method="POST">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label for="senha">Nova Senha (deixe em branco para não alterar):</label>
            <input type="password" id="senha" name="senha">

            <label for="perm">Permissão:</label>
            <select id="perm" name="perm" required>
                <option value="Visitante" <?= ($usuario['perm'] == 'Visitante') ? 'selected' : '' ?>>Visitante</option>
                <option value="Organizador" <?= ($usuario['perm'] == 'Organizador') ? 'selected' : '' ?>>Organizador</option>
                <option value="Administrador" <?= ($usuario['perm'] == 'Administrador') ? 'selected' : '' ?>>Administrador</option>
            </select>

            <button type="submit" class="submit-button">Salvar Alterações</button>
            <a href="gerenciar_usuarios.php" class="btn-cancelar" style="text-align: center; display: block; margin-top: 1rem;">Cancelar</a>
        </form>
    </div>
</main>

<?php include_once("templates/footer.php"); ?>