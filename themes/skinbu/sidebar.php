		<!-- SIDEBAR -->
		<div id="sidebar" >
			<?php include (TEMPLATEPATH . '/searchform.php'); ?>
			<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
			<div id="box" style="margin-left:auto;margin-right:auto;"><h2>Categories</h2><ul><?php wp_list_categories('title_li='); ?></ul></div>
			<?php endif; ?>
		</div>
		<!-- END SIDEBAR -->
