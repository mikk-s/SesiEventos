
<?php 
session_start();
ob_start();


require "conexao.php";
include_once("templates/header.php");


if (isset($_SESSION["usuario"])) {
    $_SESSION['login_error'] = "Já logou. Redirecionando...";
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") { 
    $email = $_POST["login"];
    $senha = $_POST["senha"];

    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if (password_verify($senha, $usuario["senha"])) {
            $_SESSION["usuario"] = $usuario['nome'];
            $_SESSION["perm"] = $usuario['perm']; 
            header("Location: index.php");
            
            exit();
        } else {
            $_SESSION["erro"] = "Senha incorreta.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION["erro"] = "Usuário não encontrado.";
        header("Location: login.php");
        exit();
    }
}


?>
<link rel="stylesheet" href="css/style.css">
<main class="form-container">

    <div class="form-card" > <h2>Login</h2>
        <?php
if (isset($_SESSION["erro"])) {
   echo "<script>alert('{$_SESSION["erro"]}');</script>";

    unset($_SESSION["erro"]);
}
?>
        <form method="POST">
            
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>
            
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
            
            <button type="submit" class="submit-button">Entrar</button>

        </form>

        <p class="secondary-text">
            Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
        </p>

    </div>
</main>
