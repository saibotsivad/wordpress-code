<?php get_header(); ?>

		<div id="wrapper">
			<div id="box1wrap">
				<div id="box1">
 					<div id="box1pad">
						<?php get_template_part( 'customfile', 'content' ); /* the main 420x800 box */ ?>
 					</div>
				</div>
			</div>
			<div id="box2wrap">
				<div id="box2">
					<div id="box2pad">
						<?php get_template_part( 'customfile', 'bigbox' ); /* the single 300x820 box */ ?>
					</div>
				</div>
			</div>
			
			<div class="box3wrap">
				<div class="box3">
					<div class="box3pad">
						<?php get_template_part( 'customfile', 'smallboxone' ); /* one of the 200x400 boxes */ ?>
					</div>
				</div>
			</div>
			<div class="box3wrap">
				<div class="box3">
					<div class="box3pad">
						<?php get_template_part( 'customfile', 'smallboxtwo' ); /* the other 200x400 box */ ?>
					</div>
				</div>
			</div>
			
		</div>

<?php get_footer(); ?>