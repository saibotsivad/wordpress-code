<?php
/* 
Plugin Name: Sermon Posts Deuce
Plugin URI: http://www.tobiaslabs.com/sermonposts/
Description: Manage sermon audio, documents, preachers, sermon catagories, and more! Uses built-in WordPress functionality! RSS! Podcasts! Exclamation points!
Version: 1.0
Author: Tobias Labs
Author URI: http://tobiaslabs.com
*/

// check that the version is high enough
if ( version_compare( get_bloginfo( 'version' ), '3.0.0' ) < 0 )
{
	wp_die( __( "Your version of WordPress is too old! You need version 3.0.0 or higher to install this plugin." ) );
}

// Initialization loads as little as possible.
if ( is_admin() )
{
	require_once( 'include/plugin/admin-core.php' );
	$TL_Sermons = new TL_Sermons_Admin;
}
else
{
	$TL_Sermons = new TL_Sermons_Core;
}

// The core visitor-side functionality is held held here
class TL_Sermons_Core
{

	// plugin information, for the admin side
	var $plugin_info = array();
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function __construct()
	{
		// set the plugin information, for activation and other options
		$this->plugin_info['plugin_location'] = __FILE__;
		
		// Initialization
		add_action( 'init', array( $this, 'Init' ) );
		add_action( 'after_setup_theme', array( $this, 'EnablePostThumbnails' ), '9999' );
	}
	
	/**
	 * Register custom post type (sermon) and taxonomies (preacher, service, series, sermon tag)
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function Init()
	{
	
		// register the new post type: sermon_post
		$args = array(
			'labels' => array(
				'name' => _x('Sermons', 'post type general name'),
				'singular_name' => _x('Sermon', 'post type singular name'),
				'add_new' => _x('Add New', 'sermon_post'),
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
			'rewrite' => array( 'slug' => 'sermon' ),
			'menu_position' => 5,
			'menu_icon' => plugins_url( '/include/media/bible-icon-16x12.png', $this->plugin_info['plugin_location'] ),
			'map_meta_cap' => true,
			'supports' => array('title','thumbnail','editor','comments','custom-fields'),
			'taxonomies' => array('tlsp_preacher','tlsp_series','tlsp_service','tlsp_tag')
			
		);
		if ( is_admin() )
		{
			$args['register_meta_box_cb'] = array( $this, 'MetaboxSetup' );
		}
		// TODO: set it so the URL shows as 'sermons' instead, or even customizable perhaps?
		register_post_type( 'sermon_post' , $args );

		// add taxonomy: preacher
		register_taxonomy(
			'tlsp_preacher',
			array('sermon_post'),
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
		
		// add taxonomy: series
		register_taxonomy(
			'tlsp_series',
			array('sermon_post'),
			array(
				'hierarchical' => false,
				'labels' => array(
					'name' => _x( 'Sermon Series', 'taxonomy general name' ),
					'singular_name' => _x( 'Sermon Series', 'taxonomy singular name' ),
					'search_items' =>  __( 'Search Sermon Series' ),
					'all_items' => __( 'All Sermon Series' ),
					'edit_item' => __( 'Edit Sermon Series' ), 
					'update_item' => __( 'Update Sermon Series' ),
					'add_new_item' => __( 'Add New Sermon Series' ),
					'new_item_name' => __( 'New Sermon Series Name' ),
				),
				'show_ui' => false,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'series' ),
			)
		);
		
		// add taxonomy: service
		register_taxonomy(
			'tlsp_service',
			array('sermon_post'),
			array(
				'hierarchical' => false,
				'labels' => array(
					'name' => _x( 'Services', 'taxonomy general name' ),
					'singular_name' => _x( 'Service', 'taxonomy singular name' ),
					'search_items' =>  __( 'Search Services' ),
					'all_items' => __( 'All Services' ),
					'edit_item' => __( 'Edit Service' ), 
					'update_item' => __( 'Update Service' ),
					'add_new_item' => __( 'Add New Service' ),
					'new_item_name' => __( 'New Service Name' ),
				),
				'show_ui' => false,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'service' ),
			)
		);
		
		// add category sermontag
		register_taxonomy(
			'tlsp_tag',
			array('sermon_post'),
			array(
				'hierarchical' => false,
				'labels' => array(
					'name' => _x( 'Sermon Tags', 'taxonomy general name' ),
					'singular_name' => _x( 'Sermon Tag', 'taxonomy singular name' ),
					'search_items' =>  __( 'Search Sermon Tags' ),
					'all_items' => __( 'All Sermon Tags' ),
					'edit_item' => __( 'Edit Sermon Tag' ), 
					'update_item' => __( 'Update Sermon Tag' ),
					'add_new_item' => __( 'Add New Sermon Tag' ),
					'new_item_name' => __( 'New Sermon Tag' ),
				),
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'sermontag' ),
			)
		);
	
	}
	// EOF: Init
	
	/**
	 * Enable post-thumbnail for sermon_post and attachments, even if the theme does not support it.
	 * Version 3.1 changes the way post thumbnail support is added.
	 *
	 * @since 0.7
	 * @author Tobias Davis
	*/
	function EnablePostThumbnails()
	{
	
		if ( get_bloginfo( 'version' ) >= '3.1' )
		{
			// make sure thumbnails work for sermon_post post type
			$thumbnails = get_theme_support( 'post-thumbnails' );
			if ( $thumbnails === false ) $thumbnails = array ();
			if ( is_array( $thumbnails ) )
			{
				$thumbnails[] = 'sermon_post';
				$thumbnails[] = 'post';
				$thumbnails[] = 'attachment';
				add_theme_support( 'post-thumbnails', $thumbnails );
			}
		}
		else
		{
			// required global variable
			global $_wp_theme_features;
			
			// if the post thumbnails are not set, we'll enable support to sermon_post posts
			if( !isset( $_wp_theme_features['post-thumbnails'] ) )
			{
				$_wp_theme_features['post-thumbnails'] = array( array( 'sermon_post', 'post' ) );
			}
			// if they are set, we'll add sermon_post posts to the array
			elseif ( is_array( $_wp_theme_features['post-thumbnails'] ) )
			{
				$_wp_theme_features['post-thumbnails'][0][] = 'sermon_post';
			}
		}
	
	}
	// EOF: EnablePostThumbnails

} //EOC: TL_Sermons_Core

?>