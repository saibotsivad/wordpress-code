<?php
/*
Plugin Name: Simple Custom post
Plugin URI: http://tobiaslabs.com/
Description: Testing the thickbox popup routine
Version: 1.0
Author: Tobias Labs
Author URI: http://tobiaslabs.com/
*/

$SimpleCustomPost = new SimpleCustomPost;

class SimpleCustomPost
{

	function __construct()
	{
		add_action( 'init', array( $this, 'Init' ) );
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		//add_filter( 'type_url_form_file', array( $this, 'WTFhack' ) );
	}
	
	function Init()
	{
	
		// the basic custom posts stuff
		$args = array(
			'label' => 'Custom Post',
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => true,
			'menu_position' => 5,
			'supports' => array('title','editor')
		);
		register_post_type( 'my_custom_post' , $args );
	
	}
	
	function AdminInit()
	{
		// just adding a metabox so we can demonstrate the link without the editor
		add_meta_box(
			'custom_metabox_id',
			'Sweet box, dude!',
			array( $this, 'MetaBox' ),
			'my_custom_post',
			'normal',
			'low'
		);
		// if you don't have supports include 'editor', you'll need to enqueue thickbox
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
	}
	
	// all we put in here is the link
	function MetaBox()
	{
		global $post;
		$link = get_bloginfo( 'wpurl' ).'/wp-admin/media-upload.php?post_id='.$post->ID.'&TB_iframe=1&width=600&height=600';
		echo "<a class='button-secondary thickbox' id='unique_id_name' title='Add Files' href='$link' title='Browse'>Add/View Files</a>";
	}
	
	function WTFhack( $data )
	{
		if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'type_url' )
		{
			$data .= 'Hello?';
		}
		return $data;
	}
	
}

?>