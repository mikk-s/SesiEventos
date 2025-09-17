<?php
include_once("helpers/url.php");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NetNucleo</title>

 
  <link rel="stylesheet" href="<?= $BASE_URL ?>css/style.css">

  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <div class="logo-container">
            <img src="img/logo_sesi.png" alt="Logo SESI Horto" class="logo">
            <img src="img/logo_senai.png" alt="Logo SENAI Horto" class="logo">
        </div>
  
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                
                <?php if (isset($_SESSION["usuario"])): ?>
                    <li><a href="eventos.php">Meus Ingressos</a></li>
                    
                    <?php if (isset($_SESSION["perm"])): ?>
                        <?php // Link para Cadastrar Evento (Organizador ou Admin)
                        if ($_SESSION["perm"] == "Organizador" || $_SESSION["perm"] == "Administrador"): ?>
                            <li><a href="cadastrar_evento.php">Cadastrar Evento</a></li>
                        <?php endif; ?>

                        <?php // LINK DO PAINEL ADMIN (Apenas Administrador)
                        if ($_SESSION["perm"] == "Administrador"): ?>
                            <li><a href="dashboard.php" style="color: #ffc107; font-weight: bold;">Dashboard</a></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <li><a href="deslogar.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
</body>
</html>
