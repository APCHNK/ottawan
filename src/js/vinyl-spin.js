// Rotate the Golden Hits vinyl disc as the user scrolls past the section.
// Cross-browser: rAF-throttled scroll listener that maps scroll progress through
// the section to a rotation of the disc element.

export function initVinylSpin() {
  const disc = document.querySelector('.songs-vinyl__disc');
  if (!disc) return;

  // Respect reduced-motion preference.
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  // Tunables: how many full rotations across the scroll range, and how
  // generous the active range is (multiplier of viewport height).
  const TURNS_PER_VIEWPORT = 0.6;

  let ticking = false;
  let lastY = 0;

  const update = () => {
    const rect = disc.getBoundingClientRect();
    const vh = window.innerHeight || document.documentElement.clientHeight;

    // Skip work when the disc is far above/below the viewport.
    if (rect.bottom < -vh || rect.top > vh * 2) {
      ticking = false;
      return;
    }

    // Use the scroll delta from the page's scroll Y to drive rotation.
    // Linear and continuous: each pixel of scroll = a fixed amount of rotation.
    const y = window.scrollY || window.pageYOffset || 0;
    const deg = (y * TURNS_PER_VIEWPORT * 360) / vh;
    disc.style.transform = `rotate(${deg}deg)`;
    lastY = y;
    ticking = false;
  };

  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(update);
  };

  // Run once to set initial rotation, then bind.
  update();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });
}
