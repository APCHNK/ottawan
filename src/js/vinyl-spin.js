// Rotate the Golden Hits vinyl disc as the user scrolls past the section.
// iOS Safari fires `scroll` sparsely during momentum scroll, so binding the
// rotation directly to scroll events looks janky. Instead we keep a rAF loop
// running while the disc is in view and lerp toward the scroll-derived target,
// which smooths between sparse scroll samples on iPhone.

export function initVinylSpin() {
  const disc = document.querySelector('.songs-vinyl__disc');
  if (!disc) return;

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const TURNS_PER_VIEWPORT = 0.6;
  const LERP = 0.18;

  let targetDeg = 0;
  let currentDeg = 0;
  let inView = false;
  let rafId = null;

  // Promote to its own composited layer so iOS doesn't repaint the rotation.
  disc.style.willChange = 'transform';

  const computeTarget = () => {
    const vh = window.innerHeight || document.documentElement.clientHeight;
    const y = window.scrollY || window.pageYOffset || 0;
    targetDeg = (y * TURNS_PER_VIEWPORT * 360) / vh;
  };

  const apply = () => {
    disc.style.transform = `translate3d(0,0,0) rotate(${currentDeg.toFixed(2)}deg)`;
  };

  const tick = () => {
    const delta = targetDeg - currentDeg;
    if (Math.abs(delta) < 0.05) {
      currentDeg = targetDeg;
    } else {
      currentDeg += delta * LERP;
    }
    apply();
    rafId = inView ? requestAnimationFrame(tick) : null;
  };

  const startLoop = () => {
    if (rafId === null && inView) {
      rafId = requestAnimationFrame(tick);
    }
  };

  const observer = new IntersectionObserver(
    ([entry]) => {
      inView = entry.isIntersecting;
      startLoop();
    },
    { rootMargin: '50% 0px 50% 0px' }
  );
  observer.observe(disc);

  // Initial state — snap to current scroll position so it doesn't ease in.
  computeTarget();
  currentDeg = targetDeg;
  apply();

  const onScroll = () => {
    computeTarget();
    startLoop();
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });
}
