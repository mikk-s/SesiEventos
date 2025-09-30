<?php
// Garante que a variável $eventos_destaque exista, mesmo que vazia
if (!isset($eventos_destaque)) {
    $eventos_destaque = [];
}
?>
<section class="splide container" aria-label="Carrossel de Próximos Eventos">
  <div class="splide__track">
		<ul class="splide__list">
            <?php foreach($eventos_destaque as $evento): ?>
			<li class="splide__slide">
                <a href="adquirir_ingresso.php?id_evento=<?= $evento['id'] ?>" class="carousel-link">
                    <div class="carousel-slide-content">
                        <img src="<?= $evento['imagem'] ? $BASE_URL . $evento['imagem'] : $BASE_URL . 'img/placeholder.jpg' ?>" alt="<?= htmlspecialchars($evento['nome']) ?>">
                        <h4><?= htmlspecialchars($evento['nome']) ?></h4>
                    </div>
                </a>
			</li>
            <?php endforeach; ?>

            <li class="splide__slide">
                <a href="eventos.php" class="carousel-link">
                    <div class="carousel-slide-content carousel-banner-slide">
                        <h3>Ver Todos os Eventos</h3>
                        <p>Explore nosso catálogo completo!</p>
                    </div>
                </a>
            </li>
		</ul>
  </div>
</section>