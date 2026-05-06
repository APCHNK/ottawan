export function initBurger() {
  const burger = document.querySelector('.toggle-menu');
  if (!burger) return;

  burger.addEventListener('click', () => {
    const header = document.querySelector('header');
    const menu = document.querySelector('.menu');
    const isOpen = menu.classList.contains('open');

    if (isOpen) {
      menu.classList.remove('open');
      header.classList.remove('open-menu');
      document.body.classList.remove('menu-open');
    } else {
      menu.classList.add('open');
      header.classList.add('open-menu');
      document.body.classList.add('menu-open');
    }
  });

  // Submenu toggle on mobile
  const submenuIndicators = document.querySelectorAll('.menu .has-submenu > a .submenu-indicator');
  submenuIndicators.forEach((indicator) => {
    indicator.addEventListener('click', (e) => {
      if (window.innerWidth < 1024) {
        e.preventDefault();
        e.stopPropagation();
        const li = e.currentTarget.closest('li');
        if (li) {
          li.classList.toggle('expanded');
        }
      }
    });
  });
}
