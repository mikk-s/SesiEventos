<?php
session_start();
require_once 'conexao.php';

// VERIFICAÇÃO DE PERMISSÃO DE ADMINISTRADOR
if (!isset($_SESSION['perm']) || $_SESSION['perm'] != 'Administrador') {
    $_SESSION['erro'] = "Acesso negado. Você não tem permissão para acessar esta página.";
    header("Location: index.php");
    exit();
}

// Lógica para buscar estatísticas rápidas
try {
    $total_eventos = $conn->query("SELECT COUNT(*) FROM eventos")->fetchColumn();
    $total_usuarios = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $total_salas = $conn->query("SELECT COUNT(*) FROM locais")->fetchColumn();
    $total_inscricoes = $conn->query("SELECT COUNT(*) FROM inscricoes")->fetchColumn();
} catch (PDOException $e) {
    $erro_stats = "Não foi possível carregar as estatísticas.";
}

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<style>
    .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
    .stat-card { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
    .stat-card h3 { margin-top: 0; color: #5b7086; }
    .stat-card .stat-number { font-size: 2.5rem; font-weight: bold; color: #333; }
    .admin-actions a { display: block; background-color: #5b7086; color: white; padding: 10px; margin-top: 10px; border-radius: 5px; text-decoration: none; transition: background-color 0.3s; }
    .admin-actions a:hover { background-color: #4a5a6a; }
</style>

<main class="form-container">
    <div class="form-card" style="max-width: 1000px;">
        <h2>Painel do Administrador</h2>
        
        <?php if (isset($erro_stats)): ?>
    <p class="message error"><?= htmlspecialchars($erro_stats); ?></p>
<?php else: ?>
<div class="dashboard-grid">
    
    <a href="gerenciar_eventos.php">
        <div class="stat-card">
            <h3>Total de Eventos</h3>
            <p class="stat-number"><?= $total_eventos ?></p>
        </div>
    </a>

    <a href="gerenciar_usuarios.php">
        <div class="stat-card">
            <h3>Total de Usuários</h3>
            <p class="stat-number"><?= $total_usuarios ?></p>
        </div>
    </a>

    <a href="gerenciar_salas.php">
        <div class="stat-card">
            <h3>Salas Cadastradas</h3>
            <p class="stat-number"><?= $total_salas ?></p>
        </div>
    </a>
    
    <a href="meus_ingressos.php">
        <div class="stat-card">
            <h3>Ingressos Adquiridos</h3>
            <p class="stat-number"><?= $total_inscricoes ?></p>
        </div>
    </a>

</div>
<?php endif; ?>

         <hr style="margin: 2rem 0;">
        <h3>Ferramentas de Gerenciamento</h3>
        <div class="dashboard-grid admin-actions">
            <div class="stat-card">
                <h3>Usuários</h3>
                <p>Adicionar, editar e remover usuários do sistema.</p>
                <a href="gerenciar_usuarios.php">Gerenciar Usuários</a>
            </div>
            <div class="stat-card">
                <h3>Salas / Locais</h3>
                <p>Adicionar, editar e remover salas e locais de eventos.</p>
                <a href="gerenciar_locais.php">Gerenciar Salas</a>
            </div>
            <div class="stat-card">
                <h3>Eventos</h3>
                <p>Editar e remover eventos existentes.</p>
                <a href="gerenciar_eventos.php">Gerenciar Eventos</a>
            </div>
        </div>
    </div>
</main>

<?php include_once("templates/footer.php"); ?>