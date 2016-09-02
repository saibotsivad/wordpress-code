<?php
/* 
Plugin Name: Example - Making a Widget
Plugin URI: http://www.tobiaslabs.com/
Description: Adding a widget as a tutorial. It displays a list of posts of a certain category.
Version: 1.0
Author: Tobias Davis
Author URI: http://davistobias.com

	I found this example online, modified it, and added copious comments.
	Found here: http://www.lonewolfdesigns.co.uk/create-wordpress-widgets/

	This doesn't show how to add controls to the widgit, only the bare bones of what
	it takes to make a widgit display in the Widgit menu.

*/

add_action(				// register an action with Wordpress
	"plugins_loaded",	// at this point,
	"display_cat_init"	// run this function
);

function display_cat_init(){	// wrap the registering inside a function so Wordpress knows how to handle it
	register_sidebar_widget(	// register the widget
		'List Categories',		// this is the title that shows up on the Widget page
		'widget_display_cat'	// this is the function that displays the actual widget
	);
}

function widget_display_cat(){	// magic happens

/*	This is where magic happens. When a user pulls up your website, if this Widgit is active, it will
	run everything that it finds in here and put any output within the HTML element that this sidebar
	is placed. Usually you would grab data from the database options table and use it to display
	something. In this case I'm going to display the excerpt of a random post from a specific category.
*/

	// within a theme you wouldn't normally need to query_posts, but because The Loop probably already
	// ran, I'll be explicit. (Also, I want a custom query.)
	// showposts=#		the number of posts to display
	// cat=#			the ID of the category to display
	// orderby=rand		randmoize the order
	query_posts('showposts=1&cat=1&orderby=rand');

	// now we'll display the HTML by running The Loop *just like normal*
	?>
	<li class="widget">
		<h2>Random Post Excerpt:</h2>
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<?php	// everything inside here is processed just like a normal theme loop ?>
		<h3><?php the_title() ?></p>
		<p><?php the_excerpt() ?></p>
		<?php endwhile; endif; // end of The Loop ?>
	</li>
	<?php

	// you should always reset the query explicitly if you used it
	wp_reset_query(); 

} // end of magic

?>