<?php get_header(); ?>

<div class="page-spacing">
  <div class="page">
    <div class="columnar simple-column">
      <?php
      while (have_posts()) : the_post();
        ?>
        <h1><?php the_title(); ?></h1>

        <?php if (has_post_thumbnail()) : ?>
          <div class="img-wrap">
            <?php the_post_thumbnail('full'); ?>
          </div>
        <?php endif; ?>

        <div class="floating">
          <?php the_content(); ?>
        </div>

        <?php
        the_tags('<div class="post-tags">', ', ', '</div>');

        if (comments_open() || get_comments_number()) :
          comments_template();
        endif;
        ?>
        <?php
      endwhile;
      ?>
    </div>
  </div>
</div>

<?php get_footer(); ?>
