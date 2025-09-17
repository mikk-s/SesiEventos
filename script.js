document.addEventListener('DOMContentLoaded', function() {
    
    // --- LÓGICA PARA O MODAL DE DETALHES DO EVENTO ---

    // Pega os elementos essenciais da página
    const modal = document.getElementById('event-modal');
    const eventsGrid = document.querySelector('.events-grid');

    // Se o modal ou a grade de eventos não existirem nesta página, o script para aqui.
    // Isso evita erros em outras páginas como 'login.php' ou 'cadastrar_evento.php'.
    if (!modal || !eventsGrid) {
        return; 
    }

    // Pega os elementos de dentro do modal que vamos preencher
    const closeButton = document.querySelector('.close-button');
    const modalTitle = document.getElementById('modal-title');
    const modalDate = document.getElementById('modal-date');
    const modalLocation = document.getElementById('modal-location');
    const modalCapacity = document.getElementById('modal-capacity');
    const modalDescription = document.getElementById('modal-description');

    // Adiciona um "ouvinte" de cliques ao container dos eventos.
    // Isso é mais eficiente do que adicionar um para cada botão.
    eventsGrid.addEventListener('click', function(event) {
        
        // Verifica se o que foi clicado foi um botão "Saiba Mais"
        if (event.target.classList.contains('btn-details')) {
            event.preventDefault(); // Impede que o link '#' recarregue a página
            
            const button = event.target;
            
            // Preenche o conteúdo do modal usando os atributos 'data-*' do botão
            modalTitle.textContent = button.dataset.nome;
            modalDate.innerHTML = `<strong>Data e Horário:</strong> ${button.dataset.data}`;
            modalLocation.innerHTML = `<strong>Local:</strong> ${button.dataset.local}`;
            modalCapacity.innerHTML = `<strong>Lotação Máxima:</strong> ${button.dataset.pessoas} pessoas`;
            modalDescription.textContent = button.dataset.descricao;

            // Finalmente, exibe o modal
            modal.style.display = 'block';
        }
    });

    // Adiciona a funcionalidade de fechar o modal no botão 'X'
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    // Adiciona a funcionalidade de fechar o modal ao clicar fora dele
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});