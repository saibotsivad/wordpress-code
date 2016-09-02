<?php get_header(); ?>

<div id="mask">
	<div id="colmid">
		<div id="colright">
			<div id="col1wrap">
				<div id="col1">
					<?php get_template_part( 'customfile', 'leftcolumn' ); ?>
				</div>
			</div>
			<div id="col2wrap">
				<div id="col2">
					<?php get_template_part( 'customfile', 'centercolumn' ); ?>
				</div>
			</div>
			<div id="col3">
				<?php get_template_part( 'customfile', 'rightcolumn' ); ?>
			</div>
		</div>
	</div>
</div>
	
<?php get_footer(); ?>