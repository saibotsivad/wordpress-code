<?php
/**
 *
 * Index (home) template for the theme
 *
 * @package WordPress
 * @subpackage THEME NAME
 */
?>

<?php get_header(); ?>

<?php if(have_posts()) : ?>
	<?php while(have_posts()) : the_post(); ?>


<!-- ==================== -->
<div class="post" <?php post_class(); ?>>
	<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<div class="content">
		<?php the_content(); ?>
	</div><!-- .content -->
	<p>Comments: <?php comments_number(); ?></p>
</div><!-- .post -->
<!-- ==================== -->


		<?php comments_template(); // Only displays on a single page/post ?>

	<?php endwhile; ?>
<?php else : ?>
	This displays if no posts are found.
<?php endif; ?>

<?php comments_template(); // Only displays on a single page/post ?>

<?php get_sidebar(); ?>

<?php include(TEMPLATEPATH . '/customfile.php'); //optional ?>

<?php get_footer(); ?>

<?php /* Post tags listed for convenience:
	the_ID, the_title, the_tags, the_meta, post_class, body_class, sticky_class, the_excerpt,
	the_content, the_title_rss, the_category, the_shortlink, wp_link_pages, posts_nav_link,
	next_post_link, next_posts_link, the_excerpt_rss, the_content_rss, next_image_link,
	the_category_rss, single_post_title, the_title_attribute, previous_post_link, previous_posts_link,
	previous_image_link, post_password_link
*/ ?>