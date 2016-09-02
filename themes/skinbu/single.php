<?php get_header(); ?>
		<!-- LEFTBAR -->
		<div id="leftbar">
		<?php while (have_posts()) : the_post(); ?>
			<div id="post">
				<h1><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
				<h2>By <span style="color:#0066FF"><?php the_author() ?>  <?php if(function_exists('the_views')) { the_views(); } ?></span></h2>
				<div id="postcontent"><p> <?php the_content(''); ?><br /><img style="float:left; margin-right:3px;" src="<?php bloginfo('template_directory'); ?>/images/tag.png" /> <?php the_tags('', ' , ', '');?> <br /> </p></div>
				<div id="links"><?php wp_link_pages('arguments'); ?> </div>
				<div id="tagsbar2">
					<span id="tbarelement"><img alt="categoria" src="<?php bloginfo('template_directory'); ?>/images/categoria.png" /><?php the_category(', '); ?></span>
					<span id="tbarelement"><img alt="commento" src="<?php bloginfo('template_directory'); ?>/images/comment.png" /><?php comments_popup_link('No Comments', '1 Comment', '% Comments'); ?></span>
					<span id="tbarelement"><img alt="data" src="<?php bloginfo('template_directory'); ?>/images/data.png" /><?php the_time('F jS, Y') ?></span>
				</div>
			</div>					
			<div id="authorbox"><h3>About... <?php the_author_posts_link(); ?></h3> <?php if (function_exists('get_avatar')) { echo get_avatar( get_the_author_email(), '80' ); }?><em>This author published  <?php the_author_posts(); ?> posts in this site.</em><br /> <?php the_author_description(); ?></div>
			<h1>Share</h1><div id="share"><?php if(function_exists('wp_print')) { print_link(); } ?><a href="http://www.facebook.com/share.php?u=<?php the_permalink() ?>&t=<?php the_title(); ?>"><img alt="Facebook"  src="<?php bloginfo('template_directory'); ?>/images/facebook.png"  /></a><a href="http://twitter.com/home?status=<?php the_title(); ?>-<?php the_permalink() ?>"><img alt="Twitter" height="32" src="<?php bloginfo('template_directory'); ?>/images/twitter.png" width="32" /></a><a href="mailto:?subject=<?php the_title() ?>&body=<?php the_permalink() ?>"><img alt="Email" height="32" src="<?php bloginfo('template_directory'); ?>/images/email.png" width="32" /></a><a href="https://skydrive.live.com/sharefavorite.aspx%2f.SharedFavorites??marklet=1&amp;url=<?php the_permalink() ?>&title=<?php the_title(); ?>"><img alt="Windows Live" height="32" src="<?php bloginfo('template_directory'); ?>/images/windows.png" width="32" /></a><a href="http://technorati.com/faves?add=<?php the_permalink() ?>"><img alt="Technorati" height="32" src="<?php bloginfo('template_directory'); ?>/images/technorati.png" width="32" /></a><a href="http://delicious.com/post?url=<?php the_permalink() ?>"><img alt="Delicious" height="32" src="<?php bloginfo('template_directory'); ?>/images/delicious.png" width="32" /></a><a href="http://digg.com/submit?phase=2&url=<?php the_permalink() ?>&title=<?php the_title(); ?>&bodytext=<?php the_permalink() ?>"><img alt="Digg" height="32" src="<?php bloginfo('template_directory'); ?>/images/digg.png" width="32" /></a><a href="http://www.stumbleupon.com/submit?url=<?php the_permalink() ?>"><img alt="Stumblepon" height="32" src="<?php bloginfo('template_directory'); ?>/images/stumbleupon.png" width="32" /></a><a href="http://www.myspace.com/Modules/PostTo/Pages/?u=<?php the_permalink() ?>&t=<?php the_title(); ?>"><img alt="Myspace" height="32" src="<?php bloginfo('template_directory'); ?>/images/myspace.png" width="32" /></a><a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php the_permalink() ?>&amp;title=<?php the_title(); ?>;&amp;summary="><img alt="Likedin" height="32" src="<?php bloginfo('template_directory'); ?>/images/linkedin.png" width="32" /></a></div>	

				<?php comments_template(); ?>
		<div id="links"><?php previous_post_link('%link', 'Previous post') ?><?php next_post_link('%link', ' - Next post') ?></div>
		<?php endwhile; ?>
		
		</div>
		<!-- END LEFTBAR -->
		
<?php get_sidebar(); ?>
<?php get_footer(); ?>
