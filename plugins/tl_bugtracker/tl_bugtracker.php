<?php
/*
Plugin Name: TL Bug Tracker
Plugin URI: http://tobiaslabs.com/
Description: Track bugs, let users create bugs and feature requests, etc.
Version: 0.1
Author: Tobias Labs
Author URI: http://tobiaslabs.com/
*/

$BugTracker = new BugTracker;

class BugTracker
{

	function __construct()
	{
		add_action( 'init', array( $this, 'Init' ) );
	}
	
	function Init()
	{
		// register post type: bugtracker
		$args = array(
			'labels' => array(
				'name' => _x('Tracker', 'post type general name'),
				'singular_name' => _x('Bug', 'post type singular name'),
				'add_new' => _x('Add New', 'sermon_post'),
				'add_new_item' => __('Add New Bug'),
				'edit_item' => __('Edit Bug'),
				'new_item' => __('New Bug'),
				'view_item' => __('View Bug'),
				'search_items' => __('Search Bugs'),
				'not_found' =>  __('No bugs found'),
				'not_found_in_trash' => __('No bugs found in Trash')
			),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array( 'slug' => 'bugtracker' ),
			'menu_position' => 20,
			'supports' => array( 'title', 'editor'),
			'taxonomies' => array( 'bugtag' )
		);
		register_post_type( 'bugtracker' , $args );
		
		// register the taxonomy: bugtags
		register_taxonomy(
			'bugtag',
			array('bugtracker'),
			array(
				'hierarchical' => false,
				'labels' => array(
					'name' => _x( 'Bug Tags', 'taxonomy general name' ),
					'singular_name' => _x( 'Bug Tag', 'taxonomy singular name' ),
					'search_items' =>  __( 'Search Bug Tags' ),
					'all_items' => __( 'All Bug Tags' ),
					'edit_item' => __( 'Edit Bug Tag' ), 
					'update_item' => __( 'Update Bug Tag' ),
					'add_new_item' => __( 'Add New Bug Tag' ),
					'new_item_name' => __( 'New Bug Tag Name' ),
				),
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'bugtag' ),
			)
		);

	}


}

?>