export function initFaq() {
  const faqItems = document.querySelectorAll('.faq__item');
  if (!faqItems.length) return;

  // Open first item by default
  const firstItem = faqItems[0];
  const firstAnswer = firstItem.querySelector('.faq__answer');
  firstItem.classList.add('active');
  firstAnswer.style.maxHeight = firstAnswer.scrollHeight + 'px';

  faqItems.forEach(item => {
    const question = item.querySelector('.faq__question');
    const answer = item.querySelector('.faq__answer');

    question.addEventListener('click', () => {
      const isActive = item.classList.contains('active');

      // Close all
      faqItems.forEach(i => {
        const a = i.querySelector('.faq__answer');
        i.classList.remove('active');
        a.style.maxHeight = null;
      });

      // Open clicked if it wasn't active
      if (!isActive) {
        item.classList.add('active');
        answer.style.maxHeight = answer.scrollHeight + 'px';
      }
    });
  });
}
