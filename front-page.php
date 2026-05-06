<?php get_header(); ?>

<main>
  <?php get_template_part('template-parts/banner'); ?>

  <?php
  // ACF structure: flex-content (flexible_content)
  //   → layout: content_block (clone display:seamless → style + content)
  //     → content (nested flexible_content) → actual layout components
  if (have_rows('flex-content')) :
    while (have_rows('flex-content')) : the_row();
      $style = get_sub_field('style') ?: 'light';
      ?>
      <div class="<?php echo esc_attr($style); ?>-bg">
        <div class="columnar">
          <?php
          if (have_rows('content')) :
            while (have_rows('content')) : the_row();
              $layout = get_row_layout();

              switch ($layout) {
                case 'headline_content':
                  get_template_part('template-parts/headline-content');
                  break;
                case 'book_request':
                  get_template_part('template-parts/booking');
                  break;
                case 'schedule':
                  get_template_part('template-parts/schedule');
                  break;
                case 'videos':
                  get_template_part('template-parts/media-videos');
                  break;
                case 'player':
                  get_template_part('template-parts/player');
                  break;
                case 'contact':
                  get_template_part('template-parts/contact-inline');
                  break;
                case 'songs':
                  get_template_part('template-parts/songs');
                  break;
                case 'faq':
                  get_template_part('template-parts/faq');
                  break;
              }
            endwhile;
          endif;
          ?>
        </div>
      </div>
      <?php
    endwhile;
  endif;
  ?>

  <?php get_template_part('template-parts/instagram'); ?>
</main>

<?php get_footer(); ?>
