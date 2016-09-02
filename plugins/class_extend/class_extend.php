<?php
/*
Plugin Name: Class Extend
Plugin URI: http://tobiaslabs.com
Description: Trying out extending classes for admin side.
Author: Tobias Davis
Version: 0.1
Author URI: http://davistobias.com
*/

// initialization
if ( is_admin() )
{
	require_once( 'include/admin.php' );
	$TL_ClassExtend = new TL_CE_Admin;
}
else
{
	$TL_ClassExtend = new TL_CE_Core;
}

// the core functions
class TL_CE_Core
{

	function __construct()
	{
		add_action( 'init', array( $this, 'Init' ) );
	}
	
	function Init()
	{
		register_post_type( 'sermon',
			array(
				'labels' => array(
					'name' => _x('Sermon', 'post type general name'),
					'singular_name' => _x('Sermon', 'post type singular name'),
					'add_new' => _x('Add New', 'sermon'),
					'add_new_item' => __('Add New Sermon'),
					'edit_item' => __('Edit Sermon'),
					'new_item' => __('New Sermon'),
					'view_item' => __('View Sermon'),
					'search_items' => __('Search Sermons'),
					'not_found' =>  __('No sermons found'),
					'not_found_in_trash' => __('No sermons found in Trash')
				),
				'public' => true,
				'show_ui' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'rewrite' => true,
				'menu_position' => 5,
				'supports' => array('title', 'editor')
			)
		);
		register_taxonomy( 'tlsp_preacher',
			array('sermon'),
			array(
				'hierarchical' => false,
				'labels' => array(
					'name' => _x( 'Preachers', 'taxonomy general name' ),
					'singular_name' => _x( 'Preacher', 'taxonomy singular name' ),
					'search_items' =>  __( 'Search Preachers' ),
					'all_items' => __( 'All Preachers' ),
					'edit_item' => __( 'Edit Preacher' ), 
					'update_item' => __( 'Update Preacher' ),
					'add_new_item' => __( 'Add New Preacher' ),
					'new_item_name' => __( 'New Preacher Name' ),
				),
				'show_ui' => false,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'preacher' ),
			)
		);
	}
}

?>