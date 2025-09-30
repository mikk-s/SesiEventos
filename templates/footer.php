<footer class="footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> SESI Eventos. Todos os direitos reservados.</p>
    </div>
</footer>
   <div id="event-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <div class="modal-image-container">
                <img id="modal-image" src="" alt="Imagem do Evento">
            </div>
            <div class="modal-details">
                <h2 id="modal-title"></h2>
                <p id="modal-date"></p>
                <p id="modal-location"></p>
                <p id="modal-capacity"></p>
                <p id="modal-description"></p>
                <div id="modal-action" class="event-action"></div>
            </div>
        </div>
    </div>
     <script src="script.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const mainCarouselElement = document.getElementById('main-carousel');
        if (mainCarouselElement) {
            const mainSplide = new Splide('#main-carousel', {
                type: 'fade',
                rewind: true,
                pagination: true,
                arrows: true,
                autoplay: true,
                interval: 4000,
            });

            const infoSection = document.getElementById('carousel-event-info');
            const infoLink = document.getElementById('event-info-link');
            const infoTitle = document.getElementById('event-info-title');
            const infoDate = document.getElementById('event-info-date');
            const infoLocation = document.getElementById('event-info-location');

            // CORREÇÃO: Função atualizada para esconder o título do banner
            const updateEventInfo = (slide) => {
                const slideEl = slide.slide;
                // Verifica se é o slide do banner
                if (slideEl.dataset.type === 'banner') {
                    infoSection.style.display = 'none'; // Esconde a seção de informações
                } else {
                    infoSection.style.display = 'block'; // Mostra a seção de informações
                    infoTitle.textContent = slideEl.dataset.title || '';
                    infoDate.textContent = slideEl.dataset.date || '';
                    infoLocation.textContent = slideEl.dataset.location ? `• ${slideEl.dataset.location}` : '';
                    infoLink.href = slideEl.dataset.link || '#';
                }
            };
            
            mainSplide.on('active', updateEventInfo);
            mainSplide.mount();
        }

        const secondaryCarouselElement = document.getElementById('secondary-carousel');
        if (secondaryCarouselElement) {
            new Splide('#secondary-carousel', {
                type: 'slide',
                rewind: true,
                perPage: 4,
                perMove: 1,
                gap: '1rem',
                pagination: false,
                arrows: true,
                breakpoints: { 1024: { perPage: 3 }, 768: { perPage: 2 }, 640: { perPage: 1 } }
            }).mount();
        }
    });
    </script> 
</body>
</html>