export function initTextSliders() {
  const el = document.querySelector('.description-swiper');
  if (!el || typeof Swiper === 'undefined') return;

  const swiper = new Swiper('.description-swiper', {
    loop: true,
    speed: 600,
    autoHeight: true,
    grabCursor: true,
  });
}
