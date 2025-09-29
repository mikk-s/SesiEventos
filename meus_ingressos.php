<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION["usuario_id"])) {
    $_SESSION['erro'] = "Você precisa estar logado para ver seus ingressos.";
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['mensagem']) || isset($_SESSION['erro'])) {
    $mensagem = $_SESSION['mensagem'] ?? $_SESSION['erro'];
    echo "<script>alert('" . addslashes($mensagem) . "');</script>";
    unset($_SESSION['mensagem']);
    unset($_SESSION['erro']);
}

$meus_eventos = [];
$id_usuario = $_SESSION['usuario_id'];

try {
    // A consulta agora também pega o ID do evento para o link "Comprar Mais"
    $sql = "SELECT 
                i.id as id_inscricao,
                i.quantidade, 
                e.id as id_evento,
                e.nome, 
                e.data
            FROM inscricoes AS i
            JOIN eventos AS e ON i.id_evento = e.id
            WHERE i.id_usuario = ?
            ORDER BY e.data ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_usuario]);
    $meus_eventos = $stmt->fetchAll();

} catch (PDOException $e) {
    $meus_eventos = [];
    echo "Erro ao buscar seus ingressos: " . $e->getMessage();
}

include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<main class="form-container">
    <div class="form-card" style="max-width: 900px;"> <h2>Meus Ingressos</h2>

        <?php if (count($meus_eventos) > 0): ?>
            <p>Aqui estão todos os eventos para os quais você adquiriu ingresso.</p>
            
            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Data</th>
                            <th>Ingressos</th>
                            <th style="width: 300px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meus_eventos as $evento): ?>
                            <tr>
                                <td><?= htmlspecialchars($evento['nome']) ?></td>
                                <td><?= (new DateTime($evento['data']))->format('d/m/Y, H:i') ?></td>
                                <td><?= htmlspecialchars($evento['quantidade']) ?></td>
                                <td>
                                    <div class="action-buttons-meus-ingressos">
                                        <form class="cancel-form" action="cancelar_inscricao.php" method="POST" onsubmit="return confirm('Tem certeza que deseja cancelar os ingressos selecionados?');">
                                            <input type="hidden" name="id_inscricao" value="<?= $evento['id_inscricao'] ?>">
                                            <input type="number" name="quantidade" value="1" min="1" max="<?= $evento['quantidade'] ?>" class="cancel-input">
                                            <button type="submit" class="btn-excluir">Cancelar</button>
                                        </form>
                                        <a href="adquirir_ingresso.php?id_evento=<?= $evento['id_evento'] ?>" class="btn-editar">Comprar Mais</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <p class="no-events-message">Você ainda não adquiriu nenhum ingresso.</p>
        <?php endif; ?>
    </div>
</main>
<style>
/* Estilos para alinhar os botões e campo de quantidade */
.action-buttons-meus-ingressos {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cancel-form {
    display: flex;
    gap: 5px;
}
.cancel-input {
    width: 120px;
    padding: 8px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
}
</style>
<?php
include_once("templates/footer.php");
?>