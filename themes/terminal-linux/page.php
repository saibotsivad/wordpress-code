<?php get_header(); ?>

	<div id="content">
	<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>
		
		<div class="post">
			<h1><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
			<?php the_content('Read the rest of this entry &raquo;'); ?>
			 <hr/>
		</div>
		
		<?php endwhile; ?>

	<?php else : ?>

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>

	<?php endif; ?>

	</div>
	
<?php get_sidebar(); ?>

<?php get_footer(); ?>