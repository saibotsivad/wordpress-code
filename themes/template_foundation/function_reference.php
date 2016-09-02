<?php
/*
These same functions can obviously be found on the Wordpress site, but I wanted a reference list of common ones.
*/

// INCLUDE TAGS
get_header();		// header.php
get_footer();		// footer.php
get_sidebar();		// sidebar.php
get_search_form();	// searchform.php
get_template_part( $slug, $name );	// {slug}-{name}.php
get_template_part( $slug );			// {slug}.php

// BLOG INFO TAG
bloginfo( $string );
// Valid strings: name, description, admin_email, url, stylesheet_directory, stylesheet_url,
// template_directory, template_url, atom_url, rss2_url, rss_url, charset, html_type, version

// LISTS Check out the Wordpress page for wasy to modify output: http://codex.wordpress.org/Template_Tags#Lists_.26_Dropdown_tags
wp_dropdown_categories(); wp_dropdown_pages(); wp_dropdown_users(); wp_get_archives();
wp_list_authors(); wp_list_bookmarks(); wp_list_categories(); wp_list_comments(); wp_list_pages();

// LOGIN TAGS:
is_user_logged_in(); wp_login_url(); wp_loginout(); wp_logout(); wp_register();

// THE LOOP
if( have_posts() ) : while( have_posts() ) : the_post();
//POST CONTENT
endwhile; else :
//THERE WERE NO POSTS
endif;
// END OF LOOP

// POST TAGS (inside the loop)
the_title(); // the_title( $HTMLbefore, $HTMLafter ); both default to ''
the_excerpt(); // no options to pass
the_category(); // the_category( $HTMLseparator, $parents ); $parents can be 'multiple' or 'single'
the_shortlink(); // the_shortlink( $DisplayText, $TooltipText, $HTMLbefore, $HTMLafter );
the_tags(); // the_tags( $HTMLbefore, $HTMLseparator, $HTMLafter
the_content(); // the_content( $MoreLinkText );
the_author();
the_author_link();
the_author_posts(); // display integer number of posts the authoer has made
the_date();
next_post_link(); // Displayed in single.php or equivalent. See wordpress for variables: http://codex.wordpress.org/Function_Reference/next_post_link
next_posts_link(); // Displayed in archive.php or equivalent list. See wordpress for vars: http://codex.wordpress.org/Function_Reference/next_posts_link
// post thumbnail ("featured image") the array integers are array(width,height)
if( has_post_thumbnail() ) { the_post_thumbnail( array( 140, 100 ), array( 'class' => 'post' ) ); }

// MENU
wp_nav_menu( $args ); // a good default is $args = array( 'menu' => 'Unique Menu Name' ) but there are lots of options

// TAXONOMY
term_description();
single_term_title();
get_the_term_list();
the_terms();
the_taxonomies();


?>