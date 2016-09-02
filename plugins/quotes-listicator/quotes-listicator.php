<?php
/* 
Plugin Name: Quotes Listicator
Plugin URI: http://www.tobiaslabs.com/
Description: Display and rotate random quotes and words everywhere on your blog. Based heavily on "Stray Random Quotes", updated for Wordpress 3.x and PHP 5.x
Version: 1.0
Author: Tobias Davis
Author URI: http://davistobias.com
*/

// Require the functions file, of course
include (WP_PLUGIN_DIR . '/quotes-listicator/shortcode.php');

// Plugin activation options
function quoteslist_install(){

	// This is the database
	$data = array(
		'prepend' => '<li class="widget">',
		'content' => '<p>[quote_content]</p><span class="quotesnotes">[quote_note]</span><p>[quote_author], <em>[quote_source]</em>, [quote_link]link[/quote_link]</p>',
		'append' => '</li>',
		'css' => '',
		'defaulthtml' => array( // This is held in case the user wants to revert back to the default
			'prepend' => '<li class="widget">',
			'content' => '<p>[quote_content]</p><span class="quotesnotes">[quote_note]</span><p>[quote_author], <em>[quote_source]</em>, [quote_link]link[/quote_link]</p>',
			'append' => '</li>',
			'css' => ''
		)
	);

	// Add database option field (if it exists already, it will be overwritten)
	update_option('plugin_quoteslist_options', $data, '', 'yes');
}
register_activation_hook(__FILE__, 'quoteslist_install');

// Plugin deactivation options
function quoteslist_uninstall(){
	// Delete database option field
	delete_option('plugin_quoteslist_options');
};
register_deactivation_hook(__FILE__, 'quoteslist_uninstall');

// Register a post type: quote
function vid_post_reg(){
	$args = array(
		'labels' => array(
			'name' => _x('Quotes', 'post type general name'),
			'singular_name' => _x('Quote', 'post type singular name'),
			'add_new' => _x('Add New', 'video'),
			'add_new_item' => __('Add New Quote'),
			'edit_item' => __('Edit Quote'),
			'new_item' => __('New Quote'),
			'view_item' => __('View Quote'),
			'search_items' => __('Search Quotes'),
			'not_found' =>  __('No quotes found'),
			'not_found_in_trash' => __('No quotes found in Trash')
		),
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'rewrite' => true,
		'menu_position' => 5,
		'supports' => array('editor')
	);
	register_post_type( 'quote' , $args );
}
add_action('init', 'vid_post_reg');

// Register taxonomies: quote_author, quote_category
function quotelist_tax_reg(){
	register_taxonomy(
		'quote_author',
		'quote',
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Authors', 'taxonomy general name' ),
				'singular_name' => _x( 'Author', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Authors' ),
				'all_items' => __( 'All Authors' ),
				'edit_item' => __( 'Edit Authors' ), 
				'update_item' => __( 'Update Author' ),
				'add_new_item' => __( 'Add New Author' ),
				'new_item_name' => __( 'New Author Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'quote_author' ),
		)
	);
	register_taxonomy(
		'quote_category',
		'quote',
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Categories', 'taxonomy general name' ),
				'singular_name' => _x( 'Category', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Categories' ),
				'all_items' => __( 'All Categories' ),
				'edit_item' => __( 'Edit Categories' ), 
				'update_item' => __( 'Update Category' ),
				'add_new_item' => __( 'Add New Category' ),
				'new_item_name' => __( 'New Category Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'quote_category' ),
		)
	);
};
add_action('init', 'quotelist_tax_reg');

// Register a meta field for the source and for extra notes: quote_source, quote_notes
function quoteslist_meta(){
	add_meta_box("quoteInfo-meta", "Quote Extras", "quotelist_options", "quote", "normal", "low");
}
function quotelist_options(){
	global $post;

	// Use a nonce field to verify data entry
	echo '<input type="hidden" name="quotelist_noncename" id="quotelist_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';

	$custom = get_post_custom($post->ID);
	$note = $custom['quote_note'][0];
	$source = $custom['quote_source'][0];
	$link = $custom['quote_link'][0];
	?>
	<table class="form-table">
		<tr>
			<th style="text-align:right;" scope="row">Note:</th>
			<td><input type="text" size="45" name="quote_note" value="<?php echo $note; ?>" /></td>
		</tr>
		<tr>
			<th style="text-align:right;" scope="row">Source:</th>
			<td><input type="text" size="45" name="quote_source" value="<?php echo $source; ?>" /></td>
		</tr>
		<tr>
			<th style="text-align:right;" scope="row">Link:</th>
			<td><input type="text" size="45" name="quote_link" value="<?php echo $link; ?>" /></td>
		</tr>
	</table>
	<?php
}
add_action('admin_init', 'quoteslist_meta');

// Save the data when you hit publish/update on a quote or the settings page
function save_quoteinfo(){
	global $post;

	// Verify the nonce key
	if ( !wp_verify_nonce( $_POST['quotelist_noncename'], plugin_basename(__FILE__) )) { return $post->ID; };

	// Verify the user has proper priveleges
	if ( 'page' == $_POST['post_type'] ) { if ( !current_user_can( 'edit_page', $post->ID )) { return $post->ID; } }
	else { if ( !current_user_can( 'edit_post', $post->ID )) { return $post->ID; } };

	update_post_meta($post->ID, 'quote_note', $_POST['quote_note']);
	update_post_meta($post->ID, 'quote_source', $_POST['quote_source']);
	update_post_meta($post->ID, 'quote_link', $_POST['quote_link']);

}
add_action('save_post', 'save_quoteinfo');

// Change how the quotes are listed
function quotelist_cols($columns){
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Useless Title", // If I comment this line out, the title goes away but the edit/delete option also disappears
			"quote" => "Quote",
			"quoteauthor" => "Author",
		);

		return $columns;
}
function quotelist_customcols($column){
		global $post;
		switch ($column)
		{
			case "quote":
				the_content();
				break;
			case "quoteauthor":
				echo get_the_term_list($post->ID, 'quote_author', '', ', ','');
				break;
		}
}
add_filter("manage_edit-quote_columns", "quotelist_cols");
add_action("manage_posts_custom_column",  "quotelist_customcols");

// Instead of making a widgit, I'll just make Wordpress process shortcodes in the widgits
add_filter('widget_text', 'do_shortcode');

// Add a settings page and a help page
function quotelist_addmenu(){
	add_options_page('Quote Listicator', 'Quote Listicator', 'manage_options', 'quote-listicator-settings', 'quotelist_settings');
	// add a help page to display at the bottom of the "Quotes" menu
}
function quotelist_settings(){

	// Does user have priveleges?
	if( !current_user_can('manage_options') ) wp_die( __('You do not have sufficient priveleges to access this page.') );
	
	// Grab the plugin options from the database
	$data = get_option('plugin_quoteslist_options');

	// If the user posted ...
	if( isset($_POST['quotelist-settings-hidden']) && ($_POST['quotelist-settings-hidden'] == 'Y') ) {

		$ok = false;

		// If the user clicked on the Save button
		if( isset($_POST['quotelist-options-save']) ){

			// You should do input validation here, it's not cleaning itself correctly.
			$data['prepend'] = stripslashes(utf8_encode($_POST['quotelist-prepend']));
			$data['content'] = stripslashes(utf8_encode($_POST['quotelist-loop']));
			$data['append'] = stripslashes(utf8_encode($_POST['quotelist-append']));
			$data['css'] = stripslashes(utf8_encode($_POST['quotelist-css']));
			// Update the HTML options
			update_option('plugin_quoteslist_options', $data);
			$ok = true;

		} elseif( isset($_POST['quotelist-options-default']) ){

			// reset the fields
			$data['prepend'] = $data['defaulthtml']['prepend'];
			$data['content'] = $data['defaulthtml']['content'];
			$data['append'] = $data['defaulthtml']['append'];
			$data['css'] = $data['defaulthtml']['css'];
			update_option('plugin_quoteslist_options', $data);
			$ok = true;
		}

		// Print a response
		if( $ok == true ) echo '<div id="message" class="updated fade"><p>Options saved.</p></div>';
		else echo '<div id="message" class="error fade"><p>Failed to save options.<p></div>';
	}

	// Print out the page
	?>
	<div class="wrap">
	<form name="quotelist-settings-form" method="post" action="">
		<input type="hidden" name="quotelist-settings-hidden" value="Y" />
		<h2>Quote Listicator Options</h2>
		<p>You can change the settings for the HTML that go before the quote loop</p>
		<div class="form-field">
			<label for="quotelist-prepend"><h3>HTML printed before the Quote Loop</h3></label>
			<textarea name="quotelist-prepend" id="quotelist-prepend" rows="5" cols="55" value=""><?php echo $data['prepend']; ?></textarea>
		</div>
		<div class="form-field">
			<label for="quotelist-loop"><h3>The Quote Loop</h3></label>
			<ul>
				<li> Available shortcodes (no options): <strong>[quote_content], [quote_author], [quote_category], [quote_note]</strong></li>
				<li><strong>[quote_source]</strong> If there is a link attributed to the quote, you can say <strong>[quote_source makelink=true]</strong> to wrap the source in a link. Default is false.</li>
				<li><strong>[quote_link]</strong> Used by itself it prints the link address unformatted, but you can use <strong>[quote_link]</strong>Link<strong>[/quote_link]</strong> and it will make 'Link' a link.</li>
			</ul>
			<textarea name="quotelist-loop" id="quotelist-loop" rows="15" cols="55" value=""><?php echo $data['content']; ?></textarea>
		</div>
		<div class="form-field">
			<label for="quotelist-append"><h3>HTML printed after the Quote Loop</h3></label>
			<textarea name="quotelist-append" id="quotelist-append" rows="5" cols="55" value=""><?php echo $data['append']; ?></textarea>
		</div>
		<div class="form-field">
			<label for="quotelist-css"><h3>CSS Styling for the Quotes</h3></label>
			<textarea name="quotelist-css" id="quotelist-css" rows="5" cols="55" value=""><?php echo $data['css']; ?></textarea>
		</div>
		<br />
		<input class="button-primary" type="submit" name="quotelist-options-save" value="Save Changes" id="submitbutton" />
		<input class="button-secondary" type="submit" name="quotelist-options-default" value="Restore Default" />
		<br /><br />
	</form>
	</div>
	<?php
}
add_action('admin_menu', 'quotelist_addmenu');


?>