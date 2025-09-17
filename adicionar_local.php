<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['perm']) || $_SESSION['perm'] != 'Administrador') {
    $_SESSION['erro'] = "Acesso negado."; header("Location: index.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sala = $_POST['sala'];
    $bloco = $_POST['bloco'];
    try {
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM locais WHERE sala = ? AND bloco = ?");
        $stmt_check->execute([$sala, $bloco]);
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['erro'] = "Este local (sala e bloco) já existe.";
        } else {
            $stmt = $conn->prepare("INSERT INTO locais (sala, bloco) VALUES (?, ?)");
            $stmt->execute([$sala, $bloco]);
            $_SESSION['mensagem'] = "Local adicionado com sucesso!";
            header("Location: gerenciar_locais.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro de banco de dados: " . $e->getMessage();
    }
    header("Location: adicionar_local.php");
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
        <h2>Adicionar Novo Local</h2>
        <form method="POST">
            <label for="sala">Nome da Sala:</label>
            <input type="text" id="sala" name="sala" required>
            <label for="bloco">Bloco:</label>
            <select id="bloco" name="bloco" required>
                <option value="A">A</option> <option value="B">B</option>
                <option value="C">C</option> <option value="D">D</option>
            </select>
            <button type="submit" class="submit-button">Adicionar Local</button>
            <a href="gerenciar_locais.php" style="text-align: center; display: block; margin-top: 1rem;">Cancelar</a>
        </form>
    </div>
</main>
<?php include_once("templates/footer.php"); ?>