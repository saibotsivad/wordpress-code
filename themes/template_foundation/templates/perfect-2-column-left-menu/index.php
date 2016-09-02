		<?php get_header(); ?>

		<div class="colmask leftmenu">
			<div class="colleft">
				<div class="col1">
					<?php get_sidebar(); ?>
				</div>
				<div class="col2">
					<?php get_template_part( 'customfile', 'content' ); ?>
				</div>
			</div>
		</div>

		<?php get_footer(); ?>