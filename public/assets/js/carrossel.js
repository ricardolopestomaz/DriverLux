document.addEventListener('DOMContentLoaded', () => {
  // ==========================================================================
  // CARROSSEL DE CTA (TEXTO + TROCA DE IMAGEM E COR POR SLIDE)
  // ==========================================================================
  const ctaSection  = document.querySelector('.cta');
  const ctaSlides   = document.querySelectorAll('.cta-slide');
  const ctaDots     = document.querySelectorAll('.cta-dot');
  const ctaBtnPrev  = document.querySelector('.cta-carousel-btn.prev');
  const ctaBtnNext  = document.querySelector('.cta-carousel-btn.next');
  let currentSlide  = 0;
  let autoplayTimer = null;

  /**
   * Aplica fundo e cor de texto da seção .cta com base nos data-* do slide ativo.
   * data-bg        → URL da imagem de fundo
   * data-overlay   → "cor_esquerda,cor_direita" para o gradiente overlay
   * data-text-color→ cor do texto do slide (opcional, default: #3b0567)
   */
  function applyBackground(slide) {
    if (!ctaSection || !slide) return;

    const bg      = slide.getAttribute('data-bg') || '';
    const overlay = slide.getAttribute('data-overlay') || 'rgba(255, 255, 255, 0.82) | rgba(255, 255, 255, 0.1)';
    const color   = slide.getAttribute('data-text-color') || '#3b0567';

    const [colorLeft, colorRight] = overlay.split('|').map(s => s.trim());

    ctaSection.style.backgroundImage =
      `linear-gradient(to right, ${colorLeft}, ${colorRight}), url("${bg}")`;
    ctaSection.style.backgroundSize     = 'cover';
    ctaSection.style.backgroundPosition = 'center right';
    ctaSection.style.backgroundRepeat   = 'no-repeat';
    ctaSection.style.color              = color;
  }

  function goToSlide(index) {
    if (!ctaSlides.length) return;
    if (index >= ctaSlides.length) index = 0;
    if (index < 0) index = ctaSlides.length - 1;
    currentSlide = index;

    ctaSlides.forEach((slide, i) => slide.classList.toggle('active', i === currentSlide));
    ctaDots.forEach((dot, i)   => dot.classList.toggle('active', i === currentSlide));
    applyBackground(ctaSlides[currentSlide]);
  }

  function startAutoplay() {
    stopAutoplay();
    autoplayTimer = setInterval(() => goToSlide(currentSlide + 1), 6000);
  }

  function stopAutoplay() {
    if (autoplayTimer) clearInterval(autoplayTimer);
  }

  if (ctaBtnPrev && ctaBtnNext && ctaSlides.length) {
    applyBackground(ctaSlides[0]);   // aplica imagem inicial

    ctaBtnPrev.addEventListener('click', () => { goToSlide(currentSlide - 1); startAutoplay(); });
    ctaBtnNext.addEventListener('click', () => { goToSlide(currentSlide + 1); startAutoplay(); });

    ctaDots.forEach(dot => {
      dot.addEventListener('click', () => {
        goToSlide(parseInt(dot.getAttribute('data-slide'), 10));
        startAutoplay();
      });
    });

    startAutoplay();
  }

  // ==========================================================================
  // CARREGAMENTO DINÂMICO DO RODAPÉ PARA INDEX.HTML
  // ==========================================================================
  const loadFooter = async () => {
    try {
      const response = await fetch('/DriverLux/public/assets/components/footer.html');
      if (response.ok) {
        const html    = await response.text();
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const footer = wrapper.firstElementChild;
        if (footer) document.body.appendChild(footer);
      }
    } catch (e) {
      console.error('Erro ao carregar o rodapé:', e);
    }
  };

  loadFooter();
});
