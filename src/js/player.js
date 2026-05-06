export function initPlayer() {
  const playerEl = document.querySelector('.player-wrap');
  if (!playerEl) return;

  const iframe = playerEl.querySelector('#spotify-player');
  const librarySongs = playerEl.querySelectorAll('.library-song');
  if (!iframe || librarySongs.length === 0) return;

  let currentIndex = 0;

  function setActive(index) {
    librarySongs.forEach((el, i) => {
      el.classList.toggle('is-playing', i === index);
    });
    currentIndex = index;

    const url = librarySongs[index].dataset.spotifyUrl;
    if (url && iframe.src !== url) {
      iframe.style.opacity = '0';
      iframe.src = url;
      iframe.onload = function () {
        iframe.style.opacity = '1';
      };
    }
  }

  librarySongs.forEach((el, i) => {
    el.addEventListener('click', () => setActive(i));
  });
}
