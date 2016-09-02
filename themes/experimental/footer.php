<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
?>

<hr />
<div id="footer" role="contentinfo">
<!-- If you'd like to support WordPress, having the "powered by" link somewhere on your blog is the best way; it's our only promotion or advertising. -->
	<p>
		Powered by <a href="http://wordpress.org/">WordPress</a>, designed by <a href="http://davistobias.com/">Tobias Davis</a>.<br />
		<!-- <?php echo get_num_queries(); ?> queries. <?php timer_stop(1); ?> seconds. -->
		All content &#169; <?php the_time('Y'); ?> <?php bloginfo('name'); ?><br />
		<a href="<?php bloginfo('rss2_url'); ?>">Get the feed!</a></p>  
	</p>
</div>
</div>
		<?php wp_footer(); ?>
<!-- Designed by Tobias Davis: http://davistobias.com -->
</body>
</html>
