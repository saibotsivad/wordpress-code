<?php
/*
Plugin Name: Classes
Plugin URI: http://tobiaslabs.com/
Description: Testing the method of classes for plugin design.
Version: 0.1
Author: Tobias
Author URI: http://tobiaslabs.com
*/

class TLSP
{

	// add actions
	public function __construct()
	{
		add_action( 'init', array( $this, 'Init' ) );
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		add_action( 'save_post', array( $this, 'SavePost' ) );
	}

	// inintialization options
	function Init()
	{
		// add custom post type
		$labels = array(
			'name' => _x('Books', 'post type general name'),
			'singular_name' => _x('Book', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Book'),
			'edit_item' => __('Edit Book'),
			'new_item' => __('New Book'),
			'view_item' => __('View Book'),
			'search_items' => __('Search Books'),
			'not_found' =>  __('No books found'),
			'not_found_in_trash' => __('No books found in Trash'), 
			'parent_item_colon' => '',
			'menu_name' => 'Books'
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true, 
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title')
		); 
		register_post_type('book',$args);		
		
	}
	
	// admin initialization options
	function AdminInit()
	{
	
		// add meta box to the custom post
		add_meta_box( 'tlsp_meta', 'Books Stuff', array( $this, 'MetaBox' ), 'book', 'normal' );
		
		// register an import method
		if( function_exists( 'register_importer' ) )
		{
			register_importer( 'tlsp_importer', 'Sermon Browser Import', 'Import sermons from the Sermon Browser plugin to the Sermon Posts plugin.', array( $this, 'ImportSermons' ) );
		}

	}
	
	// the custom post meta box
	function MetaBox()
	{
		
		// wordpress global
		global $post;

		// grab meta data, if it exists
		$tlsp_text = get_post_meta( $post->ID, 'tlsp_text', true );

		// use a nonce for verification
		wp_nonce_field( plugin_basename(__FILE__), 'tlsp_noncename' );
		
		// this is the html for the metabox
		$security = 1;
		include( 'metabox-html.php' );
		$security = 0;
		
	}
	
	// save the meta box data
	function SavePost()
	{
	
		// wordpress global
		global $post;
		
		// this first check is, I feel, a hack: why should SavePost run on page load?
		if ( isset( $_POST['tlsp_noncename'] ) )
		{
		
			// run the following checks:
			if (
				// validate the nonce
				( wp_verify_nonce( $_POST['tlsp_noncename'], plugin_basename(__FILE__) ) )
				// make sure this is not an autosave
				&& ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) == false )
				// check user permissions
				&& ( current_user_can( 'edit_post', $post_id ) )
				// check that the element exists
				&& ( isset ( $_POST['tlsp_text'] ) )
			)
			{
				
				// if there is data, update the meta field
				if ( $_POST['tlsp_text'] != '' )
				{
					update_post_meta( $post->ID, 'tlsp_text', $_POST['tlsp_text'] );
				}
				// otherwise delete the table row, for a cleaner database
				else
				{
					delete_post_meta( $post->ID, 'tlsp_text' );
				}
			
			}
			// if the checks fail, return an error
			else
			{
				// TODO: This error does not show up yet
				echo '<div id="message" class="error">Error on saving meta data!</div>';
			}
			
		}

	}
	
	// the import sermons page
	function ImportSermons()
	{
		
		// wordpress globals
		global $wpdb;
		
		// sermon browser variables
		$sb_options = unserialize( base64_decode( get_option( 'sermonbrowser_options' ) ) );
		
		// TODO: add security checks
	
		// either the user clicked from the menu, or they clicked within the import page (after changing settings, they click "go" or whatever)
		if ( isset ( $_POST['tlsp_import'] ) )
		{
			// import the file
			$security = 1;
			include ( 'import-functions.php' );
			$security = 0;
		}
		else
		{
			// display the options
			$security = 1;
			include ( 'import-html.php' );
			$security = 0;
		}
	
	}
	
}

// check that the version is high enough
// TODO: check which version is required, I think 3.0 for custom post types?
if ( get_bloginfo( 'version' ) >= '1.1.1' )
{
	$sermonposts = new TLSP;
}
else
{
	// return an error that they have too old a version of WordPress
}

?>
