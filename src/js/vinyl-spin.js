// Rotate the Golden Hits vinyl disc in sync with scroll position.
// Key iOS detail: during momentum scroll, `scroll` events fire sparsely but
// `window.scrollY` keeps updating every frame. So we poll scrollY inside a
// rAF loop (gated by IntersectionObserver) instead of binding to `scroll`,
// which eliminates the stepping you'd see with an event-driven approach.

export function initVinylSpin() {
  const disc = document.querySelector('.songs-vinyl__disc');
  if (!disc) return;

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const TURNS_PER_VIEWPORT = 0.6;

  let currentDeg = 0;
  let inView = false;
  let rafId = null;

  // Promote to a composited layer so iOS doesn't repaint on rotation.
  disc.style.willChange = 'transform';
  disc.style.backfaceVisibility = 'hidden';

  const apply = () => {
    disc.style.transform = `translate3d(0,0,0) rotate(${currentDeg.toFixed(2)}deg)`;
  };

  const tick = () => {
    const vh = window.innerHeight || document.documentElement.clientHeight;
    const y = window.scrollY || window.pageYOffset || 0;
    currentDeg = (y * TURNS_PER_VIEWPORT * 360) / vh;
    apply();
    rafId = inView ? requestAnimationFrame(tick) : null;
  };

  const observer = new IntersectionObserver(
    ([entry]) => {
      inView = entry.isIntersecting;
      if (inView && rafId === null) {
        rafId = requestAnimationFrame(tick);
      }
    },
    { rootMargin: '50% 0px 50% 0px' }
  );
  observer.observe(disc);

  // Snap to current scroll on init so it doesn't pop.
  const vh = window.innerHeight || document.documentElement.clientHeight;
  const y = window.scrollY || window.pageYOffset || 0;
  currentDeg = (y * TURNS_PER_VIEWPORT * 360) / vh;
  apply();
}
