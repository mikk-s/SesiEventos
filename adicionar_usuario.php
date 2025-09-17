<?php
session_start();
require_once 'conexao.php';

// VERIFICAÇÃO DE PERMISSÃO DE ADMINISTRADOR
if (!isset($_SESSION['perm']) || $_SESSION['perm'] != 'Administrador') {
    $_SESSION['erro'] = "Acesso negado.";
    header("Location: index.php");
    exit();
}

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $perm = $_POST['perm'];

    // Validação básica
    if (empty($nome) || empty($email) || empty($senha) || empty($perm)) {
        $_SESSION['erro'] = "Por favor, preencha todos os campos.";
    } else {
        try {
            // Verifica se o email já existe
            $stmt_check = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
            $stmt_check->execute([$email]);
            if ($stmt_check->fetchColumn() > 0) {
                $_SESSION['erro'] = "Este email já está cadastrado.";
            } else {
                // Criptografa a senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                $sql = "INSERT INTO usuarios (nome, email, senha, perm) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nome, $email, $senha_hash, $perm]);

                $_SESSION['mensagem'] = "Usuário adicionado com sucesso!";
                header("Location: gerenciar_usuarios.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro de banco de dados: " . $e->getMessage();
        }
    }
    // Recarrega a página para exibir o erro
    header("Location: adicionar_usuario.php");
    exit();
}

include_once("templates/header.php");
if (isset($_SESSION['erro'])) {
    echo "<script>alert('" . addslashes($_SESSION['erro']) . "');</script>";
    unset($_SESSION['erro']);
}
?>
<link rel="stylesheet" href="css/style.css">

<main class="form-container">
    <div class="form-card">
        <h2>Adicionar Novo Usuário</h2>
        <form method="POST" action="adicionar_usuario.php">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <label for="perm">Permissão:</label>
            <select id="perm" name="perm" required>
                <option value="">-- Selecione a permissão --</option>
                <option value="Visitante">Visitante</option>
                <option value="Organizador">Organizador</option>
                <option value="Administrador">Administrador</option>
            </select>

            <button type="submit" class="submit-button">Adicionar Usuário</button>
            <a href="gerenciar_usuarios.php" class="btn-cancelar" style="text-align: center; display: block; margin-top: 1rem;">Cancelar</a>
        </form>
    </div>
</main>

<?php include_once("templates/footer.php"); ?>