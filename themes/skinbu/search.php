<?php get_header(); ?>
<div id="leftbar">
	<h1>Search Results</h1>
	<?php if (have_posts()) : ?>
	<?php while (have_posts()) : the_post(); ?>
			<div id="post">
				<h1><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
				<h2>By <span style="color:#0066FF"><?php the_author() ?>  <?php if(function_exists('the_views')) { the_views(); } ?></span></h2>
				<div id="postcontent"><p> <?php the_content(''); ?> </p></div>
				<div id="tagsbar">
					<span id="tbarelement"><img alt="categoria" src="<?php bloginfo('template_directory'); ?>/images/categoria.png" /><?php the_category(', '); ?></span>
					<span id="tbarelement"><img alt="commento" src="<?php bloginfo('template_directory'); ?>/images/comment.png" /><?php comments_popup_link('No comments', '1 Comment', '% Comments'); ?></span>
					<span id="tbarelement"><img alt="data" src="<?php bloginfo('template_directory'); ?>/images/data.png" /><?php the_time('F jS, Y') ?></span>
				</div>
				<div id="readmore"><a href="<?php the_permalink() ?>#more-<?php the_ID(); ?>"><img alt="Leggi tutto" src="<? bloginfo('template_directory'); ?>/images/readmore1.png" onmouseover="this.src='<? bloginfo('template_directory'); ?>/images/readmore2.png'" onmouseout="this.src='<? bloginfo('template_directory'); ?>/images/readmore1.png'" /></a></div>
			</div>
		<?php endwhile; ?>
	<div id="links"><?php next_posts_link('Previous page') ?><?php previous_posts_link('       Next Page') ?></div>
	
		<?php else : ?>
	<div id="links">No posts found. Try a different search?</div>
		<?php endif; ?>
</div>

	<?php get_sidebar(); ?>
	<?php get_footer(); ?>
