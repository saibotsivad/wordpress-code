<?php

// security
if ( !is_admin() ) die();

class TL_Sermons_Admin extends TL_Sermons_Core
{

	function __construct()
	{
		// additional security
		if ( get_parent_class($this) != 'TL_Sermons_Core' ) die();
		// functions named the same need to call the parent class function
		parent::__construct();
		
		// activation/deactivation
		register_activation_hook( $this->plugin_info['plugin_location'], array( $this, 'Activation' ) );
		register_deactivation_hook( $this->plugin_info['plugin_location'], array( $this, 'Deactivation' ) );
		
		// admin actions
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		add_action( 'save_post', array( $this, 'SavePost' ) );
		add_action( 'admin_menu', array( $this, 'Menu' ) );
		/*
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
		*/
	}
	
	/**
	 * Add the Bible as two database tables, and populate with data.
	 * Create additional table to relate sermons to verse ranges (one to many relationship).
	 *
	 * @since 0.5
	 * @author Tobias Davis
	*/
	function Activation()
	{
	
		// The database setup for the Bible is done here
		include( 'include/tlsp/install-thebible.php' );
		
		// plugin option data
		$options = array(
			'version' => '0.14',
			'options' => array(
				'manage-files-count' => array(
					'val' => 16,
					'type' => 'int', // int/text/tax
					'name' => 'Files Per Page',
					'desc' => 'The number of files to display per page, on the "Manage Files" page.'
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
		update_option( 'plugin_tlsp_options', $options );
		
	}
	// EOF: Activation
	
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
			'Sermon Import',
			'Import sermons from the Sermon Browser plugin to the Sermon Posts plugin.',
			array( $this, 'Importing' )
		);
		
		// only do these things on the sermon_post admin page
		if( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sermon_post' )
		{
		
			// enqueu the thickbox scripts
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			
			// check the PHP version, for the getID3() library
			if ( version_compare( phpversion(), '4.2.0' ) >= 0 && version_compare( phpversion(), '5.0.5' ) == -1 ) $getid3_version = 'getid3-1.7.10-20090426';
			elseif ( version_compare( phpversion(), '5.0.5' ) >= 0 ) $getid3_version = 'getid3-1.8.5-20110218';
			else $getid3_version = false;
			/*
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
			*/
			
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
	
	}
	// EOF: AdminInit
	
	/**
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function Deactivation()
	{
	}
	// EOF: Deactivation
	
	/**
	 * Manage the taxonomies with a custom interface which is a little easier to use.
	 *
	 * @since 0.5
	 * @author Tobias Davis
	*/
	function ManageTaxonomy()
	{
		include( 'taxonomy.php' );
	}
	
	/**
	 * Register the "Settings > Sermon Posts", "Sermons > Manage Files" menus, and replacing the default taxonomy
	 * menus with custom ones so they can be managed easier.
	 *
	 * @since 0.5
	 * @author Tobias Davis
	*/
	function Menu()
	{
	
		// plugin settings page
		add_options_page(
			'Sermon Posts Options',
			'Sermon Posts',
			'manage_options',
			'tl_sermons-settings',
			array( $this, 'OptionsPage' )
		);
		
		// custom taxonomy management pages
		add_submenu_page(
			'edit.php?post_type=sermon_post',
			'Services',
			'Services',
			'manage_options',
			'tl_sermons-tlsp_service',
			array( $this, 'ManageTaxonomy' )
		);
		
		add_submenu_page(
			'edit.php?post_type=sermon_post',
			'Series',
			'Series',
			'manage_options',
			'tl_sermons-tlsp_series',
			array( $this, 'ManageTaxonomy' )
		);
		
		add_submenu_page(
			'edit.php?post_type=sermon_post',
			'Preachers',
			'Preachers',
			'manage_options',
			'tl_sermons-tlsp_preacher',
			array( $this, 'ManageTaxonomy' )
		);
		
		// file manager
		add_submenu_page(
			'edit.php?post_type=sermon_post',
			'Manage Files',
			'Manage Files',
			'edit_posts',
			'tl_sermons-files',
			array( $this, 'ManageFiles' )
		);
		
	}
	// EOF: Menu
	
	/**
	 * Print out the sermon metabox
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function Metabox()
	{
		include( 'metabox.php' );
	}
	// EOF: Metabox
	
	/**
	 * Register and remove the sermon post metaboxes
	 *
	 * @since 1.0
	 * @author Tobias Davis
	*/
	function MetaboxSetup()
	{
		add_meta_box(
			'tlsp_metabox',
			'Sermon Details',
			array( $this, 'Metabox' ),
			'sermon_post',
			'normal',
			'high'
		);
		// both of these are handled in the "Sermon Information" metabox
		remove_meta_box( 'postcustom', 'sermon_post', 'normal' );
		remove_meta_box( 'postimagediv', 'sermon_post', 'side' );
	}
	// EOF: MetaboxSetup
	
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
			include( 'options.php' );
		}
		
	}
	// EOF: OptionsPage
	
	/**
	 * Save sermon information: Set taxonomies, set verse ranges
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function SavePost()
	{
		// only run on the sermon_post save
		if ( isset ( $_REQUEST['tlsp_MetaBox_nonce'] ) )
		{
		
			global $post;
			
			// verify nonce, check user permissions
			if ( wp_verify_nonce( $_REQUEST['tlsp_MetaBox_nonce'], 'tlsp_metabox_save' ) && current_user_can( 'edit_posts', $post->ID ) )
			{

				// description and date are handled magically by Wordpress if the
				// element name/id is the same as the default field
				// sermon tags are handled automagically as well
				
				if ( isset ( $_POST['tlsp_preacher'] ) )
				{
					wp_set_object_terms( $post->ID, (int)$_POST['tlsp_preacher'], 'tlsp_preacher', false );
				}
			
				if ( isset ( $_POST['tlsp_series'] ) )
				{
					wp_set_object_terms( $post->ID, (int)$_POST['tlsp_series'], 'tlsp_series', false );
				}
			
				if ( isset ( $_POST['tlsp_service'] ) )
				{
					wp_set_object_terms( $post->ID, (int)$_POST['tlsp_service'], 'tlsp_service', false );
				}

				// this needs a serious update
				$passages = $this->Post2Array( $_POST['tlsp_ref'] );
				$this->AddTerms( $post->ID, $passages );
			
			}
			else
			{
				// the user cannot edit this
				echo '<div id="message" class="error fade"><p>Failed to save options, nonce invalid or insufficient priveleges.</p></div>';
			}
			
		}
	
	}
	// EOF: SavePost
	
}

?>