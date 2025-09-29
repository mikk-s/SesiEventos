<?php
session_start();
require_once 'conexao.php';
include_once("helpers/url.php");

// A sua lógica de busca de eventos continua a mesma, está perfeita.
try {
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

    $eventos_usuario_inscrito = [];
    if (isset($_SESSION['usuario_id'])) {
        $stmt_inscrito = $conn->prepare("SELECT id_evento FROM inscricoes WHERE id_usuario = ?");
        $stmt_inscrito->execute([$_SESSION['usuario_id']]);
        $eventos_usuario_inscrito = $stmt_inscrito->fetchAll(PDO::FETCH_COLUMN);
    }

} catch(PDOException $e) {
    $eventos_destaque = [];
}

include_once("templates/header.php");
?>
<<<<<<< HEAD
<style>
    .hero {
        background: url('img/sesii.jpg') no-repeat center center/cover;
        color: white;
        text-align: center;
        padding: 100px 20px;
    }
    .hero h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
</style>
=======

>>>>>>> 33e83d4be28257176f3ae9b90f3f154d410b855f
<main>
    <section class="home-header">
        <div class="container">
            <div class="home-header-text">
                <h1>Encontre os Melhores Eventos</h1>
                <p>Participe de palestras, workshops e muito mais no SESI/SENAI!</p>
            </div>

            <div class="swiper-container">
                <div class="swiper">
                    <div class="swiper-wrapper">
                        <?php if (!empty($eventos_destaque)): ?>
                            <?php 
                            foreach ($eventos_destaque as $evento):
                                $vagas_restantes = $evento['max_pessoas'] - $evento['inscritos'];
                                $data_formatada = (new DateTime($evento['data']))->format('d/m/Y, H:i');
                                $usuario_logado = isset($_SESSION['usuario_id']);
                                $usuario_inscrito = in_array($evento['id'], $eventos_usuario_inscrito);
                            ?>
                                <div class="swiper-slide">
                                    <?php include 'templates/event_card.php'; ?>
                                </div>
                            <?php 
                            endforeach; 
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>

        </div>
    </section>

    </main>

<?php
include_once("templates/footer.php");
?>