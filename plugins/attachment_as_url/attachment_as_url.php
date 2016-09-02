<?php
/*
Plugin Name: Attachment As URL
Plugin URI: http://tobiaslabs.com/
Description: Demonstrates the core code needed to modify the attachment fields.
Version: 1.0
Author: Tobias Labs
Author URI: http://tobiaslabs.com/
*/

$AttachmentURL = new AttachmentURL_Class;

// it is a good practice to put functions inside a class, to avoid namespace issues
class AttachmentURL_Class
{

	function __construct()
	{
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		add_action( 'save_post', array( $this, 'SavePost' ), 9999 );
	}
	
	function AdminInit()
	{
		// make a meta box
		add_meta_box(
			'tl_url_field',
			'URL Field',
			array( $this, 'MetaBox' ),
			'sermon_post',
			'normal',
			'low'
		);
	}
	
	function MetaBox()
	{
		global $post;
		echo "<p>{$post->ID}</p>";
		
		$filetypes = array(
			'pdf'=>'application/pdf',
			'mpeg'=>'audio/mpeg'
		);
		echo '<select name="tl_the_type"><option value="0"></option>';
		foreach ( $filetypes as $i => $type ) echo "<option value='{$i}'>{$type}</option>";
		echo '</select>';
		
		echo '<input type="text" name="tl_the_url" />';
		
	}
	
	function SavePost()
	{
	
		global $post;
		
		if ( @$_POST['tl_the_url'] != '' && @$_POST['tl_the_type'] != '0' )
		{
		
			$url = $_POST['tl_the_url'];
			$type = $_POST['tl_the_type'];
			
			$attachment = array(
				'post_title' => 'Example Attachment',
				'post_mime_type' => $_POST['tl_the_type'],
				'post_content' => '',
				'post_status' => 'inherit'
			);
		
		}
	
	}
	
}

?>