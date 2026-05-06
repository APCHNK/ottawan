<?php get_header(); ?>

<?php
// Find player with under_title flag
$under_title_player = null;
if (have_rows('page_sections')) :
  while (have_rows('page_sections')) : the_row();
    if (get_row_layout() === 'player' && get_sub_field('under_title')) {
      $under_title_player = get_row_index();
    }
  endwhile;
  reset_rows();
endif;
?>

<div class="page-spacing">
  <div class="page">
    <div class="columnar simple-column">
      <?php
      while (have_posts()) : the_post();
        ?>
        <?php if (get_the_title()) : ?>
          <h1><?php the_title(); ?></h1>
        <?php endif; ?>

        <?php
        // Render under-title player here
        if ($under_title_player && have_rows('page_sections')) :
          while (have_rows('page_sections')) : the_row();
            if (get_row_index() === $under_title_player) {
              get_template_part('template-parts/player');
            }
          endwhile;
          reset_rows();
        endif;
        ?>

        <?php if (has_post_thumbnail()) : ?>
          <div class="img-wrap">
            <?php the_post_thumbnail('full'); ?>
          </div>
        <?php endif; ?>

        <div class="floating">
          <?php the_content(); ?>
        </div>
        <?php
      endwhile;
      ?>
    </div>
  </div>
</div>

<?php
if (have_rows('page_sections')) :
  while (have_rows('page_sections')) : the_row();
    $layout = get_row_layout();

    // Skip under-title player, already rendered above
    if ($layout === 'player' && get_row_index() === $under_title_player) continue;
    ?>
    <div class="light-bg">
      <div class="columnar">
        <?php
        switch ($layout) {
          case 'headline_content':
            get_template_part('template-parts/headline-content');
            break;
          case 'player':
            get_template_part('template-parts/player');
            break;
          case 'videos':
            get_template_part('template-parts/media-videos');
            break;
          case 'schedule':
            get_template_part('template-parts/schedule');
            break;
          case 'book_request':
            get_template_part('template-parts/booking');
            break;
          case 'songs':
            get_template_part('template-parts/songs');
            break;
          case 'faq':
            get_template_part('template-parts/faq');
            break;
        }
        ?>
      </div>
    </div>
    <?php
  endwhile;
endif;
?>

<?php get_footer(); ?>
