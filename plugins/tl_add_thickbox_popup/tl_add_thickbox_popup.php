<?php
/*
Plugin Name: Add Thickbox Pop-Up
Plugin URI: http://tobiaslabs.com/
Description: Demonstrates the core code needed to create a pop-up thickbox link.
Version: 1.0
Author: Tobias Labs
Author URI: http://tobiaslabs.com/
*/

/*
In this one, we are figuring out how to handle the thickbox pop-ups, like the
ones used for adding pictures to a blog post.
*/

$MyThing = new MyThing_Class;

// it is a good practice to put functions inside a class, to avoid namespace issues
class MyThing_Class
{

	// pick an HTML-safe name for your pop-up, probably under 10 characters maybe?
	// it is used as part of the add action name and the pop-up link
	private $html_name = 'myupload';
	
	// there's also an HTML-safe URL part, which is set for paged=
	private $page_name = 'mynewpage';
	
	// the pop-up screen also needs a title
	private $title_name = 'My Sweet Pop-Up';
	
	// pop-up size can be defined
	private $popup_width = '600';
	private $popup_height = '400';
	
	// this is the link we'll use to go to the pop-up, and to navigate in it
	private $link;

	// in php, the __construct function runs when the class is initialized
	// so we put the add_action and add_filter here
	function __construct()
	{
	
		// here we add an action for the admin side, to add a menu
		add_action( 'admin_menu', array( $this, 'MyMenu' ) );
		
		// this is the pop-up html (note the action name construction!)
		add_action( 'media_upload_'.$this->html_name, array( $this, 'MyFrameAdder' ) );
		
		// the pop-ups require the thickbox script
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		
		// this is the basic link we'll use
		$this->link = get_bloginfo('url')."/wp-admin/media-upload.php?tab={$this->html_name}&TB_iframe=1&width={$this->popup_width}&height={$this->popup_height}";
	
	}

	// here is the function that will create the menu item
	function MyMenu()
	{
		// for this example we'll just add a settings page
		add_options_page(
			'My Thing Options', // this is a label, I don't know where it's referenced
			'My Thing', // this is the menu title in the "Settings" menu sidebar
			'manage_options', // the rights the user has to see this menu item
			$this->page_name,
			array( $this, 'MyDisplay' ) // the function name (inside a class this is how you name it)
		);
	}
	
	// in here we'll enqueue the core style/script used: thickbox
	function AdminInit()
	{
		// it's a good idea to only load it on the page we'll use it
		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->page_name )
		{
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
		}
	}

	// here is the function that will display the settings page, which has the pop-up link
	function MyDisplay()
	{
		echo "<p><a id='my_thickbox' class='button-secondary thickbox' title='{$this->title_name}' href='{$this->link}'>Click Here!</a></p>";
	}
	
	// this function calls an I-Frame, which will display the actual HTML
	function MyFrameAdder()
	{
		wp_iframe( array( $this, 'MyUploadForm' ) );
	}
	
	// this function actually puts out the HTML in the thickbox pop-up
	function MyUploadForm()
	{
		include( 'popuppage.php' );
	}

}

?>