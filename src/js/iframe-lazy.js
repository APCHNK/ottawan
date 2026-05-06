export function initLazyIframes() {
  const iframes = document.querySelectorAll('iframe[data-src]');
  if (iframes.length === 0) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const iframe = entry.target;
          iframe.src = iframe.dataset.src;
          observer.unobserve(iframe);
        }
      });
    },
    { rootMargin: '100px' }
  );

  iframes.forEach((iframe) => observer.observe(iframe));
}
