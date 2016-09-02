<?php
/* 
Plugin Name: Tobias Labs Product Manager
Plugin URI: http://www.tobiaslabs.com/
Description: Manage products such as writings, audio, and video. Makes them available for download, purchase, or through affiliate links. See readme.txt and readme.rtf for more notes.
Version: 0.8
Author: Tobias Davis
Author URI: http://davistobias.com
*/

// Plugin activation options
function tlpm_install(){

	// This is the data array that gets stored as the plugin options
	$data = array(
		'name' => 'tlpm_', // This name is used to prepend HTML fields
		'options' => array(
			'paypalid' => array(
				'label' => 'Paypal ID',
				'desc' => 'The Paypal ID is just the email address associated with the account.',
				'val' => '' // the persons email address
			),
			'amazonid' => array(
				'label' => 'Amazon ID',
				'desc' => 'The Amazon ID used to associate a user with a product.',
				'val' => '' // unique amazon key
			)
		),
		'datafields' => array(
			'common' => array(
				'language' => 'Language',
				'condition' => 'Condition',
				'pubdate' => 'Publishers Date',
				'asincode' => 'Amazon ASIN Code',
				'volumenumber' => 'Volume Number',
				'isbnten' => 'ISBN (10)',
				'isbnthirteen' => 'ISBN (13)',
				'condition' => 'Condition',
				'quantity' => 'Quantity',
				'weight' => 'Weight'
			),
			'documents' => array(
				'edition' => 'Edition',
				'binding' => 'Binding',
				'pages' => 'Page Length'
			),
			'audio' => array(
				'category' => 'Category',
				'numdiscs' => 'Number of Discs',
				'length' => 'Length',
				'medium' => 'Recording Medium'
			),
			'video' => array(
				'category' => 'Category',
				'numdiscs' => 'Number of Discs',
				'subtitles' => 'Subtitles',
				'length' => 'Length',
				'format' => 'Format',
				'medium' => 'Recording Medium',
				'youtubeurl' => 'YouTube URL'
			)
		),
		'language' => array(
			'english' => 'English',
			'chinese' => 'Chinese',
			'german' => 'German',
			'french' => 'French',
			'other' => 'Other'
		),
		'condition' => array(
			'newinbox' => 'New in Box',
			'nowear' => 'No Wear',
			'lightwear' => 'Light Wear',
			'modwear' => 'Moderate Wear',
			'sigwear' => 'Significant Wear'
		)
	);

	// Add database option field (if it exists already, it will be overwritten)
	update_option('plugin_tlpm_options', $data, '', 'yes');
	// I saw where someone said to prefix the options entry with 'plugin_' so that, when readong the wp_options database, you could see clearly that the field is from a plugin
	// I like this idea, so do it

};
register_activation_hook(__FILE__, 'tlpm_install'); 

// Plugin deactivation options
function tlpm_uninstall(){

	// Delete database option field
	delete_option('plugin_tlpm_options');

};
register_deactivation_hook(__FILE__, 'tlpm_uninstall');

// Add a custom icon to the admin pages
function tlpm_custom_logo(){
	echo '<style type="text/css"> #header-logo { background-image: url('.plugins_url().'/tlproductman/images/admin-icon.png) !important; }</style>';
};
add_action('admin_head', 'tlpm_custom_logo');

/* Add a settings page for:
	Paypal account key
	Amazon ID
	Manage languages
	
*/
function tlpm_settings_menu(){
	add_options_page('Product Manager Options', 'Product Manager', 'manage_options', 'tl-product-manager', 'tlpm_plugin_options');
};
function tlpm_plugin_options(){
	$options = get_option('plugin_tlpm_options');
	$prefix = $options['name'];
	$options = $options['options'];

	if (!current_user_can('manage_options')){
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	echo '<div class="wrap"><h2>Product Manager Options</h2>';
	
	if ($_REQUEST[$prefix.'submit']){
		tlpm_update_options();
	}
	tlpm_print_options();

	echo '</div>';
};
function tlpm_update_options(){
	$options = get_option('plugin_tlpm_options');
	$prefix = $options['name'];

	// Verify authentication
	$ok = false;
	if ( wp_verify_nonce( $_POST[$prefix.'admin_noncename'], plugin_basename(__FILE__) ) && current_user_can('manage_options') ) { $ok = true; };

	// Print out an error if the verification failed
	if(!$ok){
		echo '<div id="message" class="error fade"><p>Failed to save options, are you an administrator?</p></div>';
	}
	// Otherwise, authentication passed
	else{

		// Cycle through the fields and update them
		foreach($options['options'] as $key => $field){
			$data = $_POST[$prefix.$key]; // clean it first?
			$options['options'][$key]['val'] = $data;
		};

		// Check the language fields
// not yet implemented, this section is unfinished
//		$data = $_POST[$prefix.'langs'];
		

		// Update the options
		update_option('plugin_tlpm_options', $options);

		// Finally, print that everything went ok
		echo '<div id="message" class="updated fade"><p>Options updated successfully.</p></div>';

	};
};
function tlpm_print_options(){
	// Get options
	$options = get_option('plugin_tlpm_options');
	$prefix = $options['name'];
	
	// Print out the table of options
	echo '<form method="post"><table class="form-table">';

	// Use a nonce field to verify data entry later on
	echo '<input type="hidden" name="'.$prefix.'admin_noncename" id="'.$prefix.'noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';

	foreach($options['options'] as $key => $field){
		echo '<tr>';
		?>
		<th style="text-align:right;" scope="row"><label for="<?php echo $prefix; ?>greeting"><?php echo $field['label']; ?>:</label></th>
		<td><input type="text" name="<?php echo $prefix.$key; ?>" value="<?php echo $field['val']; ?>" /><a title="<?php echo $field['desc']; ?>">?</a></td>
		<?php
		echo '</tr>';
	};
		// Print out list of languages
/* not yet implemented, need to figure out how to save correctly
		$langs = implode(', ', $options['language']);
		echo '<tr><th style="text-align:right;" scope="row"><label for="'.$prefix.'langs">Languages:</label></th><td>';
		echo '<input type="text" name="'.$prefix.'langs" value="'.$langs.'" size="50" /> Separate languages with commas.';
		echo '</td></tr>';
*/
	echo '<td><input type="submit" class="button-primary" name="'.$prefix.'submit" value="Update Options"></td>';
	echo '</table></form>';
};
add_action('admin_menu', 'tlpm_settings_menu');

/* Register new post types:
	Documents:	'documents'
	Audio:		'audio'
	Video:		'video'
*/
function tlpm_post_register(){
	$plugin = plugins_url() . '/tlproductman';
	$documents_args = array(
		'labels' => array(
			'name' => _x('Documents', 'post type general name'),
			'singular_name' => _x('Document', 'post type singular name'),
			'add_new' => _x('Add New', 'documents'),
			'add_new_item' => __('Add New Document'),
			'edit_item' => __('Edit Document'),
			'new_item' => __('New Document'),
			'view_item' => __('View Document'),
			'search_items' => __('Search Documents'),
			'not_found' =>  __('No documents found'),
			'not_found_in_trash' => __('No documents found in Trash')
		),
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'rewrite' => true,
		'menu_position' => 5,
		'menu_icon' => $plugin . '/images/document_icon_16x16.png',
		'supports' => array('title', 'editor', 'thumbnail')
	);
	$audio_args = array(
		'labels' => array(
			'name' => _x('Audio', 'post type general name'),
			'singular_name' => _x('Audio', 'post type singular name'),
			'add_new' => _x('Add New', 'audio'),
			'add_new_item' => __('Add New Audio'),
			'edit_item' => __('Edit Audio'),
			'new_item' => __('New Audio'),
			'view_item' => __('View Audio'),
			'search_items' => __('Search Audio'),
			'not_found' =>  __('No audio items found'),
			'not_found_in_trash' => __('No audio items found in Trash')
		),
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'rewrite' => true,
		'menu_position' => 6,
		'menu_icon' => $plugin . '/images/audio_icon_16x16.png',
		'supports' => array('title', 'editor', 'thumbnail')
	);
	$video_args = array(
		'labels' => array(
			'name' => _x('Videos', 'post type general name'),
			'singular_name' => _x('Video', 'post type singular name'),
			'add_new' => _x('Add New', 'video'),
			'add_new_item' => __('Add New Video'),
			'edit_item' => __('Edit Video'),
			'new_item' => __('New Video'),
			'view_item' => __('View Video'),
			'search_items' => __('Search Videos'),
			'not_found' =>  __('No videos found'),
			'not_found_in_trash' => __('No videos found in Trash')
		),
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'rewrite' => true,
		'menu_position' => 7,
		'menu_icon' => $plugin . '/images/video_icon_16x16.png',
		'supports' => array('title', 'editor', 'thumbnail')
	);
	register_post_type( 'documents' , $documents_args );
	register_post_type( 'audio' , $audio_args );
	register_post_type( 'video' , $video_args );
}
add_action('init', 'tlpm_post_register');

/* Register taxonomies:
	The taxonomies are set with "'show_ui'=> false" so that I can setup a custom menu
	which lists all taxonomies.
	Author:				'writer' (Documents, Audio, Video)
	Publisher:			'publisher' (Documents, Audio, Video)
	Speaker:			'speaker' (Audio, Video)
	Album Artist:		'albumartist' (Audio)
	Album Conductor:	'albumconductor' (Audio)
	Director:			'director' (Video)
	Producer:			'producer' (Audio, Video)
	Actors:				'actor' (Audio, Video)
	Category Tag:		'categorytag' (Documents, Audio, Video)
	Series:				'series' (Documents, Audio, Video)
*/
function tlpm_taxonomy_register(){
	register_taxonomy( // Writer (Author)
		'tlpm_writer',
		array('documents', 'audio', 'video'), // These are the posts it is attached to
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Authors', 'taxonomy general name' ),
				'singular_name' => _x( 'Author', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Authors' ),
				'all_items' => __( 'All Authors' ),
				'edit_item' => __( 'Edit Author' ), 
				'update_item' => __( 'Update Author' ),
				'add_new_item' => __( 'Add New Author' ),
				'new_item_name' => __( 'New Author Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'writer' ),
		)
	);
	register_taxonomy( // Publisher
		'tlpm_publisher',
		array('documents', 'audio', 'video'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Publishers', 'taxonomy general name' ),
				'singular_name' => _x( 'Publisher', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Publishers' ),
				'all_items' => __( 'All Publishers' ),
				'edit_item' => __( 'Edit Publishers' ), 
				'update_item' => __( 'Update Publisher' ),
				'add_new_item' => __( 'Add New Publisher' ),
				'new_item_name' => __( 'New Publisher Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'publisher' ),
		)
	);
	register_taxonomy( // Speaker
		'tlpm_speaker',
		array('audio', 'video'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Speakers', 'taxonomy general name' ),
				'singular_name' => _x( 'Speaker', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Speakers' ),
				'all_items' => __( 'All Speakers' ),
				'edit_item' => __( 'Edit Speaker' ), 
				'update_item' => __( 'Update Speaker' ),
				'add_new_item' => __( 'Add New Speaker' ),
				'new_item_name' => __( 'New Speaker Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'speaker' ),
		)
	);
	register_taxonomy( // Album Artist
		'tlpm_albumartist',
		array('audio'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Album Artists', 'taxonomy general name' ),
				'singular_name' => _x( 'Album Artist', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Artists' ),
				'all_items' => __( 'All Artists' ),
				'edit_item' => __( 'Edit Artist' ), 
				'update_item' => __( 'Update Artist' ),
				'add_new_item' => __( 'Add New Artist' ),
				'new_item_name' => __( 'New Artist Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'albumartist' ),
		)
	);
	register_taxonomy( // Album Conductor
		'tlpm_albumconductor',
		array('audio'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Conductors', 'taxonomy general name' ),
				'singular_name' => _x( 'Conductor', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Conductors' ),
				'all_items' => __( 'All Conductors' ),
				'edit_item' => __( 'Edit Conductor' ), 
				'update_item' => __( 'Update Conductor' ),
				'add_new_item' => __( 'Add New Conductor' ),
				'new_item_name' => __( 'New Conductor Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'albumconductor' ),
		)
	);
	register_taxonomy( // Director
		'tlpm_director',
		array('video'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Directors', 'taxonomy general name' ),
				'singular_name' => _x( 'Director', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Directors' ),
				'all_items' => __( 'All Directors' ),
				'edit_item' => __( 'Edit Director' ), 
				'update_item' => __( 'Update Director' ),
				'add_new_item' => __( 'Add New Director' ),
				'new_item_name' => __( 'New Director Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'director' ),
		)
	);
	register_taxonomy( // Producer
		'tlpm_producer',
		array('audio', 'video'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Producers', 'taxonomy general name' ),
				'singular_name' => _x( 'Producer', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Producers' ),
				'all_items' => __( 'All Producers' ),
				'edit_item' => __( 'Edit Producer' ), 
				'update_item' => __( 'Update Produced' ),
				'add_new_item' => __( 'Add New Producer' ),
				'new_item_name' => __( 'New Producer Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'producer' ),
		)
	);
	register_taxonomy( // Actor
		'tlpm_actor',
		array('audio', 'video'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Actors', 'taxonomy general name' ),
				'singular_name' => _x( 'Actor', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Actors' ),
				'all_items' => __( 'All Actors' ),
				'edit_item' => __( 'Edit Actor' ), 
				'update_item' => __( 'Update Actor' ),
				'add_new_item' => __( 'Add New Actor' ),
				'new_item_name' => __( 'New Actor Name' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'actor' ),
		)
	);
	register_taxonomy( // Series
		'tlpm_series',
		array('documents', 'audio', 'video'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Series', 'taxonomy general name' ),
				'singular_name' => _x( 'Series', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Series' ),
				'all_items' => __( 'All Series' ),
				'edit_item' => __( 'Edit Series' ), 
				'update_item' => __( 'Update Series' ),
				'add_new_item' => __( 'Add New Series' ),
				'new_item_name' => __( 'New Series' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'series' ),
		)
	);
	register_taxonomy( // Category (just like a normal post's "Tags")
		'tlpm_categorytag',
		array('documents', 'audio', 'video'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x( 'Category Tags', 'taxonomy general name' ),
				'singular_name' => _x( 'Category Tag', 'taxonomy singular name' ),
				'search_items' =>  __( 'Search Category Tags' ),
				'all_items' => __( 'All Category Tags' ),
				'edit_item' => __( 'Edit Category Tag' ), 
				'update_item' => __( 'Update Category Tag' ),
				'add_new_item' => __( 'Add New Category Tag' ),
				'new_item_name' => __( 'New Category Tag' ),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'categorytag' ),
		)
	);
};
add_action('init', 'tlpm_taxonomy_register');

/* Add the sweet looking meta boxes:
	Documents:	'tlpm_metabox_documents'
	Audio:		'tlpm_metabox_audio'
	Video:		'tlpm_metabox_video'
*/
function tlpm_metabox_register(){
	add_meta_box(
		'tlpm_metabox_documents',
		'Document Information',
		'tlpm_metabox_documents',
		'documents',
		'normal',
		'low'
	);
	add_meta_box(
		'tlpm_metabox_audio',
		'Audio Information',
		'tlpm_metabox_audio',
		'audio',
		'normal',
		'low'
	);
	add_meta_box(
		'tlpm_metabox_video',
		'Video Information',
		'tlpm_metabox_video',
		'video',
		'normal',
		'low'
	);
};
add_action('admin_init', 'tlpm_metabox_register');

// Design the documents meta box
function tlpm_metabox_documents(){
	global $post;

	// Generate the array from the plugin options database array
	$fields = get_option('plugin_tlpm_options');
	$fieldarray = array_merge(
		$fields['datafields']['common'],
		$fields['datafields']['documents']
	);
	$prefix = $fields['name'];
	$lang = $fields['language'];
	$cond = $fields['condition'];

	// Use a nonce field to verify data entry later on
	echo '<input type="hidden" name="'.$prefix.'noncename" id="'.$prefix.'docs_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';

	// These things will be in an unordered list
	echo '<table class="form-table">';

	// Go through the list of document data items
	foreach($fieldarray as $key => $name) {

		// Check for data already occupying the meta fields
		$data = get_post_meta($post->ID, $key, true);

		// If it's the language, create a dropdown from the array
		if ($key == 'language') {
			echo '<tr>';
			echo '<th style="text-align:right;" scope="row">'.$name.':</th>';
			echo '<td><select name="'.$prefix.$key.'_value">';
			echo '<option value=""></option>';
			foreach($lang as $htmlsafe => $title){
				if ($htmlsafe == $data) echo '<option selected value="'.$htmlsafe.'">'.$title.'</option>';
				else echo '<option value="'.$htmlsafe.'">'.$title.'</option>';
			};
			echo '</select></td></tr>';
		}

		// If it's the condition, create a dropdown from the array
		elseif ($key == 'condition') {
			echo '<tr>';
			echo '<th style="text-align:right;" scope="row">'.$name.':</th>';
			echo '<td><select name="'.$prefix.$key.'_value">';
			echo '<option value=""></option>';
			foreach($cond as $htmlsafe => $title){
				if ($htmlsafe == $data) echo '<option selected value="'.$htmlsafe.'">'.$title.'</option>';
				else echo '<option value="'.$htmlsafe.'">'.$title.'</option>';
			};
			echo '</select></td></tr>';
		}

		// Otherwise, just show a simple text box
		else {
			echo '<tr>';
			echo '<th style="text-align:right;" scope="row">'.$name.':</th>';
			echo '<td><input type="text" maxlength="45" size="45" name="'.$prefix.$key.'_value" value="'.$data.'" /></td>';
			echo '</tr>';
		};

	};

	// Close off the unordered list
	echo '</table>';

};

// Design the audio meta box
function tlpm_metabox_audio(){
	global $post;
	// Generate the array from the plugin options database array
	$fields = get_option('plugin_tlpm_options');
	$fieldarray = array_merge(
		$fields['datafields']['common'],
		$fields['datafields']['audio']
	);
	$prefix = $fields['name'];
	$lang = $fields['language'];
	$cond = $fields['condition'];

	// Use a nonce field to verify data entry later on
	echo '<input type="hidden" name="'.$prefix.'noncename" id="'.$prefix.'docs_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';

	// These things will be in an unordered list
	echo '<table class="form-table">';

	// Go through the list of document data items
	foreach($fieldarray as $key => $name) {

		// Check for data already occupying the meta fields
		$data = get_post_meta($post->ID, $key, true);

		// If it's the language, create a dropdown from the array
		if ($key == 'language') {
			echo '<tr>';
			echo '<th style="text-align:right;" scope="row">'.$name.':</th>';
			echo '<td><select name="'.$prefix.$key.'_value">';
			echo '<option value=""></option>';
			foreach($lang as $htmlsafe => $title){
				if ($htmlsafe == $data) echo '<option selected value="'.$htmlsafe.'">'.$title.'</option>';
				else echo '<option value="'.$htmlsafe.'">'.$title.'</option>';
			};
			echo '</select></td></tr>';
		}

		// If it's the condition, create a dropdown from the array
		elseif ($key == 'condition') {
			echo '<tr>';
			echo '<th style="text-align:right;" scope="row">'.$name.':</th>';
			echo '<td><select name="'.$prefix.$key.'_value">';
			echo '<option value=""></option>';
			foreach($cond as $htmlsafe => $title){
				if ($htmlsafe == $data) echo '<option selected value="'.$htmlsafe.'">'.$title.'</option>';
				else echo '<option value="'.$htmlsafe.'">'.$title.'</option>';
			};
			echo '</select></td></tr>';
		}

		// Otherwise, just show a simple text box
		else {
			echo '<tr>';
			echo '<th style="text-align:right;" scope="row">'.$name.':</th>';
			echo '<td><input type="text" maxlength="45" size="45" name="'.$prefix.$key.'_value" value="'.$data.'" /></td>';
			echo '</tr>';
		};

	};

	// Close off the unordered list
	echo '</table>';

};

// Design the video meta box
function tlpm_metabox_video(){
	global $post;

	// Generate the array from the plugin options database array
	$fields = get_option('plugin_tlpm_options');
	$fieldarray = array_merge(
		$fields['datafields']['common'],
		$fields['datafields']['video']
	);
	$prefix = $fields['name'];
	$lang = $fields['language'];
	$cond = $fields['condition'];

	// Use a nonce field to verify data entry later on
	echo '<input type="hidden" name="'.$prefix.'noncename" id="'.$prefix.'docs_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';

	// These things will be in an unordered list
	echo '<table class="form-table">';

	// Go through the list of document data items
	foreach($fieldarray as $key => $name) {

		// Check for data already occupying the meta fields
		$data = get_post_meta($post->ID, $key, true);

		// If it's the language, create a dropdown from the array
		if ($key == 'language') {
			echo '<tr>';
			echo '<th style="text-align:right;" scope="row">'.$name.':</th>';
			echo '<td><select name="'.$prefix.$key.'_value">';
			echo '<option value=""></option>';
			foreach($lang as $htmlsafe => $title){
				if ($htmlsafe == $data) echo '<option selected value="'.$htmlsafe.'">'.$title.'</option>';
				else echo '<option value="'.$htmlsafe.'">'.$title.'</option>';
			};
			echo '</select></td></tr>';
		}

		// If it's the condition, create a dropdown from the array
		elseif ($key == 'condition') {
			echo '<tr>';
			echo '<th style="text-align:right;" scope="row">'.$name.':</th>';
			echo '<td><select name="'.$prefix.$key.'_value">';
			echo '<option value=""></option>';
			foreach($cond as $htmlsafe => $title){
				if ($htmlsafe == $data) echo '<option selected value="'.$htmlsafe.'">'.$title.'</option>';
				else echo '<option value="'.$htmlsafe.'">'.$title.'</option>';
			};
			echo '</select></td></tr>';
		}

		// Otherwise, just show a simple text box
		else {
			echo '<tr>';
			echo '<th style="text-align:right;" scope="row">'.$name.':</th>';
			echo '<td><input type="text" maxlength="45" size="45" name="'.$prefix.$key.'_value" value="'.$data.'" /></td>';
			echo '</tr>';
		};

	};

	// Close off the unordered list
	echo '</table>';

};

// Save all the data
function tlpm_save_data(){
	global $post;

	// Generate the array from the plugin options database entry
	$fields = get_option('plugin_tlpm_options');
	$fieldarray = array_merge(
		$fields['datafields']['common'],
		$fields['datafields']['documents'],
		$fields['datafields']['audio'],
		$fields['datafields']['video']
	);
	$prefix = $fields['name'];

	// Verify the nonce key
	if ( !wp_verify_nonce( $_POST[$prefix.'noncename'], plugin_basename(__FILE__) )) { return $post->ID; };

	// Verify the user has proper priveleges
	if ( 'page' == $_POST['post_type'] ) { if ( !current_user_can( 'edit_page', $post->ID )) { return $post->ID; } }
	else { if ( !current_user_can( 'edit_post', $post->ID )) { return $post->ID; } };


	// Go through the list of document data items
	foreach($fieldarray as $key => $name) {

		// If the meta field is not empty then update it
		if($_POST[$prefix.$key.'_value'] != '') {

			// Sanitize the $_POST data first!
//			$data = $_POST[$prefix.$key.'_value'];

			// Calling update_post_meta() will call add_post_meta() if the field doesn't exist yet
			update_post_meta($post->ID, $key, $_POST[$prefix.$key.'_value']);
		}

		// The only other option is that $_POST returned empty, so we'll delete the field for a cleaner database
		else{
			delete_post_meta($post->ID, $key);
		};

	};

};
add_action('save_post', 'tlpm_save_data');

?>