<?php get_header(); ?>
		<!-- LEFTBAR -->
		<div id="leftbar">
		<?php while (have_posts()) : the_post(); ?>
			<div id="post">
				<h1><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
				<h2>By <span style="color:#0066FF"><?php the_author() ?>  <?php if(function_exists('the_views')) { the_views(); } ?></span></h2>
				<p> <?php the_content(''); ?> </p>
			</div>		
			<?php comments_template(); ?>
		<div id="links"><?php wp_link_pages(); ?></div>
		<?php endwhile; ?>
		
		</div>
		<!-- END LEFTBAR -->
		
<?php get_sidebar(); ?>
<?php get_footer(); ?>
