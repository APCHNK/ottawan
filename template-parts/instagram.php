<?php
$photos = theme_get_instagram_photos(8);
if (empty($photos)) return;

$insta = get_field('instagram', 'option');
$title = $insta['title'] ?? 'Social Media';
$btn_text = $insta['btn_text'] ?? 'View more on Instagram';
$url = $insta['url'] ?? '#';
$title_as_link = !empty($insta['title_as_link']) && !empty($insta['url']);
?>
<div class="white-bg spacing-lg">
  <div class="columnar">
    <div class="instagram-section">
      <h2 class="reveal reveal--up<?php echo $title_as_link ? ' instagram-title--with-icon' : ''; ?>">
        <?php if ($title_as_link) : ?>
          <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
            <svg class="instagram-title__icon" width="126" height="126" viewBox="0 0 126 126" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <g>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M39.375 0H86.625C108.368 0 126 17.6321 126 39.375V86.625C126 108.368 108.368 126 86.625 126H39.375C17.6321 126 0 108.368 0 86.625V39.375C0 17.6321 17.6321 0 39.375 0ZM86.625 114.188C101.824 114.188 114.188 101.824 114.188 86.625V39.375C114.188 24.1763 101.824 11.8125 86.625 11.8125H39.375C24.1763 11.8125 11.8125 24.1763 11.8125 39.375V86.625C11.8125 101.824 24.1763 114.188 39.375 114.188H86.625Z" fill="currentColor"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M31.5 63C31.5 45.6041 45.6041 31.5 63 31.5C80.3959 31.5 94.5 45.6041 94.5 63C94.5 80.3959 80.3959 94.5 63 94.5C45.6041 94.5 31.5 80.3959 31.5 63ZM43.3125 63C43.3125 73.8517 52.1483 82.6875 63 82.6875C73.8517 82.6875 82.6875 73.8517 82.6875 63C82.6875 52.1404 73.8517 43.3125 63 43.3125C52.1483 43.3125 43.3125 52.1404 43.3125 63Z" fill="currentColor"/>
                <path d="M96.8611 33.3337C99.1792 33.3337 101.058 31.4545 101.058 29.1363C101.058 26.8182 99.1792 24.939 96.8611 24.939C94.543 24.939 92.6637 26.8182 92.6637 29.1363C92.6637 31.4545 94.543 33.3337 96.8611 33.3337Z" fill="currentColor"/>
              </g>
            </svg>
            <span><?php echo esc_html($title); ?></span>
          </a>
        <?php else : ?>
          <?php echo esc_html($title); ?>
        <?php endif; ?>
      </h2>
      <div class="instagram-grid">
        <?php foreach ($photos as $index => $photo) : ?>
          <a href="<?php echo esc_url($photo['link']); ?>" target="_blank" rel="noopener noreferrer" class="instagram-item reveal reveal--up" data-reveal-delay="<?php echo ($index * 80); ?>">
            <img src="<?php echo esc_url($photo['url']); ?>" alt="Instagram" loading="lazy" decoding="async" width="400" height="400">
          </a>
        <?php endforeach; ?>
      </div>
      <?php if ($url && $btn_text) : ?>
        <div class="instagram-cta">
          <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="button button--outline">
            <?php echo esc_html($btn_text); ?>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
