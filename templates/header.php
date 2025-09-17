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
                <li><a href="#">Início</a></li>
                <li><a href="cadastrar_evento.php">Cadastrar Evento</a></li>
                <li><a href="#">Eventos</a></li>
                <li><a href="login.php">Login</a></li>
               

            </ul>
        </nav>
    </header>
</body>
</html>
