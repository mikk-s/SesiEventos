<?php
// templates/event_card.php
?>
<div class="event-card card">
    <img src="<?= $evento['imagem'] ? $BASE_URL . $evento['imagem'] : $BASE_URL . 'img/placeholder.jpg' ?>" alt="Imagem do Evento: <?= htmlspecialchars($evento['nome']) ?>" class="event-card-image">
    
    <div class="event-card-content">
        <h3><?= htmlspecialchars($evento['nome']) ?></h3>
        <p class="event-info"><strong>Origem:</strong> <?= htmlspecialchars($evento['origem']) ?></p>
        <p class="event-info">Data: <?= $data_formatada ?></p>
        <p class="event-info">Local: <?= htmlspecialchars($evento['local']) ?></p>
        <p class="event-info">Vagas: 
            <?php 
                if ($evento['max_pessoas'] > 0) {
                    echo $vagas_restantes > 0 ? $vagas_restantes : 'Esgotado';
                } else {
                    echo 'Ilimitadas';
                }
            ?>
        </p>
        
        <a href="#" class="btn-primary btn-details"
           data-nome="<?= htmlspecialchars($evento['nome']) ?>"
           data-data="<?= $data_formatada ?>"
           data-local="<?= htmlspecialchars($evento['local']) ?>"
           data-pessoas="<?= htmlspecialchars($evento['max_pessoas']) ?>"
           data-descricao="<?= htmlspecialchars($evento['descricao_completa']) ?>"
           data-evento-id="<?= $evento['id'] ?>"
           data-origem="<?= htmlspecialchars($evento['origem']) ?>"
           data-vagas-restantes="<?= ($evento['max_pessoas'] > 0) ? $vagas_restantes : '9999' ?>"
           data-usuario-logado="<?= $usuario_logado ? 'true' : 'false' ?>"
           data-usuario-inscrito="<?= $usuario_inscrito ? 'true' : 'false' ?>"
           data-imagem="<?= $evento['imagem'] ? $BASE_URL . $evento['imagem'] : $BASE_URL . 'img/placeholder.jpg' ?>">
           Saiba Mais
        </a>
    </div>
</div>