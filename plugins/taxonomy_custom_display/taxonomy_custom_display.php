<?php
/* 
Plugin Name: Taxonomy - Custom Displays
Plugin URI: http://www.tobiaslabs.com/
Description: Demonstrates different methods of displaying taxonomies inside a custom meta box.
Version: 0.1
Author: Tobias Davis
Author URI: http://davistobias.com
*/

// Register a taxonomy:
function tcd_taxonomy_register(){
	register_taxonomy(
		'tcd_singer',
		'post',
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Singers', 'taxonomy general name' ),
				'singular_name' => _x( 'Singer', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Singers' ),
				'all_items' => __( 'All Singers' ),
				'edit_item' => __( 'Edit Singer' ), 
				'update_item' => __( 'Update Singer' ),
				'add_new_item' => __( 'Add New Singer' ),
				'new_item_name' => __( 'New Singer Name' ),
			),
			'show_ui' => true, // will not display in the 'post', and won't display in the sidebar either
			'query_var' => true,
			'rewrite' => array( 'slug' => 'singer' ),
		)
	);
};
add_action('init', 'tcd_taxonomy_register');

// Add a meta box, this is where the modified taxonomy display will be
function tcd_metabox_register(){
	add_meta_box(
		'tcd_metabox_documents',
		'Extra Information',
		'tcd_metabox_documents',
		'post',
		'normal',
		'low'
	);
};
add_action('admin_init', 'tcd_metabox_register');


// Design the meta box
function tcd_metabox_documents(){
	global $post;

	// Use a nonce field to verify data entry later on
	echo '<input type="hidden" name="'.$prefix.'noncename" id="'.$prefix.'docs_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';

	// grab an array of taxonomy objects currently associated with this post
	$curr_tax = get_terms('tcd_singer');
	
	// grab an array of all taxonomy objects
	$all_tax = get_terms('tcd_singer', array( 'get' => 'all' ) );

	// print a list of taxonomy objects by name, with a #link to their id
	foreach ( $all_tax as $class1 )
	{
		$in_curr = false;
		foreach ( $curr_tax as $class2 )
		{
			if ( $class1->term_id == $class2->term_id ) $in_curr = true;
		}
		if ( $in_curr )
		{
			echo '<a id="tcd_singer-'. $class1->term_id .'" class="delete">X</a> '. $class1->name .'</a><br />';
		}
		else
		{
			echo '<a id="tcd_singer-'. $class1->term_id .'" class="add">+</a> '. $class1->name .'</a><br />';
		}
	}
//	print_r($all_tax);

	//echo '<a href="#'.$class->term_id.'">'.$class->name.'</a><br />';

};




?>