<?php
session_start();
require_once 'conexao.php';
include_once("helpers/url.php");

try {
    // Busca 4 eventos para o carrossel principal (destaques)
    $stmt_featured = $conn->query(
        "SELECT id, nome, data, local, imagem 
         FROM eventos 
         WHERE data > NOW() AND imagem IS NOT NULL AND imagem != '' 
         ORDER BY data ASC 
         LIMIT 4"
    );
    $eventos_destaque = $stmt_featured->fetchAll(PDO::FETCH_ASSOC);

    // Busca 8 eventos para o carrossel secundário (próximos eventos)
    $stmt_upcoming = $conn->query(
        "SELECT eventos.*, COALESCE(SUM(inscricoes.quantidade), 0) AS inscritos 
         FROM eventos 
         LEFT JOIN inscricoes ON eventos.id = inscricoes.id_evento
         WHERE eventos.data > NOW()
         GROUP BY eventos.id 
         ORDER BY data ASC 
         LIMIT 8"
    );
    $eventos_proximos = $stmt_upcoming->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $eventos_destaque = [];
    $eventos_proximos = [];
}

include_once("templates/header.php");
?>

<main>
    <section id="main-carousel" class="splide" aria-label="Eventos em destaque">
        <div class="splide__track">
            <ul class="splide__list">
                <?php foreach ($eventos_destaque as $evento): 
                    $data_formatada = (new DateTime($evento['data']))->format('d/m/Y, H:i');
                ?>
                <li class="splide__slide" 
                    data-title="<?= htmlspecialchars($evento['nome']) ?>" 
                    data-date="<?= $data_formatada ?>"
                    data-location="<?= htmlspecialchars($evento['local']) ?>"
                    data-link="adquirir_ingresso.php?id_evento=<?= $evento['id'] ?>">
                    <div class="main-carousel-image-container">
                        <img src="<?= $BASE_URL . htmlspecialchars($evento['imagem']) ?>" alt="<?= htmlspecialchars($evento['nome']) ?>">
                    </div>
                </li>
                <?php endforeach; ?>

                <li class="splide__slide" 
                    data-type="banner"
                    data-title="Encontre os Melhores Eventos" 
                    data-link="eventos.php">
                    <div class="main-carousel-image-container final-slide-banner">
                         <h2>Encontre os Melhores Eventos</h2>
                         <p>Participe de palestras, workshops e muito mais!</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>

    <section id="carousel-event-info" class="container">
        <a id="event-info-link" href="#">
            <h2 id="event-info-title"></h2>
            <p>
                <span id="event-info-date"></span>
                <span id="event-info-location"></span>
            </p>
        </a>
    </section>

    <section class="search-and-filter-section" style="padding: 2rem 1rem;">
         <div class="container">
            <form action="eventos.php" method="GET">
                <div class="search-bar">
                    <input type="search" name="search_term" placeholder="Busque por shows, palestras, workshops...">
                    <button type="submit">Pesquisar</button>
                </div>
                <div class="filters-container">
                    <div class="filter-group">
                        <label for="event-origin">Origem:</label>
                        <select id="event-origin" name="origem">
                            <option value="todos">Todos</option>
                            <option value="SESI">SESI</option>
                            <option value="SENAI">SENAI</option>
                        </select>
                    </div>
                    <div class="filter-group date-filter-group">
                        <label for="data_inicio">Período:</label>
                        <input type="date" name="data_inicio" id="data_inicio" title="Data de início">
                        <span>até</span>
                        <input type="date" name="data_fim" id="data_fim" title="Data final">
                    </div>
                </div>
            </form>
        </div>
    </section>
    
    <div class="container" style="padding: 40px 15px;">
        <h2 style="text-align: center; margin-bottom: 30px;">Próximos Eventos</h2>
        <section id="secondary-carousel" class="splide" aria-label="Próximos eventos">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php foreach ($eventos_proximos as $evento): ?>
                    <li class="splide__slide">
                        <?php 
                            $vagas_restantes = ($evento['max_pessoas'] > 0) ? $evento['max_pessoas'] - $evento['inscritos'] : PHP_INT_MAX;
                            $data_formatada = (new DateTime($evento['data']))->format('d/m/Y, H:i');
                            $usuario_logado = isset($_SESSION['usuario_id']);
                            $usuario_inscrito = false;
                            include 'templates/event_card.php'; 
                        ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    </div>
</main>

<?php
include_once("templates/footer.php");
?>