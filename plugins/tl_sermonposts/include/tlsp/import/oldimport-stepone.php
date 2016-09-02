<?php
// ===== Security Check ===== //
if( isset ( $security ) ) :
// ========================== //

/* ===== Operational Procedure =====

Acronyms: Sermon Browser (SB), Sermon Posts (SP), WordPress (WP)
Tables Created: sb_transition_preacher, sb_transition_series, sb_transition_tag, sb_transition_passage, sb_transition_file, sb_transition_sermon

1) Relating SB terms with WP taxonomy terms
	These are the tables of interest: sb_preachers, sb_series, sb_tags, sb_services
	Each item on each table will be replaced by WP taxonomy terms, so
	a test is run to see if the term already exists in the WP taxonomy
	table. For each taxonomy term in the SB tables, a relation is
	noted between it and the term in the WP table, and this relation is stored
	in a table called: sb_transition_preacher, sb_transition_series, sb_transition_tag, sb_transition_service

2) Relating SB passages with SP passages
	The SP plugin uses a simple table, relating each passage to
	a range of verses. The SB plugin stores a book name, chapter,
	and verse for each range, in a complex form. Viewing the initialization
	section of the SP single extra table will aid in understanding.
	For each passage that a SB sermon references, a proper reference range is
	created and stored in a table called: sb_transition_passage

3) Creating the WP posts (post_type=attachment) from the SB sermon files
	The user can decide if SB sermon files should be left in their current folder, or
	if they should be moved into folders according to the normal WP upload structure.
	A benefit of having files in a single folder is for ease of backups, but a
	critical problem is that all files must have distinct file names.
	If the user wants the files moved, a folder is created if necessary, and				// post_parent=0
	a copy of the sermon file is then made in the new location. Once file location has		// post_status=inherit
	been determined (new or old, either way) a new post is generated, with post type		// see table _postmeta for file location information
	set as attachment, and basic post information is filled in using the MP3 ID tags.
	Additionally, a table is generated which relates the WP post(attachment) ID to
	the SB sermon ID, and this table is called: sb_transition_file

4) Creating the WP posts (post_type=sermon_post) from the SB sermons
	Using data from the SB sermons table and the recently created transitional tables, a
	new WP post is generated. An additional table is generated which relates the new
	post(sermon_post) ID to the old SB table, this is called: sb_transition_sermon

5) WP taxonomy terms are related to the new WP post(sermon_post)
	This is done using the transitional tables above and adding a row to the _term_relationships table

6) WP posts(attachment) are attached to posts(sermon_post)
	This is done using the transitional tables above to change post_parent from 0 to the correct ID
	
7) Insert references into the reference table

*/



// ========== 0) Initialization: Declare variables, get options. ==========
	
	// wordpress global class, for queries
	global $wpdb;

	// sermon browser information
	$sb_options = unserialize( base64_decode( get_option( 'sermonbrowser_options' ) ) );
	
	// sermon posts plugin info
	$sp_options = get_option( 'plugin_tlsp_options' );
	
	// get the mysql maximum allowed packet size
	$maxsize = $wpdb->get_results( "SHOW VARIABLES LIKE 'max_allowed_packet'" );
	$maxsize = $maxsize[0]->Value;
	
	// track time for operations
	$time = array();
	$time['total'] = time();
	
	// track errors
	$error = array();
	
	// the tables we need
	$tables = array(
		$wpdb->prefix."sermon_reference",
		$wpdb->prefix."sermon_thebible",
		$wpdb->prefix."sermon_biblebook"
	);

	// if the table does not exist, that's a fatal error
	$tables_count = 0;
	foreach ( $tables as $table )
	{
		if ( !$wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) )
		{
			wp_die( "<div class='wrap'><div class='error'><p><strong>Fatal Error!</strong> Please deactivate, and then re-activate the Sermon Posts plugin, a table was not found and needs to be reinitialized.</p></div></div>" );
		}
	}
	
// ========== 1) Relating SB terms with WP taxonomy terms ==========

	// reset data
	$data = '';
	
	// ===== Preacher
	// TODO: Nothing is done with the image information yet
	
	// record time for the operation
	$time['preacher'] = time();
	
	// make the transitional relational table if it does not exist
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_preacher` (
				`sb_id` int NOT NULL,
				`sp_id` int NOT NULL,
				PRIMARY KEY( `sb_id`, `sp_id` )
			)";
	$table = $wpdb->query( $sql );
	
	// grab the SB preachers
	$t = $wpdb->prefix.'sb_preachers';
	$sql = "SELECT
			`{$t}`.`id` AS `id`,
			`{$t}`.`name` AS `name`,
			`{$t}`.`description` AS `description`,
			`{$t}`.`image` AS `image`
			FROM {$t}";
	$sb_terms = $wpdb->get_results( $sql, OBJECT );
	
	// for each row, check if the term already exists as a WP term
	foreach ( $sb_terms as $sb_term )
	{
	
		// the old term id
		$old_id = (int)$sb_term->id;
		
		// a hack to get around an occasional empty name field
		if ( $sb_term->name == '' ) continue;
		
		// term_exists returns 0, the term id, or an array containing the id
		$wp_term = term_exists( $sb_term->name, 'tlsp_preacher' );
		
		// check if the term exists
		if ( $wp_term == 0 )
		{
		
			// if it has a description, add it
			if ( property_exists( $sb_term, 'description' ) )
			{
				$id = wp_insert_term( $sb_term->name, 'tlsp_preacher', array( 'description' => $sb_term->description ) );
			}
			else
			{
				$id = wp_insert_term( $sb_term->name, 'tlsp_preacher' );
			}
			
			// wp_insert_term() returns the term id in an array
			$new_id = (int)$id['term_id'];
		
		}
		else
		{
		
			// $wp_term may be returned as an integer or array
			if ( is_int( $wp_term ) == true )
			{
				$new_id = (int)$wp_term;
			}
			else 
			{
				$new_id = (int)$wp_term['term_id'];
			}
		
		}
		
		// add the values to the database in groups not exceeding mysql maxsize
		$data .= "( {$old_id}, {$new_id} ),";
		if( strlen( $data ) >= ( $maxsize - 200 ) )
		{
			$data = substr( $data, 0, strlen( $data ) - 1 );
			$table = $wpdb->query( "INSERT IGNORE INTO `{$wpdb->prefix}sb_transition_preacher` ( `sb_id`, `sp_id` ) VALUES {$data}" );
			$data = '';
		}
	
	}
	
	// at the end, there's usually some leftover data still needed to be passed to mysql
	if ( strlen( $data ) >= 3 )
	{
		$data = substr( $data, 0, strlen( $data ) - 1 );
		$table = $wpdb->query("INSERT IGNORE INTO `{$wpdb->prefix}sb_transition_preacher` ( `sb_id`, `sp_id` ) VALUES {$data}");
	}
	$data = '';
	
	// store the time the operation took
	$time['preacher'] = time() - $time['preacher'];
	
	// ===== Series
	// TODO: Nothing is done with the page id yet (what *is* it?)
	
	// record time for the operation
	$time['series'] = time();
	
	// make the transitional relational table if it does not exist
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_series` (
				`sb_id` int NOT NULL,
				`sp_id` int NOT NULL,
				PRIMARY KEY( `sb_id`, `sp_id` )
			)";
	$table = $wpdb->query( $sql );
	
	// grab the SB series
	$t = $wpdb->prefix.'sb_series';
	$sql = "SELECT
			`{$t}`.`id` AS `id`,
			`{$t}`.`name` AS `name`,
			`{$t}`.`page_id` AS `page_id`
			FROM {$t}";
	$sb_terms = $wpdb->get_results( $sql, OBJECT );
	
	// for each row, check if the term already exists as a WP term
	foreach ( $sb_terms as $sb_term )
	{
	
		// the old term id
		$old_id = (int)$sb_term->id;
		
		// a hack to get around an occasional empty name field
		if ( $sb_term->name == '' ) continue;
		
		// term_exists returns 0, the term id, or an array containing the id
		$wp_term = term_exists( $sb_term->name, 'tlsp_series' );
		
		// check if the term exists
		if ( $wp_term == 0 )
		{
			$id = wp_insert_term( $sb_term->name, 'tlsp_series' );
			$new_id = (int)$id['term_id'];
		}
		else
		{
		
			// $wp_term may be returned as an integer or array
			if ( is_int( $wp_term ) == true )
			{
				$new_id = (int)$wp_term;
			}
			else 
			{
				$new_id = (int)$wp_term['term_id'];
			}
		
		}
		
		// add the values to the database in groups not exceeding mysql maxsize
		$data .= "( {$old_id}, {$new_id} ),";
		if( strlen( $data ) >= ( $maxsize - 200 ) )
		{
			$data = substr( $data, 0, strlen( $data ) - 1 );
			$table = $wpdb->query( "INSERT IGNORE INTO `{$wpdb->prefix}sb_transition_series` ( `sb_id`, `sp_id` ) VALUES {$data}" );
			$data = '';
		}
	
	}
	
	// at the end, there's usually some leftover data still needed to be passed to mysql
	if ( strlen( $data ) >= 3 )
	{
		$data = substr( $data, 0, strlen( $data ) - 1 );
		$table = $wpdb->query("INSERT IGNORE INTO `{$wpdb->prefix}sb_transition_series` ( `sb_id`, `sp_id` ) VALUES {$data}");
	}
	$data = '';
	
	// store the time the operation took
	$time['series'] = time() - $time['series'];
	
	// ===== Tags
	
	// record time for the operation
	$time['tags'] = time();
	
	// make the transitional relational table if it does not exist
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_tag` (
				`sb_id` int NOT NULL,
				`sp_id` int NOT NULL,
				PRIMARY KEY( `sb_id`, `sp_id` )
			)";
	$table = $wpdb->query( $sql );
	
	// grab the SB tags
	$t = $wpdb->prefix.'sb_tags';
	$sql = "SELECT
			`{$t}`.`id` AS `id`,
			`{$t}`.`name` AS `name`
			FROM {$t}";
	$sb_terms = $wpdb->get_results( $sql, OBJECT );
	
	// for each row, check if the term already exists as a WP term
	foreach ( $sb_terms as $sb_term )
	{
	
		// the old term id
		$old_id = (int)$sb_term->id;
		
		// a hack to get around an occasional empty name field
		if ( $sb_term->name == '' ) continue;
		
		// term_exists returns 0, the term id, or an array containing the id
		$wp_term = term_exists( $sb_term->name, 'tlsp_tag' );
		
		// check if the term exists
		if ( $wp_term == 0 )
		{
			$id = wp_insert_term( $sb_term->name, 'tlsp_tag' );
			$new_id = (int)$id['term_id'];
		}
		else
		{
		
			// $wp_term may be returned as an integer or array
			if ( is_int( $wp_term ) == true )
			{
				$new_id = (int)$wp_term;
			}
			else 
			{
				$new_id = (int)$wp_term['term_id'];
			}
		
		}
		
		// add the values to the database in groups not exceeding mysql maxsize
		$data .= "( {$old_id}, {$new_id} ),";
		if( strlen( $data ) >= ( $maxsize - 200 ) )
		{
			$data = substr( $data, 0, strlen( $data ) - 1 );
			$table = $wpdb->query( "INSERT IGNORE INTO `{$wpdb->prefix}sb_transition_tag` ( `sb_id`, `sp_id` ) VALUES {$data}" );
			$data = '';
		}
	
	}
	
	// at the end, there's usually some leftover data still needed to be passed to mysql
	if ( strlen( $data ) >= 3 )
	{
		$data = substr( $data, 0, strlen( $data ) - 1 );
		$table = $wpdb->query("INSERT IGNORE INTO `{$wpdb->prefix}sb_transition_tag` ( `sb_id`, `sp_id` ) VALUES {$data}");
	}
	$data = '';
	
	// store the time the operation took
	$time['tags'] = time() - $time['tags'];
	
	// ===== Services (times stored in description)
	
	// record time for the operation
	$time['services'] = time();
	
	// make the transitional relational table if it does not exist
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_service` (
				`sb_id` int NOT NULL,
				`sp_id` int NOT NULL,
				PRIMARY KEY( `sb_id`, `sp_id` )
			)";
	$table = $wpdb->query( $sql );
	
	// grab the SB services
	$t = $wpdb->prefix.'sb_services';
	$sql = "SELECT
			`{$t}`.`id` AS `id`,
			`{$t}`.`name` AS `name`,
			`{$t}`.`time` AS `time`
			FROM {$t}";
	$sb_terms = $wpdb->get_results( $sql, OBJECT );
	
	// for each row, check if the term already exists as a WP term
	foreach ( $sb_terms as $sb_term )
	{
	
		// the old term id
		$old_id = (int)$sb_term->id;
		
		// a hack to get around an occasional empty name field
		if ( $sb_term->name == '' ) continue;
		
		// term_exists returns 0, the term id, or an array containing the id
		$wp_term = term_exists( $sb_term->name, 'tlsp_service' );
		
		// check if the term exists
		if ( $wp_term == 0 )
		{
		
			// if it has a time, add it
			if ( property_exists( $sb_term, 'time' ) )
			{
				$id = wp_insert_term( $sb_term->name, 'tlsp_service', array( 'description' => $sb_term->time ) );
			}
			else
			{
				$id = wp_insert_term( $sb_term->name, 'tlsp_service' );
			}
			
			// wp_insert_term() returns the term id in an array
			$new_id = (int)$id['term_id'];
		
		}
		else
		{
		
			// $wp_term may be returned as an integer or array
			if ( is_int( $wp_term ) == true )
			{
				$new_id = (int)$wp_term;
			}
			else 
			{
				$new_id = (int)$wp_term['term_id'];
			}
		
		}
		
		// add the values to the database in groups not exceeding mysql maxsize
		$data .= "( {$old_id}, {$new_id} ),";
		if( strlen( $data ) >= ( $maxsize - 200 ) )
		{
			$data = substr( $data, 0, strlen( $data ) - 1 );
			$table = $wpdb->query( "INSERT IGNORE INTO `{$wpdb->prefix}sb_transition_service` ( `sb_id`, `sp_id` ) VALUES {$data}" );
			$data = '';
		}
	
	}
	
	// at the end, there's usually some leftover data still needed to be passed to mysql
	if ( strlen( $data ) >= 3 )
	{
		$data = substr( $data, 0, strlen( $data ) - 1 );
		$table = $wpdb->query("INSERT IGNORE INTO `{$wpdb->prefix}sb_transition_service` ( `sb_id`, `sp_id` ) VALUES {$data}");
	}
	$data = '';
	
	// store the time the operation took
	$time['services'] = time() - $time['services'];
	
// ========== 2) Relating SB passages with SP passages ==========

	// set the time marker
	$time[ 'passages' ] = time();
	
	// make the transitional relational table if it does not exist
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_passage` (
				`sb_sermon_id` int NOT NULL,
				`start` int NOT NULL,
				`end` int NOT NULL,
				PRIMARY KEY( `sb_sermon_id`, `start`, `end` )
			)";
	$table = $wpdb->query( $sql );
	
	// output of this query is a list of arrays of: book id number, chapter id number, verse id number, sermon id number
	// first array is the start of the range, the second is the end of the range, and this pattern repeats through the list
	$query = 	"SELECT `{$wpdb->prefix}sb_books`.`id` AS `book`,
						`{$wpdb->prefix}sb_books_sermons`.`chapter` AS `chapter`,
						`{$wpdb->prefix}sb_books_sermons`.`verse` AS `verse`,
						`{$wpdb->prefix}sb_books_sermons`.`sermon_id` AS `sermon`
						FROM `{$wpdb->prefix}sb_books_sermons`
						JOIN `{$wpdb->prefix}sb_books`
						ON `{$wpdb->prefix}sb_books`.`name`=`{$wpdb->prefix}sb_books_sermons`.`book_name`
						ORDER BY `{$wpdb->prefix}sb_books_sermons`.`sermon_id`,
							`{$wpdb->prefix}sb_books_sermons`.`order`,
							`{$wpdb->prefix}sb_books`.`id`,
							`{$wpdb->prefix}sb_books_sermons`.`chapter`,
							`{$wpdb->prefix}sb_books_sermons`.`verse`";
	
	// array( book, chapter, verse, sermon id )
	$refs = $wpdb->get_results( $query, OBJECT );
	
	// initialize range
	$range = NULL;
	
	// loop with a twist: information from the N row and the N+1 row is combined into a single variable
	foreach ( $refs as $ref )
	{
	
		// if there are no ranges present yet, create the first passage as "from"
		if ( empty( $range ) == true )
		{
		
			$range['from'] = array(
				'book' => (int)$ref->book,
				'chapter' => (int)$ref->chapter,
				'verse' => (int)$ref->verse
			);
		
		}
		
		// create the next one as "to"
		else
		{
		
			$range['to'] = array(
				'book' => (int)$ref->book,
				'chapter' => (int)$ref->chapter,
				'verse' => (int)$ref->verse
			);
			
			// pass the range (now includes from and to) to be transformed into proper ranges
			$range = $this->Post2Array( array( 0 => $range ) );
			
			// we create a string of values so we can pass it to mysql as a batch, instead of one at a time (for efficiency)
			
			// I've run into errors due to incorrect verse numbers. Because of this, we'll fail
			// nicely here, adding an error so the user can report inconsistent verse counts.
			if ( intval($range[0][0]) != 0 && intval($range[0][1]) != 0 )
			{
				// append the string long form
				$data .= "( {$ref->sermon}, {$range[0][0]}, {$range[0][1]}),";
			}
			else
			{
				$error['attachverserange'][] = $ref->sermon;
			}
			
			// add data in chunks, not exceeding the mysql max size
			if( strlen( $data ) >= ( $maxsize - 200 ) )
			{
				$data = substr( $data, 0, strlen( $data ) - 1 );
				$wpdb->query( "INSERT INTO `{$wpdb->prefix}sb_transition_passage` ( `sb_sermon_id`, `start`, `end` ) VALUES {$data}" );
				$data = '';
			}
			
			// reset the range (the range just processed is now stored in $data)
			$range = NULL;
		
		}
	
	}
	
	// add leftover data
	if ( strlen( $data ) >= 3 )
	{
		$data = substr( $data, 0, strlen( $data ) - 1 );
		$wpdb->query( "INSERT INTO `{$wpdb->prefix}sb_transition_passage` ( `sb_sermon_id`, `start`, `end` ) VALUES {$data}" );
	}
	
	// completely reset the string
	$data = '';
	
	// store operation time
	$time[ 'passages' ] = time() - $time[ 'passages' ];
	
// ========== 3) Creating the WP posts (post_type=attachment) from the SB sermon files ==========
	
	// TODO: Figure out a way to move files, if the person wants them moved
	
	// store operation time
	$time[ 'sermon2attachmentpost' ] = time();
	
	// make the transition relational table if it does not exist
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_file` (
				`sb_id` int NOT NULL,
				`sp_id` int NOT NULL,
				PRIMARY KEY( `sb_id`, `sp_id` )
			)";
	$table = $wpdb->query( $sql );
	
	// grab the sermon and file information
	$sql = "SELECT `{$wpdb->prefix}sb_sermons`.`id` AS `sermon_id`,
					`{$wpdb->prefix}sb_stuff`.`id` AS `file_id`,
					`{$wpdb->prefix}sb_sermons`.`title` AS `title`,
					`{$wpdb->prefix}sb_sermons`.`datetime` AS `date`,
					`{$wpdb->prefix}sb_sermons`.`description` AS `description`,
					`{$wpdb->prefix}sb_stuff`.`name` AS `filename`,
					`{$wpdb->prefix}sb_stuff`.`type` AS `type`,
					`{$wpdb->prefix}sb_stuff`.`count` AS `downloadcount`
				FROM `{$wpdb->prefix}sb_sermons`
				JOIN `{$wpdb->prefix}sb_stuff`
				ON `{$wpdb->prefix}sb_stuff`.`sermon_id`=`{$wpdb->prefix}sb_sermons`.`id`
				ORDER BY `{$wpdb->prefix}sb_sermons`.`id`";
	$sermons = $wpdb->get_results( $sql, OBJECT );
	
	// for each file, create a new post as type=attachment
	$folder = get_bloginfo( 'wpurl' ) .'/'. $sb_options['upload_dir'];
	foreach ( $sermons as $sermon )
	{
	
		// setup attachment/post information
		$filename = $folder . $sermon->filename;
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$args = array(
			'post_content'	=> $sermon->description,
			'post_date'		=> $sermon->date,
			'post_date_gmt' => $sermon->date,
			'guid'			=> $filename,
			'post_title'	=> $sermon->title,
			'post_status'	=> 'inherit',
			'post_mime_type' => $wp_filetype['type']
		);
		
		// create the attachment post
		$return = wp_insert_attachment( $args );
		
		// returns post ID or 0 for failure
		if ( $return != 0 )
		{
		
			// relate the file with the transitional table (sb_transition_file)
			$sb_id = (int) $sermon->file_id;
			
			// append the string long form
			$data .= "( {$sb_id}, {$return} ),";

		}
		else
		{
			$error['createattachmentpost'][] = array( 'filename' => $filename, 'filetype' => $wp_filetype, 'args' => $args );
		}
		
		// pass the data in chunks not exceeding mysql max size
		if( strlen( $data ) >= ( $maxsize - 200 ) )
		{
			$data = substr( $data, 0, strlen( $data ) - 1 );
			$wpdb->query( "INSERT INTO `{$wpdb->prefix}sb_transition_file` ( `sb_id`, `sp_id` ) VALUES {$data}" );
			$data = '';
		}
		
	}
	
	// at the end, there's usually some leftover data still needed to be passed to mysql
	if ( strlen( $data ) >= 3 )
	{
		$data = substr( $data, 0, strlen( $data ) - 1 );
		$wpdb->query( "INSERT INTO `{$wpdb->prefix}sb_transition_file` ( `sb_id`, `sp_id` ) VALUES {$data}" );
	}
	
	// completely reset the string
	$data = '';
	
	// store operation time
	$time[ 'sermon2attachmentpost' ] = time() - $time[ 'sermon2attachmentpost' ];

// ========== 4) Creating the WP posts (post_type=sermon_post) from the SB sermons ==========

	// store operation time
	$time[ 'sermonposts' ] = time();
	
	// make the transition relational table if it does not exist
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_sermon` (
				`sb_id` int NOT NULL,
				`sp_id` int NOT NULL,
				PRIMARY KEY( `sb_id`, `sp_id` )
			)";
	$table = $wpdb->query( $sql );
	
	// grab the sermon information
	$query = "SELECT `{$wpdb->prefix}sb_sermons`.`id` AS `sermon_id`,
					`{$wpdb->prefix}sb_sermons`.`title` AS `title`,
					`{$wpdb->prefix}sb_sermons`.`datetime` AS `date`,
					`{$wpdb->prefix}sb_sermons`.`description` AS `description`
				FROM `{$wpdb->prefix}sb_sermons`
				ORDER BY `{$wpdb->prefix}sb_sermons`.`id`";
	$sermons = $wpdb->get_results( $query, OBJECT );
	
	// for each sermon, create a proper wordpress post
	foreach ( $sermons as $sermon )
	{
	
		// the post data
		// TODO: Allow option to change post_author
		$args = array(
			'comment_status' => 'closed', // opt
			'ping_status' => 'closed', // opt
			'post_author' => 1, // opt
			'post_content' => $sermon->description,
			'post_date' => $sermon->date,
			'post_date_gmt' => $sermon->date,
			'post_status' => 'publish', // opt
			'post_title' => $sermon->title,
			'post_type' => 'sermon_post'
		);
		$return = wp_insert_post( $args );
		
		// $return is either the post ID or 0 if failure to add post
		if ( $return != 0 )
		{
		
			// pass the data in chunks not exceeding mysql max size
			$return = (int)$return;
			$data .= "( {$sermon->sermon_id}, {$return} ),";
			if( strlen( $data ) >= ( $maxsize - 200 ) )
			{
				$data = substr( $data, 0, strlen( $data ) - 1 );
				$wpdb->query( "INSERT INTO `{$wpdb->prefix}sb_transition_sermon` ( `sb_id`, `sp_id` ) VALUES {$data}" );
				$data = '';
			}
		
		}
		else
		{
			$error['createsermonpost'][] = $args;
		}
	
	}
	
	// at the end, there's usually some leftover data still needed to be passed to mysql
	if ( strlen( $data ) >= 3 )
	{
		$data = substr( $data, 0, strlen( $data ) - 1 );
		$wpdb->query( "INSERT INTO `{$wpdb->prefix}sb_transition_sermon` ( `sb_id`, `sp_id` ) VALUES {$data}" );
	}
	
	// completely reset the string
	$data = '';
	
	// store operation time
	$time[ 'sermonposts' ] = time() - $time[ 'sermonposts' ];
	
// ========== 5) WP taxonomy terms are related to the new WP post(sermon_post) ==========
	
	// store operation time
	$time[ 'attachtaxonomy' ][ 'preacher' ] = time();
	
	// preacher:
	$query = 	"INSERT INTO `{$wpdb->prefix}term_relationships`
					( `object_id`, `term_taxonomy_id`, `term_order` )
				SELECT  `{$wpdb->prefix}sb_transition_sermon`.`sp_id` AS `object_id`,
						`{$wpdb->prefix}sb_transition_preacher`.`sp_id` AS `term_taxonomy_id`,
						'0' AS `term_order`
				FROM `{$wpdb->prefix}sb_sermons`
				JOIN `{$wpdb->prefix}sb_transition_sermon`
				ON `{$wpdb->prefix}sb_transition_sermon`.`sb_id`=`{$wpdb->prefix}sb_sermons`.`id`
				JOIN `{$wpdb->prefix}sb_transition_preacher`
				ON `{$wpdb->prefix}sb_transition_preacher`.`sb_id`=`{$wpdb->prefix}sb_sermons`.`preacher_id`";

	// insert
	$wpdb->query( $query );
	
	// update count:
	$query = "SELECT `{$wpdb->prefix}term_taxonomy`.`term_id`
			FROM `{$wpdb->prefix}term_taxonomy`
			WHERE `{$wpdb->prefix}term_taxonomy`.`taxonomy`='tlsp_preacher'";
	$results = $wpdb->get_col( $wpdb->prepare( $query ) );
	wp_update_term_count( $results, 'tlsp_preacher' );
	
	// store operation time
	$time[ 'attachtaxonomy' ][ 'preacher' ] = time() - $time[ 'attachtaxonomy' ][ 'preacher' ];
	$time[ 'attachtaxonomy' ][ 'tags' ] = time();
	
	// tags
	$query = 	"INSERT INTO `{$wpdb->prefix}term_relationships`
					( `object_id`, `term_taxonomy_id`, `term_order` )
				SELECT  `{$wpdb->prefix}sb_transition_sermon`.`sp_id` AS `object_id`,
						`{$wpdb->prefix}sb_transition_tag`.`sp_id` AS `term_taxonomy_id`,
						'0' AS `term_order`
				FROM `{$wpdb->prefix}sb_sermons_tags`
				JOIN `{$wpdb->prefix}sb_transition_sermon`
				ON `{$wpdb->prefix}sb_transition_sermon`.`sb_id`=`{$wpdb->prefix}sb_sermons_tags`.`sermon_id`
				JOIN `{$wpdb->prefix}sb_transition_tag`
				ON `{$wpdb->prefix}sb_transition_tag`.`sb_id`=`wp_sb_sermons_tags`.`tag_id`";

	// insert
	$wpdb->query( $query );

	// update count:
	$query = "SELECT `{$wpdb->prefix}term_taxonomy`.`term_id`
			FROM `{$wpdb->prefix}term_taxonomy`
			WHERE `{$wpdb->prefix}term_taxonomy`.`taxonomy`='tlsp_tag'";
	$results = $wpdb->get_col( $wpdb->prepare( $query ) );
	wp_update_term_count( $results, 'tlsp_tag' );
	
	// store operation time
	$time[ 'attachtaxonomy' ][ 'tags' ]= time() - $time[ 'attachtaxonomy' ][ 'tags' ];
	$time[ 'attachtaxonomy' ][ 'series' ] = time();
	
	// series
	$query = "INSERT INTO `{$wpdb->prefix}term_relationships` ( `object_id`, `term_taxonomy_id`, `term_order` )
			SELECT `{$wpdb->prefix}sb_transition_sermon`.`sp_id` AS `object_id`,
			`{$wpdb->prefix}sb_transition_series`.`sp_id` AS `term_taxonomy_id`,
			'0' AS `term_order`
			FROM `{$wpdb->prefix}sb_sermons`
			JOIN `{$wpdb->prefix}sb_transition_series`
			ON `{$wpdb->prefix}sb_transition_series`.`sb_id`=`{$wpdb->prefix}sb_sermons`.`series_id`
			JOIN `{$wpdb->prefix}sb_transition_sermon`
			ON `{$wpdb->prefix}sb_transition_sermon`.`sb_id`=`{$wpdb->prefix}sb_sermons`.`id`";
	
	// insert
	$wpdb->query( $query );

	// update count:
	$query = "SELECT `{$wpdb->prefix}term_taxonomy`.`term_id`
			FROM `{$wpdb->prefix}term_taxonomy`
			WHERE `{$wpdb->prefix}term_taxonomy`.`taxonomy`='tlsp_series'";
	$results = $wpdb->get_col( $wpdb->prepare( $query ) );
	wp_update_term_count( $results, 'tlsp_series' );
	
	// store operation time
	$time[ 'attachtaxonomy' ][ 'series' ] = time() - $time[ 'attachtaxonomy' ][ 'series' ];
	$time[ 'attachtaxonomy' ][ 'services' ] = time();
	
	// services
	$query = "INSERT INTO `{$wpdb->prefix}term_relationships` ( `object_id`, `term_taxonomy_id`, `term_order` )
			SELECT `{$wpdb->prefix}sb_transition_sermon`.`sp_id` AS `object_id`,
			`{$wpdb->prefix}sb_transition_service`.`sp_id` AS `term_taxonomy_id`,
			'0' AS `term_order`
			FROM `{$wpdb->prefix}sb_sermons`
			JOIN `{$wpdb->prefix}sb_transition_service`
			ON `{$wpdb->prefix}sb_transition_service`.`sb_id`=`{$wpdb->prefix}sb_sermons`.`service_id`
			JOIN `{$wpdb->prefix}sb_transition_sermon`
			ON `{$wpdb->prefix}sb_transition_sermon`.`sb_id`=`{$wpdb->prefix}sb_sermons`.`id`";
	
	// insert
	$wpdb->query( $query );

	// update count
	$query = "SELECT `{$wpdb->prefix}term_taxonomy`.`term_id`
			FROM `{$wpdb->prefix}term_taxonomy`
			WHERE `{$wpdb->prefix}term_taxonomy`.`taxonomy`='tlsp_service'";
	$results = $wpdb->get_col( $wpdb->prepare( $query ) );
	wp_update_term_count( $results, 'tlsp_service' );
	
	// store operation time
	$time[ 'attachtaxonomy' ][ 'services' ] = time() - $time[ 'attachtaxonomy' ][ 'services' ];
	
// ========== 6) WP posts(attachment) are attached to posts(sermon_post) ==========

	// store operation time
	$time[ 'attachtopost' ] = time();

	// grab the id's of the posts and attachments
	$query = "SELECT `{$wpdb->prefix}sb_transition_file`.`sp_id` AS `id`,
					`{$wpdb->prefix}sb_transition_sermon`.`sp_id` AS `parent`
			FROM `{$wpdb->prefix}sb_stuff`
			JOIN `{$wpdb->prefix}sb_transition_file`
			ON `{$wpdb->prefix}sb_transition_file`.`sb_id`=`{$wpdb->prefix}sb_stuff`.`id`
			JOIN `{$wpdb->prefix}sb_transition_sermon`
			ON `{$wpdb->prefix}sb_transition_sermon`.`sb_id`=`{$wpdb->prefix}sb_stuff`.`sermon_id`";
	
	$results = $wpdb->get_results( $query, ARRAY_A );

	// for each file, link it to the appropriate post
	foreach ( $results as $pair )
	{
	
		wp_update_post( array( 'ID' => $pair['id'], 'post_parent' => $pair['parent'] ) );
	
	}
	
	// store operation time
	$time[ 'attachtopost' ] = time() - $time[ 'attachtopost' ];
	
// ========== 7) Insert reference ranges into table ==========

	// store operation time
	$time[ 'insertreference' ] = time();
	
	// insert data
	$query = "INSERT INTO `{$wpdb->prefix}sermon_reference` ( `sermon`, `start`, `end` )
			SELECT `{$wpdb->prefix}sb_transition_sermon`.`sp_id` AS `sermon`,
					`{$wpdb->prefix}sb_transition_passage`.`start` AS `start`,
					`{$wpdb->prefix}sb_transition_passage`.`end` AS `end`
			FROM `{$wpdb->prefix}sb_transition_passage`
			JOIN `{$wpdb->prefix}sb_transition_sermon`
			ON `{$wpdb->prefix}sb_transition_sermon`.`sb_id`=`{$wpdb->prefix}sb_transition_passage`.`sb_sermon_id`";
	$results = $wpdb->query( $query );
	
	// store operation time
	$time[ 'insertreference' ] = time() - $time[ 'insertreference' ];


// ========== 8) Final operations and print out final message ==========

	// total operation time
	$time['total'] = time() - $time['total'];
	
	// all details stored
	add_option( 'plugin_tlsp_importreport', array( $time, $error ) );
	
	// update the settings
	$sp_options = get_option( 'plugin_tlsp_options' );
	$sp_options['initialimport'] = true;
	update_option( 'plugin_tlsp_options', $sp_options );
	
	// html output
	echo '<h2>Import Results</h2><p>The results below show the statistics of your import operation.</p>';	
	echo "<p><strong>Total operation time:</strong> {$time['total']} seconds.</p>";
	echo "<p><strong>Errors:</strong> ";
	if ( empty( $error ) ) echo "No errors noted, you are ready to go!";
	else echo "Errors were reported during the import process. View the import technical report for details.";
	echo "</p>";	
	echo "<p>The detailed report is available on the import page, <a href='?import=tlsp_importer'>here</a>.</p>";

// ===== End Security Check ===== //
endif;
// ============================== //
?>