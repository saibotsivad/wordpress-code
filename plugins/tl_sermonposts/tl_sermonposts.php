<?php
/* 
Plugin Name: Sermon Posts
Plugin URI: http://www.tobiaslabs.com/sermonposts/
Description: Manage sermon audio, documents, preachers, sermon catagories, and more! Uses built-in WordPress functionality! RSS! Podcasts! Exclamation points!
Version: 0.14
Author: Tobias Labs
Author URI: http://tobiaslabs.com
*/

// TODO: are the $security=1 things necessary for good security when using includes? it seems like an awfully dumb way
// to secure things, but what other way is there? is this even needed? can you do this correctly using file permissions?

// check that the version is high enough
if ( version_compare( get_bloginfo( 'version' ), '3.0.0' ) >= 0 ) $TL_SermonPosts = new TL_SermonPosts;
else wp_die( __( "Your version of WordPress is too old! You need version 3.0.0 or higher to install this plugin." ) );

/* ========== Main ========== */

// everything is inside a class to prevent namespace errors
class TL_SermonPosts
{

	/**
	 * Variables used for the getID3() classes
	 *
	 * @since 0.6
	 * @author Tobias Davis
	*/
	var $getID3;
	var $writeID3;
	
	/**
	 * Variables used for the thickbox pop-up
	 *
	 * @since 0.11
	 * @author Tobias Davis
	*/
	var $parent_id;
	var $file_id;
	var $html_name = 'tlspmedia';
	var $thickbox_url ;
	var $thickbox_width;
	var $thickbox_height;
	
	/**
	 * Register all hooks and actions, to keep code cleaner.
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	public function __construct()
	{
		register_activation_hook( __FILE__, array( $this, 'Install' ) ); 
		add_action( 'after_setup_theme', array( $this, 'EnablePostThumbnails' ), '9999' );
		add_action( 'init', array( $this, 'Init' ) );
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		add_action( 'save_post', array( $this, 'SavePost' ) );
		add_action( 'admin_menu', array( $this, 'Menu' ) );
		register_deactivation_hook( __FILE__, array( $this, 'Deactivate' ) );
		
		// this is the part for the thickbox popup, it's kind of convoluted, and I welcome any changes to simplify it
		if ( is_admin() )
		{
			// initially we don't know the parent or file id
			$this->parent_id = false;
			$post = 0;
			
			// if it's on the "Add New Sermon" page, then global $post is set, we're okay
			if ( @$_GET['post_type'] == 'sermon_post' ) $this->parent_id = true;
			// otherwise, we can get the post id of the working page three possible ways, depending on where we are in the process of uploading
			elseif ( @is_numeric( $_GET['post'] ) ) $post = absint( $_GET['post'] );
			elseif ( @is_numeric( $_GET['post_id'] ) ) $post = absint( $_GET['post_id'] );
			elseif ( @is_numeric( $_POST['post_ID'] ) ) $post = absint( $_POST['post_ID'] );
			
			// if any of those methods worked, we can try to set the post id directly
			if ( $post )
			{
				$post = get_post( $post );
				// on the "Add New Sermon" page, the get_post() function will always evaluate to false, but that's okay, we catch it later
				if ( $post->post_type == 'sermon_post' ) $this->parent_id = $post->ID;
			}
			// when the upload process is done, it passes the attachment id back, and if we
			// set $_GET['post_id'] as the post id, it will attach them automagically
			elseif ( @is_numeric( $_POST['attachment_id'] ) )
			{
				$post = get_post( absint( $_POST['attachment_id'] ) );
				// which means we can check if the parent is a sermon
				$post = get_post( $post->post_parent );
				if ( $post->post_type == 'sermon_post' ) $this->parent_id = $post->ID;
			}
			
			// finally, either we've got the parent_id as true, or as the actual id, and in either
			// case we're playing with sermon files, so we can set up the ThickBox actions/filters
			if ( $this->parent_id )
			{
				add_action( 'admin_head', array( $this, 'SetThickboxVals' ) );
				add_action( 'media_upload_'.$this->html_name, array( $this, 'IframeAdder' ) );
				add_filter( 'attachment_fields_to_edit', array( $this, 'EditFields'), 11, 2 );
				add_action( 'post-upload-ui', array( $this, 'InfoText' ) );
				add_filter( 'media_upload_tabs', array( $this, 'RemoveTabs' ) );
				add_filter( 'media_upload_form_url', array( $this, 'AlterLibraryForm' ) );
			}
		}
	}
	
	/**
	 * Add the Bible as two database tables, and populate with data.
	 * Create additional table to relate sermons to verse ranges (one to many relationship).
	 *
	 * @since 0.5
	 * @author Tobias Davis
	*/
	function Install()
	{
	
		// The database setup for the Bible is done here
		$security = 1;
		include( 'include/tlsp/thebible.php' );
		
		// plugin option data (removed when deactivated)
		$options = array(
			'version' => '0.14',
			'options' => array(
				'manage-files-count' => array(
					'val' => 16,
					'type' => 'int', // int/text/tax
					'name' => 'Files Per Page',
					'desc' => 'The number of files to display per page, on the "Manage Files" page.'
				),
				'thickbox-width' => array(
					'val' => 640,
					'type' => 'int',
					'name' => 'Thickbox Pop-Up Width', // ideally this would be automatic sizing, but I haven't figured out how yet
					'desc' => 'Width of the "Add/View Files" pop-up area.'
				),
				'thickbox-height' => array(
					'val' => 563,
					'type' => 'int',
					'name' => 'Thickbox Pop-Up Height', // see note on width
					'desc' => 'Height of the "Add/View Files" pop-up area.'
				),
				'tlsp_preacher' => array(
					'val' => 0,
					'type' => 'tax',
					'name' => 'Default Preacher',
					'desc' => 'Set the default sermon preacher.'
				),
				'tlsp_series' => array(
					'val' => 0,
					'type' => 'tax',
					'name' => 'Default Series',
					'desc' => 'Set the default sermon series.'
				),
				'tlsp_service' => array(
					'val' => 0,
					'type' => 'tax',
					'name' => 'Default Service',
					'desc' => 'Set the default sermon service.'
				)
			)
		);
		update_option('plugin_tlsp_options', $options, '', 'yes');
		
	} // EOF: Install

	/**
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function Deactivate()
	{
	
		// remove selected data from plugin_options
		delete_option( 'plugin_tlsp_options' );
	
	} // EOF: Deactivate
	
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
// TODO: set it so the URL shows as 'sermons' instead, or even customizable perhaps?
			'rewrite' => true,
			'menu_position' => 5,
			'menu_icon' => plugins_url() '/tl_sermonposts/include/tlsp/images/bible-icon-16x12.png',
			'supports' => array('title')//, 'editor') // it supports more, really, but we don't want it to show up
		);
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
	
	} // EOF: Init
	
	/**
	 * Register an importer method, a metabox, a custom file upload window, create
	 * the getID3 class, generate the ID3 Album Art as a jpeg, end enqueue thickbox.
	 *
	 * @since 0.6
	 * @author Tobias Davis
	*/
	function AdminInit()
	{
	
		// register an import method
		register_importer(
			'tlsp_importer',
			'Sermon Browser Import',
			'Import sermons from the Sermon Browser plugin to the Sermon Posts plugin.',
			array( $this, 'Importing' )
		);
		
		// only do these things on the sermon_post admin page
		if( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sermon_post' )
		{
		
			// check the PHP version, for the getID3() library
			if ( version_compare( phpversion(), '4.2.0' ) >= 0 && version_compare( phpversion(), '5.0.5' ) == -1 ) $getid3_version = 'getid3-1.7.10-20090426';
			elseif ( version_compare( phpversion(), '5.0.5' ) >= 0 ) $getid3_version = 'getid3-1.8.5-20110218';
			else $getid3_version = false;
			
			// include the appropriate files
			if( $getid3_version )
			{
				// the core
				include( 'include/getid3/'. $getid3_version .'/getid3/getid3.php' );
				$this->getID3 = new getID3;
// TODO: editing id3 tags is still broken
//				// to write ID3 tags
//				include( 'include/getid3/'. $getid3 .'/getid3/write.php' );
//				$this->writeID3 = new getid3_writetags;
			
			}
			else $this->getID3 = false;
			
			// this is used to send the image from the id3 tag
// TODO: it might be more efficient to grab the image from the file, store it in a
// folder, and then use a direct link, but a bit of inconvenience is added
			if ( isset( $_GET['fileid_id3image'] ) )
			{
			
				// have to go through the process of grabbing the picture data again
				$post_id = (int)$_GET['fileid_id3image'];
				$file = get_post( $post_id, ARRAY_A );
				$filename = explode( get_option('siteurl').'/', $file['guid'] );
				$filename = ABSPATH . $filename[1];
				$mp3info = $this->getID3->analyze( $filename );
				$data = $mp3info['id3v2']['PIC'][0]['data'];
				
				// pass the header info
				header("Pragma: public"); // required
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false); // required for certain browsers
				header("Content-Type: image/jpg");
				
				// pass the data and die
				echo $data;
				die();
			
			}
		
		}
			
		// declare the sermon_post meta-box
		add_meta_box(
			'tlsp_metabox',
			'Sermon Details',
			array( $this, 'MetaBox' ),
			'sermon_post',
			'normal',
			'low'
		);
		
		// enqueu the thickbox scripts on the sermon_post admin page
// TODO: this currently adds the thickbox script to *every* admin page!
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		
	} // EOF: AdminInit
	
	/**
	 * Print out the sermon metabox
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function MetaBox()
	{
		// for additional security, a variable is used and then destroyed
		$security = 1;
		include( 'include/tlsp/metabox.php' );
	
	} // EOF: MetaBox
	
	/**
	 * Save sermon information: Set taxonomies, set verse ranges
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function SavePost()
	{
	
		// wordpress global
		global $post;
	
		// only run on the sermon_post save
		if ( isset ( $_POST['tlsp_MetaBox_nonce'] ) )
		{
		
			// verify nonce, check user permissions
// TODO: You may need to setup a special permission type so different people can manage the same sermon?
			if ( wp_verify_nonce( $_REQUEST['tlsp_MetaBox_nonce'], 'tlsp_metabox_save' ) && current_user_can( 'edit_posts', $post->ID ) )
			{

				// description and date are handled magically by Wordpress if the
				// element name/id is the same as the default field
				// sermon tags are handled automagically as well
				
// TODO: How important is it to make sermons be able to have more than one preacher/series/etc.? Add here if it is a big deal.

				// preacher: tlsp_preacher
				if ( isset ( $_POST['tlsp_preacher'] ) )
				{
					wp_set_object_terms( $post->ID, (int)$_POST['tlsp_preacher'], 'tlsp_preacher', false );
				}
			
				// series: tlsp_series
				if ( isset ( $_POST['tlsp_series'] ) )
				{
					wp_set_object_terms( $post->ID, (int)$_POST['tlsp_series'], 'tlsp_series', false );
				}
			
				// service: tlsp_service
				if ( isset ( $_POST['tlsp_service'] ) )
				{
					wp_set_object_terms( $post->ID, (int)$_POST['tlsp_service'], 'tlsp_service', false );
				}

				// passages selected into proper form
// TODO: I think this needs a better name.
				$passages = $this->Post2Array( $_POST['tlsp_ref'] );
// TODO: This one as well. Combine them? Is there any benefit to keeping them seperate?
				// add passages to the database
				$this->AddTerms( $post->ID, $passages );
			
			}
			// security failure
			else
			{
				// the user cannot edit this
				echo '<div id="message" class="error fade"><p>Failed to save options, nonce invalid or insufficient priveleges.</p></div>';
			}
			
		}
	
	} // EOF: SavePost
	
	/**
	 * Register the "Settings > Sermon Posts", "Sermons > Manage Files" menus, and replacing the default taxonomy
	 * menus with custom ones so they can be managed easier.
	 *
	 * @since 0.5
	 * @author Tobias Davis
	*/
	function Menu()
	{
	
		// add a settings page
		add_options_page(
			'Sermon Posts Options',
			'Sermon Posts',
			'manage_options',
			'tl-sermon-posts',
			array( $this, 'OptionsPage' )
		);
		
		// add a settings page to manage services
		add_submenu_page(
			'edit.php?post_type=sermon_post',
			'Services',
			'Services',
			'manage_options',
			'tl-sermon-posts-service',
			array( $this, 'ManageServices' )
		);
		
		// add a settings page to manage series
		add_submenu_page(
			'edit.php?post_type=sermon_post',
			'Series',
			'Series',
			'manage_options',
			'tl-sermon-posts-series',
			array( $this, 'ManageSeries' )
		);
		
		// add a settings page to manage preachers
		add_submenu_page(
			'edit.php?post_type=sermon_post',
			'Preachers',
			'Preachers',
			'manage_options',
			'tl-sermon-posts-preacher',
			array( $this, 'ManagePreachers' )
		);
		
		// add a custom file management menu
		add_submenu_page(
			'edit.php?post_type=sermon_post',
			'Manage Files',
			'Manage Files',
			'edit_posts',
			'tlsp-file-manager',
			array( $this, 'ManageFiles' )
		);
	
	} // EOF: Menu
	
	/**
	 * Menu page: "Settings > Sermon Posts"
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function OptionsPage()
	{
	
		// verify user authority
		if ( !current_user_can( 'manage_options' ) )
		{
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		else
		{
// TODO: the import process and database management need *much better* integration!
			// if the user clicked through to manage the database tables
			if ( isset( $_GET['editdatabase'] ) && current_user_can('update_core') )
			{
				$security = 1;
				include( 'include/tlsp/import-steptwo.php' );
			}
			// or just normal options
			else
			{
				$security = 1;
				include( 'include/tlsp/manage-options.php' );
			}
		}
		
	} // EOF: OptionsPage
	
	/**
	 * Menu page: "Sermons > Services"
	 *
	 * @since 0.5
	 * @author Tobias Davis
	*/
	function ManageServices()
	{
	
		// verify user permissions
		if ( !current_user_can( 'edit_posts' ) ) wp_die( __('Insufficient permissions to access this page.') );
		else
		{
			// specify type
			$type = 'service';
			$name = 'Services';
			// for additional security, a variable is used and then destroyed
			$security = 1;
			include( 'include/tlsp/manage-taxonomy.php' );
			$security = NULL;
		}
	
	} // EOF: ManageServices
	
	/**
	 * Menu page: "Sermons > Series"
	 *
	 * @since 0.5
	 * @author Tobias Davis
	*/
	function ManageSeries()
	{
	
		// verify user permissions
		if ( !current_user_can( 'edit_posts' ) ) wp_die( __('Insufficient permissions to access this page.') );
		else
		{
			// specify type
			$type = 'series';
			$name = 'Series';
			// for additional security, a variable is used and then destroyed
			$security = 1;
			include( 'include/tlsp/manage-taxonomy.php' );
			$security = NULL;
		}
	
	} // EOF: ManageSeries
	
	/**
	 * Menu page: "Sermons > Preachers"
	 *
	 * @since 0.5
	 * @author Tobias Davis
	*/
	function ManagePreachers( $type )
	{
		// verify user permissions
		if ( !current_user_can( 'edit_posts' ) ) wp_die( __('Insufficient permissions to access this page.') );
		else
		{
			// specify type
			$type = 'preacher';
			$name = 'Preachers';
			// for additional security, a variable is used and then destroyed
			$security = 1;
			include( 'include/tlsp/manage-taxonomy.php' );
			$security = NULL;
		}
	} // EOF: ManagePreachers
	
	/**
	 * Menu page: "Sermons > Manage Files"
	 *
	 * @since 0.3
	 * @author Tobias Davis
	*/
	function ManageFiles()
	{
	
		// verify user permissions
		if ( !current_user_can( 'edit_posts' ) ) wp_die( __('Insufficient permissions to access this page.') );
		else
		{
		
			// when the user clicks on "Edit", it goes back to this page, passing in this variable
			if ( isset( $_GET[ 'editfileid' ] ) )
			{
				// for additional security, a variable is used and then destroyed
				$security = 1;
				include( 'include/tlsp/file-edit.php' );
				$security = NULL;
			}
			
			// when the user wants to delete files, it goes to this page to verify deleting
			elseif ( isset( $_GET['trashfile'] ) || ( isset( $_GET['bulk_action'] ) && $_GET['bulk_action'] == 'trash' ) )
			{
				// for additional security, a variable is used and then destroyed
				$security = 1;
				include( 'include/tlsp/file-delete.php' );
				$security = NULL;
			}
			
			// otherwise, display the custom file manager
			else
			{
				// for additional security, a variable is used and then destroyed
				$security = 1;
				include( 'include/tlsp/file-manager.php' );
			}
		
		}
	
	} // EOF: ManageFiles
	
	/**
	 * Set the thickbox values
	 *
	 * @since 0.11
	 * @author Tobias Davis
	*/
	function SetThickboxVals()
	{
	
		// we can get the post id two ways, either from the global variable
		global $post;
		if ( !empty( $post ) ) $this->parent_id = $post->ID;
		// or from the GET variable
		elseif ( isset( $_GET['post_id'] ) && is_numeric( $_GET['post_id'] ) ) $this->parent_id = absint( $_GET['post_id'] );
		
// TODO: is there a better way to set the thickbox size?
		$options = get_option( 'plugin_tlsp_options' );
		$this->thickbox_width = $options['options']['thickbox-width']['val'];
		$this->thickbox_height = $options['options']['thickbox-height']['val'];
		$this->thickbox_url = get_bloginfo( 'wpurl' ).'/wp-admin/media-upload.php?tab='.$this->html_name.'&post_id='.$this->parent_id.'&TB_iframe=1&width='.$this->thickbox_width.'&height='.$this->thickbox_height;
	}
	
	/**
	 * @since 0.11
	 * @author Tobias Davis
	*/
	function IframeAdder()
	{
		wp_iframe( array( $this, 'UploadPopup' ) );
	}
	
	/**
	 * This is the actual popup page
	 *
	 * @since 0.11
	 * @author Tobias Davis
	*/
	function UploadPopup()
	{
		include( 'include/tlsp/file-upload.php' );
	}
	
	/**
	 * Replacing the help text on the thickbox upload section, since you can't edit titles/etc.
	 *
	 * @since 0.11
	 * @author Tobias Davis
	*/
	function InfoText()
	{
		echo "<p class='help'>File information will be inherited from Sermon information.</p>";
	}
	
	/**
	 * Edits the fields that show up after you upload something via thickbox popup
	 *
	 * @since 0.11
	 * @author Tobias Davis
	*/
	function EditFields( $fields, $file )
	{
		/*
		 * This is a dumb hack to put a nonce field inside the "From Media Library" tab.
		 * Check out media_upload_library_form() and see if you can find a better place
		 * to insert a nonce field...
		 */
		echo wp_nonce_field( 'tlsp_update_file_'.$file->ID, 'tlsp_update_file_'.$file->ID );
		
		// the upload form
		$ajax_nonce = wp_create_nonce( "set_post_thumbnail-{$file->post_parent}" );
		$type = explode( '/', $file->post_mime_type );
		if ( $type[0] == 'image' )
		{
			$thumbnail = "<a class='wp-post-thumbnail button-secondary' id='wp-post-thumbnail-{$file->ID}' href='#' onclick='WPSetAsThumbnail(\"{$file->ID}\", \"$ajax_nonce\");return false;'>Set as Sermon image</a>";
		}
		else $thumbnail = '';
		
		// on the library page and already attached page, each "insert" is a submit button
		$checked = '';
		if ( $file->post_parent == $this->parent_id ) $checked = " checked='yes'";
		$insert = "<input type='checkbox' id='tlsp_checkbox_{$file->ID}' name='tlsp_checkbox[{$file->ID}]' value='{$file->ID}'{$checked} /> <label for='tlsp_checkbox_{$file->ID}'>Attach to Sermon</label>";
		$insert .= "<input type='hidden' name='tlsp_allfiles[{$file->ID}]' value='{$file->ID}' />";
		
		// setup the delete button
		$attachment_id = $file->ID;
		$filename = $file->guid;
		// --- this part lifted from /wp-admin/includes/media.php with no mods, sadly it's required
		if ( current_user_can( 'delete_post', $attachment_id ) ) {
			if ( !EMPTY_TRASH_DAYS ) {
				$delete = "<a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-attachment_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete'>" . __( 'Delete Permanently' ) . '</a>';
			} elseif ( !MEDIA_TRASH ) {
				$delete = "<a href='#' class='del-link' onclick=\"document.getElementById('del_attachment_$attachment_id').style.display='block';return false;\">" . __( 'Delete' ) . "</a>
				 <div id='del_attachment_$attachment_id' class='del-attachment' style='display:none;'>" . sprintf( __( 'You are about to delete <strong>%s</strong>.' ), $filename ) . "
				 <a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-attachment_' . $attachment_id ) . "' id='del[$attachment_id]' class='button'>" . __( 'Continue' ) . "</a>
				 <a href='#' class='button' onclick=\"this.parentNode.style.display='none';return false;\">" . __( 'Cancel' ) . "</a>
				 </div>";
			} else {
				$delete = "<a href='" . wp_nonce_url( "post.php?action=trash&amp;post=$attachment_id", 'trash-attachment_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete'>" . __( 'Move to Trash' ) . "</a>
				<a href='" . wp_nonce_url( "post.php?action=untrash&amp;post=$attachment_id", 'untrash-attachment_' . $attachment_id ) . "' id='undo[$attachment_id]' class='undo hidden'>" . __( 'Undo' ) . "</a>";
			}
		} else {
			$delete = '';
		}
		// --- end of lift
		
		// reset the fields
		$fields = array();
		
		// if the file is attached, the user can still insert it, but it will break the
		// attachment to the other sermon
		if ( $file->post_parent != $this->parent_id && $file->post_parent != 0 )
		{
			$fields['tlsp_warning'] = array(
				'tr' => "\t\t<tr class='submit'><td></td><td class='warning'><em>This file is attached to another sermon, attaching it to this sermon will break the previous connection!</em></td></tr>\n"
			);
		}
		
		// create the buttons
		$fields['buttons'] = array(
			'tr' => "\t\t<tr class='submit'><td></td><td class='savesend'>$insert $thumbnail $delete</td></tr>\n"
		);
		
		// pass it back
		return $fields;
	
	}
	
	/**
	 * Reset the URL on the "From Library" tab
	 *
	 * @since 0.11
	 * @author Tobias Davis
	*/
	function AlterLibraryForm( $url )
	{
		$url = $this->thickbox_url;
		return $url;
	}
	
	/**
	 * Remove the tabs from the library screen of the thickbox upload, since we output them differently
	 *
	 * @since 0.11
	 * @author Tobias Davis
	*/
	function RemoveTabs( $tabs )
	{
		$tabs = array();
		return $tabs;
	}
	
	/**
	 * Access the import processes
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
// TODO: I also wouldn't mind if the importing process were set inside a different class entirely
	function Importing()
	{
	
		// verify user role
		if ( !current_user_can( 'import' ) )
		{
			wp_die( __( 'Insufficient permissions to access this page.' ) );
		}
		else
		{
		
			// the import process goes through many steps
			include( 'include/tlsp/import.php' );

/*
			// if the page is accessed using the "go" button, run the import
			if ( isset( $_POST['tlsp_ImportSermons_submit'] ) )
			{
			
				if ( wp_verify_nonce( $_POST['tlsp_ImportSermons_nonce'], plugin_basename(__FILE__) ) )
				{
				
					// for additional security, a variable is used and then destroyed
					$security = 1;
					include( 'include/tlsp/import-stepone.php' );
					$security = NULL;
				
				}
				else
				{
					echo '<div id="message" class="error fade"><p>Failed to import, nonce invalid.</p></div>';
				}
			
			}
			
			// otherwise, print out the options page
// TODO: there is probably a better approach to the importing interface, so you should figure it out
// maybe something like: import preachers, have viewer check them, import categories, have user check them, etc.
			else
			{
			
				// if files were already imported, disable the option
				$report = get_option( 'plugin_tlsp_importreport' );
				if ( $report != false )
				{
					
					echo '<h2>Import Technical Report</h2>';
					echo '<p>Multiple instances of importing is not supported. If you know what you are doing, you can figure out how to enable it (ie, read the code).</p>';
					echo '<p>This is the raw output from your import. For help interpreting the details, please read the import function file.</p>';
					
// A note on secondary imports:
// Supporting multiple imports is not something I am willing to support, it is complicated and likely to get
// messy very quickly. If you know how to add the code to support this function, feel free to email me and I
// would be glad to add it.
					
					// output the import report
					echo '<pre>';
					print_r( get_option( 'plugin_tlsp_importreport' ) );
					echo '</pre>';
				
				}
				else
				{
				
					// wordpress global
					global $wpdb;
				
					// we can estimate the time it will take to run the import:
					$count = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_sermons", OBJECT );
					$count = count( $count );
					
					// just the database stuff (aka, not moving files)
					$timeone = round( ($count*0.006), 2 ); // factor found experimentally
					
					echo "<p>It will take approximately {$timeone} minutes to import {$count} sermons.</p>";
					
					echo '<form method="post">';
				
					// use a nonce field for later authentication
					echo '<input type="hidden" name="tlsp_ImportSermons_nonce" id="tlsp_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';

// TODO: what options are available for importing?
				
					// this is the "Save Changes" button
					echo '<input type="submit" class="button-primary" name="tlsp_ImportSermons_submit" value="Import">';
					
					echo '</form>';
					
				}
			
			}
		*/
		}
	
	} // EOF: Importing
	
	/**
	 * Given an HTML input array of ranges, turn them into arrays of verse ids
	 *
	 * @input array The HTML form data array
	 * @return array Returns an array of verse ids, e.g., array(array(1543,1231))
	 * @since 0.3
	 * @author Tobias Davis
	*/
	function Post2Array( $array )
	{
	
		// the wordpress query global
		global $wpdb;
		
		// make sure data was actually passed in
		if ( !empty( $array ) )
		{
		
			// set the real output
			$real_output = array();
		
			// loop through each reference set
			foreach ( $array as $i => $ref )
			{
			
				// if a "from" book was selected
				if ( (int)$ref['from']['book'] != 0 )
				{
				
					// init the query
					$start = "( `{$wpdb->prefix}sermon_thebible`.`id` ) as `id` FROM `{$wpdb->prefix}sermon_thebible` WHERE ";
					
					// set the "from" book
					$start .= "`{$wpdb->prefix}sermon_thebible`.`book`=". (int)$ref['from']['book'];
					
					// if a "from" chapter was selected
					if ( $ref['from']['chapter'] != '' )
					{
					
						// set the "from" chapter
						$start .= " AND `{$wpdb->prefix}sermon_thebible`.`chapter`=". (int)$ref['from']['chapter'];
						
						// if a "from" verse was selected
						if ( $ref['from']['verse'] != '' )
						{
						
							// set the "from" verse
							$start .= " AND `{$wpdb->prefix}sermon_thebible`.`verse`=". (int)$ref['from']['verse'];
						
						}
						
					}
					
					// run the query to get the verse id
					$result = $wpdb->get_results( "SELECT MIN{$start}", OBJECT );
					
					// set the output
					$output[0] = $result[0]->id;
					
					// if a "to" book was selected
					if ( (int)$ref['to']['book'] != 0 )
					{
					
						// init the query
						$end = "( `{$wpdb->prefix}sermon_thebible`.`id` ) as `id` FROM `{$wpdb->prefix}sermon_thebible` WHERE ";
						
						// set the "to" book
						$end .= "`{$wpdb->prefix}sermon_thebible`.`book`=". (int)$ref['to']['book'];
						
						// if a "to" chapter was selected
						if ( $ref['to']['chapter'] != '' )
						{
						
							// set the "to" chapter
							$end .= " AND `{$wpdb->prefix}sermon_thebible`.`chapter`=". (int)$ref['to']['chapter'];
							
							// if a "to" verse was selected
							if ( $ref['to']['verse'] != '' )
							{
							
								// set the "from" verse
								$end .= " AND `{$wpdb->prefix}sermon_thebible`.`verse`=". (int)$ref['to']['verse'];
							
							}
							
						}
						
						// run the query to get the verse id
						$result = $wpdb->get_results( "SELECT MAX{$end}", OBJECT );
						
						// set the result
						$output[1] = $result[0]->id;
						
						// re-order the array, in case the range is backward
						sort( $output );
						
						// append the output array
						$real_output[] = $output;
					
					}
					
					// if no "to" book was selected
					else
					{
					
						// take the max verse id of the "from"
						$result = $wpdb->get_results( "SELECT MAX{$start}", OBJECT );
						
						// set the result
						$output[1] = $result[0]->id;
						
						// re-order the array, in case the range is backward
						sort( $output );
						
						// append the output array
						$real_output[] = $output;
					
					}
				
				}
				
				// if no "from" verse was selected, no output is given
			
			}
			
			// if there is output, pass it
			if( empty( $real_output ) )
			{
				return false;
			}
			else
			{
				return $real_output;
			}
		
		}
	
	} // EOF: Post2Array
	
	/**
	 * Add verse ranges to a sermon post
	 *
	 * @input array Range of verses as an array, e.g., array(array(1543,3294),array(42,371))
	 * @since 0.3
	 * @author Tobias Davis
	*/
	function AddTerms( $post_id, $passages )
	{
	
		// wordpress global
		global $wpdb;
		
		// get the mysql maximum allowed packet size
		$maxsize = $wpdb->get_results("SHOW VARIABLES LIKE 'max_allowed_packet'");
		$maxsize = $maxsize[0]->Value;
		
		// clear out the old references (using update owuld be better?)
		$wpdb->query( "DELETE FROM `{$wpdb->prefix}sermon_reference` WHERE `{$wpdb->prefix}sermon_reference`.`sermon`='{$post_id}'" );
		
		// if there are sermons, add them
		if ( !empty( $passages ) && $passages != false )
		{
		
			// initialize the update string
			$data = '';
			
			// create the string
			foreach ( $passages as $passage )
			{
				
				// append value
				$data .= "( ".intval($post_id).", ".intval($passage[0]).", ".intval($passage[1])." ),";
				
				// if the string is getting too long, add it to the database
				if( strlen( $data ) >= ( $maxsize - 200 ) )
				{
				
					// cut off the trailing comma
					$data = substr( $data, 0, strlen( $data ) - 1 );
					
					// insert it into the database
					$wpdb->query("INSERT INTO `{$wpdb->prefix}sermon_reference` ( `sermon`, `start`, `end` ) VALUES {$data}");
					
					// reset the string
					$data = '';
				
				}
			}
			
			// at the end there is probably leftover data
			
			// cut off the trailing comma
			$data = substr( $data, 0, strlen( $data ) - 1 );
			
			// insert it into the database
			$wpdb->query("INSERT INTO `{$wpdb->prefix}sermon_reference` ( `sermon`, `start`, `end` ) VALUES {$data}");
		
		}
	
	} // EOF: AddTerms

}

?>