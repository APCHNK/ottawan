<?php get_header(); ?>

<div class="page-spacing">
  <div class="page">
    <div class="columnar simple-column">
      <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
          <article>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <span><?php echo get_the_date('j F Y'); ?></span>
            <?php the_excerpt(); ?>
          </article>
        <?php endwhile; ?>

        <div class="pagination">
          <?php the_posts_pagination(); ?>
        </div>
      <?php else : ?>
        <p>No posts found.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php get_footer(); ?>
