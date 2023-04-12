<?php
/**
 * Template Name: Front Page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();
?>
<main id="primary" class="site-main">
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
      
    </header>

    <div class="entry-content">
      <?php the_content(); ?>
    </div>
  </article>
</main>

<?php get_footer(); ?>