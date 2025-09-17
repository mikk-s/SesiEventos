<?php
// Certifica-se de que a sessão foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Inclui o helper de URL, como você já tinha
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

<header class="header">
    <div class="container">
      <a href="index.php" class="logo">
          <img src="img/logo_sesi.png" alt="Logo" class="logo-img">
          <img src="img/logo_senai.png" alt="Logo" class="logo-img">
      </a>
      <nav class="nav">
          <a href="index.php">Eventos</a>
          
          <?php if (isset($_SESSION['usuario_id'])): ?>
              <a href="eventos.php">Meus Ingressos</a>

              <?php if (isset($_SESSION['perm']) && in_array($_SESSION['perm'], ['Administrador', 'Organizador'])): ?>
                  <a href="cadastrar_evento.php">Cadastrar Evento</a>
                  <a href="gerenciar_eventos.php">Gerenciar Eventos</a>
              <?php endif; ?>
              
              <?php if (isset($_SESSION['perm']) && $_SESSION['perm'] === 'Administrador'): ?>
                  <a href="dashboard.php">Dashboard</a>
                
              <?php endif; ?>

              <a href="deslogar.php">Sair</a>

          <?php else: ?>
              <a href="login.php">Login</a>
              <a href="cadastro.php">Cadastro</a>
          <?php endif; ?>
      </nav>
    </div>
</header>
</body>
</html>