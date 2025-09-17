document.addEventListener('DOMContentLoaded', function() {
    
    // --- LÓGICA DO MODAL DE DETALHES DO EVENTO ---
    const modal = document.getElementById('event-modal');
    if (!modal) return; // Parar se o modal não existir na página

    const closeButton = document.querySelector('.close-button');
    const eventsGrid = document.querySelector('.events-grid');

    // Elementos do Modal para preencher
    const modalTitle = document.getElementById('modal-title');
    const modalDate = document.getElementById('modal-date');
    const modalLocation = document.getElementById('modal-location');
    const modalCapacity = document.getElementById('modal-capacity');
    const modalDescription = document.getElementById('modal-description');

    // Usando delegação de eventos para ouvir cliques no container
    eventsGrid.addEventListener('click', function(event) {
        // Verifica se o elemento clicado é o botão "Saiba Mais"
        if (event.target.classList.contains('btn-details')) {
            event.preventDefault(); // Impede que o link '#' mude a URL
            
            const button = event.target;
            
            // Pega os dados dos atributos data-* do botão
            modalTitle.textContent = button.dataset.nome;
            modalDate.innerHTML = `<strong>Data e Horário:</strong> ${button.dataset.data}`;
            modalLocation.innerHTML = `<strong>Local:</strong> ${button.dataset.local}`;
            modalCapacity.innerHTML = `<strong>Lotação Máxima:</strong> ${button.dataset.pessoas} pessoas`;
            modalDescription.textContent = button.dataset.descricao;

            modal.style.display = 'block'; // Exibe o modal
        }
    });

    // Fechar o modal ao clicar no 'X'
    if(closeButton) {
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    // Fechar o modal ao clicar fora da área de conteúdo
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});