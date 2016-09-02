<?php get_header(); ?>

	<div id="content">
	<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>
		
		<div class="post">
			<h1><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
			<?php if ( $post->post_excerpt ) : // If there is an explicitly defined excerpt ?>
			<div class="excerpt"><h2>Summary:</h2>
			<?php the_excerpt() ?></div>
			<?php endif; ?>
			<h3><b>Posted:</b> <?php the_time('F jS, Y') ?> <?php the_tags(' | <b>Keywords:</b> ', ', ', ''); ?> <?php if ( $user_ID ) : ?> | <b>Modify:</b> <?php edit_post_link(); ?> <?php endif; ?></h3>
			<?php the_content(); ?>
			<h2><?php comments_popup_link('There are no Comments yet, but you can make some &#187;', 'There is a comment, go and read it &#187;', 'There are a few comments, you can go and read them &#187;'); ?></h2>
			<hr/>
		</div>
		
		<?php comments_template(); ?>
		
		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>

	<?php else : ?>

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>

	<?php endif; ?>

	</div>
	
<?php get_sidebar(); ?>

<?php get_footer(); ?>