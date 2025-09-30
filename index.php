<?php
session_start();
require_once 'conexao.php';
include_once("helpers/url.php");

// Buscar alguns eventos para destaque
try {
    // CORREÇÃO: A consulta foi ajustada para já trazer o número de inscritos.
    $sql = "SELECT 
                eventos.*, 
                COALESCE(SUM(inscricoes.quantidade), 0) AS inscritos 
            FROM eventos 
            LEFT JOIN inscricoes ON eventos.id = inscricoes.id_evento
            WHERE eventos.data > NOW()
            GROUP BY eventos.id 
            ORDER BY data ASC 
            LIMIT 6";

    $stmt = $conn->query($sql);
    $eventos_destaque = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Busca os eventos em que o usuário está inscrito para controle
    $eventos_usuario_inscrito = [];
    if (isset($_SESSION['usuario_id'])) {
        $stmt_inscrito = $conn->prepare("SELECT id_evento FROM inscricoes WHERE id_usuario = ?");
        $stmt_inscrito->execute([$_SESSION['usuario_id']]);
        $eventos_usuario_inscrito = $stmt_inscrito->fetchAll(PDO::FETCH_COLUMN);
    }

} catch(PDOException $e) {
    $eventos_destaque = [];
}
if (isset($_SESSION['mensagem']) || isset($_SESSION['erro'])) {
    $mensagem = $_SESSION['mensagem'] ?? $_SESSION['erro'];
    echo "<script>alert('" . addslashes($mensagem) . "');</script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['erro']);
}

include_once("templates/header.php");
?>


<style>
    .hero {
        background: url('img/banner.jpg') no-repeat center center/cover;


        color: black;
        text-align: center;
        padding: 100px 20px;
    }
    .hero h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
</style>
<main>
    <section class="hero">
    
        <h1>Encontre os Melhores Eventos</h1>
        <p>Participe de palestras, workshops e muito mais!</p>
        <a href="eventos.php" class="btn-primary">Ver Todos os Eventos</a>
    </section>

    <div class="container" style="padding: 40px 15px;">
        <h2 style="text-align: center; margin-bottom: 30px;">Próximos Eventos</h2>
        <section class="events-grid">
            <?php if (!empty($eventos_destaque)): ?>
                <?php 
                // **INÍCIO DA CORREÇÃO**
                // Este laço agora prepara as variáveis antes de incluir o template
                foreach ($eventos_destaque as $evento):
                    $vagas_restantes = $evento['max_pessoas'] - $evento['inscritos'];
                    $data_formatada = (new DateTime($evento['data']))->format('d/m/Y, H:i');
                    $usuario_logado = isset($_SESSION['usuario_id']);
                    $usuario_inscrito = in_array($evento['id'], $eventos_usuario_inscrito);

                    // Inclui o template, que agora terá acesso às variáveis necessárias
                    include 'templates/event_card.php'; 
                endforeach; 
                // **FIM DA CORREÇÃO**
                ?>
            <?php else: ?>
                <p>Nenhum evento futuro encontrado.</p>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php
include_once("templates/footer.php");
?>