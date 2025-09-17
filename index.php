<?php
// Inclui o arquivo de conexão
require_once 'conexao.php';

// --- LÓGICA DE FILTRAGEM ---
// Pega a origem do filtro da URL (via GET). Se não existir, o padrão é 'todos'.
$filtro_origem = $_GET['origem'] ?? 'todos';

// Prepara a base da consulta SQL
$sql = "SELECT * FROM eventos";
$params = [];

// Adiciona a condição WHERE se um filtro específico (SESI ou SENAI) for selecionado
if ($filtro_origem !== 'todos') {
    $sql .= " WHERE origem = ?";
    $params[] = $filtro_origem;
}

// Ordena os eventos pela data mais próxima
$sql .= " ORDER BY data ASC";

// Executa a consulta
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $eventos = $stmt->fetchAll();
} catch (PDOException $e) {
    // Em caso de erro, define $eventos como um array vazio e exibe uma mensagem
    $eventos = [];
    echo "Erro ao buscar eventos: " . $e->getMessage(); // Idealmente, logar o erro
}

// Inclui o cabeçalho da página
include_once("templates/header.php");
?>

<main>
    <section class="filter-section">
        <h1>Eventos Abertos</h1>
        <form method="GET" action="index.php" class="filter-form">
            <label for="event-origin">Filtrar por Origem:</label>
            <select id="event-origin" name="origem" onchange="this.form.submit()">
                <option value="todos" <?= ($filtro_origem == 'todos') ? 'selected' : '' ?>>Todos</option>
                <option value="sesi" <?= ($filtro_origem == 'sesi') ? 'selected' : '' ?>>SESI</option>
                <option value="senai" <?= ($filtro_origem == 'senai') ? 'selected' : '' ?>>SENAI</option>
            </select>
        </form>
    </section>

    <section class="events-grid">
        <?php if (count($eventos) > 0): ?>
            <?php foreach ($eventos as $evento): ?>
                <?php
                    // Formata a data para um formato mais legível
                    $data_formatada = (new DateTime($evento['data']))->format('d/m/Y, H:i');
                ?>
                <div class="event-card">
                    <h2><?= htmlspecialchars($evento['nome']) ?></h2>
                    <p class="event-info"><strong>Data:</strong> <?= $data_formatada ?></p>
                    <p class="event-info"><strong>Local:</strong> <?= htmlspecialchars($evento['local']) ?></p>
                    <p class="event-info"><strong>Vagas:</strong> <?= htmlspecialchars($evento['max_pessoas']) ?></p>
                    <p class="event-origin">Origem: <?= htmlspecialchars($evento['origem']) ?></p>
                    
                    <a href="#" class="btn-details"
                       data-nome="<?= htmlspecialchars($evento['nome']) ?>"
                       data-data="<?= $data_formatada ?>"
                       data-local="<?= htmlspecialchars($evento['local']) ?>"
                       data-pessoas="<?= htmlspecialchars($evento['max_pessoas']) ?>"
                       data-descricao="<?= htmlspecialchars($evento['descricao_completa']) ?>">
                       Saiba Mais
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-events-message">Sem eventos no momento.</p>
        <?php endif; ?>
    </section>
</main>

<?php
// Inclui o rodapé da página
include_once("templates/footer.php");
?>