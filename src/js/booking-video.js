export function initBookingVideo() {
  const section = document.querySelector('.booking-hero');
  if (!section) return;

  const video = section.querySelector('.booking-hero__video');
  if (!video) return;

  let progress = 0;
  let targetProgress = 0;

  function update() {
    const videoRect = video.getBoundingClientRect();
    const startPoint = window.innerHeight * 0.9;
    const endPoint = 100;
    const currentTop = videoRect.top;

    if (currentTop >= startPoint) {
      targetProgress = 0;
    } else if (currentTop <= endPoint) {
      targetProgress = 1;
    } else {
      targetProgress = 1 - (currentTop - endPoint) / (startPoint - endPoint);
    }

    progress += (targetProgress - progress) * 0.15;

    const width = 70 + (progress * 45);
    const borderRadius = 40 - (progress * 30);

    video.style.width = `${width}%`;
    video.style.borderRadius = `${borderRadius}px`;

    requestAnimationFrame(update);
  }

  requestAnimationFrame(update);
}
