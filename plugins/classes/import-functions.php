<?php
// these are the functions to import sermons
if( isset ( $security ) ) {	if ( $security == 1 ) {

/* ===== Operational Procedure =====

Acronyms: Sermon Browser (SB), Sermon Posts (SP), WordPress (WP)
Tables Created: sb_transition_preacher, sb_transition_serie, sb_transition_tag, sb_transition_passage, sb_transition_file, sb_transition_sermon

1) Relating SB terms with WP taxonomy terms
	These are the tables of interest: sb_preachers, sb_series, sb_tags
	Each item on each table will be replaced by WP taxonomy terms, so
	a test is run to see if the term already exists in the WP taxonomy
	table. For each taxonomy term in the SB tables, a relation is
	noted between it and the term in the WP table, and this relation is stored
	in a table called: sb_transition_preacher, sb_transition_series, sb_transition_tag

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

*/

/* ========== 0) We'll wrap everything in some HTML, that way we can ouput progress ========== */
echo '<div id="wrap">';

/* ========== 1) Relating SB terms with WP taxonomy terms ========== */
echo '<p>Relating Sermon Browser terms with WordPress terms...</p>';

$taxonomies = array(
	array( 'old' => 'preachers',	'new' => 'preacher' ),
	array( 'old' => 'series',		'new' => 'series' ),
	array( 'old' => 'tags',			'new' => 'tags' )
);
foreach ( $taxonomies as $taxonomy )
{
	// grab the old data
	$sb_terms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_{$taxonomy['old']}", OBJECT );

	// for each row, check if the term already exists as a WP term
	foreach ( $sb_terms as $sb_term )
	{
		
		// the old term id
		$old_id = $sb_term->id;
		
		// a hack to get around an occasional empty sp_tags.name field
		if ( $taxonomy['old'] == 'tags' )
		{
			if ( $sb_term->name == '' ) continue;
		}
		
		// term_exists returns 0 if false, and either an integer of the term id, or an array containing the id (see WP documentation for details)
		$wp_term = term_exists( $sb_term->name, "tlsp_{$taxonomy['new']}" );
		
		// it doesn't exist
		if (  $wp_term == 0 )
		{
			// the tags don't have descriptions
			if ( $taxonomy['old'] == 'tags' )
			{
				$id = wp_insert_term( $sb_term->name, "tlsp_{$taxonomy['new']}" );
			}
			// but the preacher and series may
			else
			{
				$id = wp_insert_term( $sb_term->name, "tlsp_{$taxonomy['new']}", array( 'description' => $sb_term->description ) );
			}
			// wp_insert_term() returns the term id in an array
			$new_id = $id['term_id'];
		}
		
		// it already exists
		else
		{
			if ( is_int( $wp_term ) == true )
			{
				$new_id = (int) $wp_term;
			}
			else 
			{
				$new_id = (int) $wp_term['term_id'];
			}
		}
		
		// check whether the table exists, make it if it doesn't
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_{$taxonomy['new']}` ( `sb_id` int NOT NULL, `sp_id` int NOT NULL, PRIMARY KEY( `sb_id`, `sp_id` ) )");
		
		// insert the relational information into the database
		// errors are turned into warnings, since duplication errors are possible/likely, and they are non-critical
		$table = $wpdb->query("INSERT IGNORE INTO `{$wpdb->prefix}sb_transition_{$taxonomy['new']}` ( `sb_id`, `sp_id` ) VALUES ( {$old_id}, {$new_id} )");
	
	}

}

/* ========== 2) Relating SB passages with SP passages ========== */
echo '<p>Relating Sermon Browser passages with Sermon Post passaged</p>';

// check whether the transition table exists, make it if it doesn't
$table = $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_passage` ( `sb_sermon_id` int NOT NULL, `start` int NOT NULL, `end` int NOT NULL, PRIMARY KEY( `sb_sermon_id`, `start`, `end` ) )");

// output of this query is a list of arrays of: book id number, chapter id number, verse id number, sermon id number
// first array is the start of the range, the second is the end of the range, and this pattern repeats through the list
$query = "SELECT `{$wpdb->prefix}sb_books`.`id` AS `book`, `{$wpdb->prefix}sb_books_sermons`.`chapter` AS `chapter`,
`{$wpdb->prefix}sb_books_sermons`.`verse` AS `verse`, `{$wpdb->prefix}sb_books_sermons`.`id` AS `sermon`
FROM `{$wpdb->prefix}sb_books_sermons`
JOIN `{$wpdb->prefix}sb_books`
ON `{$wpdb->prefix}sb_books`.`name` = `{$wpdb->prefix}sb_books_sermons`.`book_name`
ORDER BY `{$wpdb->prefix}sb_books_sermons`.`sermon_id`, `{$wpdb->prefix}sb_books_sermons`.`order`,
`{$wpdb->prefix}sb_books`.`id`, `{$wpdb->prefix}sb_books_sermons`.`chapter`, `{$wpdb->prefix}sb_books_sermons`.`verse`";

// array( book, chapter, verse, sermon id )
$refs = $wpdb->get_results( $query, OBJECT );

$range = NULL;
// loop with a twist: information from the N row and the N+1 row is combined into a single variable
foreach ( $refs as $ref )
{
	if ( empty( $range ) == true )
	{
		$range['from'] = array( 'book' => (int)$ref->book, 'chapter' => (int)$ref->chapter, 'verse' => (int)$ref->verse );
	}
	else
	{
		$range['to'] = array( 'book' => (int)$ref->book, 'chapter' => (int)$ref->chapter, 'verse' => (int)$ref->verse );

		// transform from numbers to a range
		$range = tlsp_post2array( array( 0 => $range ) );

		// add to database, ignore duplicates
		$wpdb->query( "INSERT INTO `{$wpdb->prefix}sb_transition_passage` ( `sb_sermon_id`, `start`, `end` ) VALUES ( {$ref->sermon}, {$range[0][0]}, {$range[0][1]})" );

		$range = NULL;
	}
}

/* ========== 3) Creating the WP posts (post_type=attachment) from the SB sermon files ========== */
echo '<p>Creating WordPress attachments from the Sermon Browser files...</p>';
/*
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
*/

/* // TODO: this section
// copy files around if desired
if ( $_POST['tlsp_folder'] != $sb_options['upload_dir'] )
{
	// copy the files, and then...
	echo '<p>Copying files to new location (this could take some time)...</p>';
}
*/

// check whether the transition table exists, make it if it doesn't
$table = $wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_file` ( `sb_id` int NOT NULL, `sp_id` int NOT NULL, PRIMARY KEY( `sb_id`, `sp_id` ) )" );

// grab the sermon and file information
$query = "SELECT
`{$wpdb->prefix}sb_sermons`.`id` AS 		`sermon_id`,
`{$wpdb->prefix}sb_stuff`.`id` AS 			`file_id`,
`{$wpdb->prefix}sb_sermons`.`title` AS 		`title`,
`{$wpdb->prefix}sb_sermons`.`datetime` AS 	`date`,
`{$wpdb->prefix}sb_sermons`.`description` AS `description`,
`{$wpdb->prefix}sb_stuff`.`name` AS 		`filename`,
`{$wpdb->prefix}sb_stuff`.`type` AS 		`type`,
`{$wpdb->prefix}sb_stuff`.`count` AS 		`downloadcount`
FROM `{$wpdb->prefix}sb_sermons`
JOIN `{$wpdb->prefix}sb_stuff` ON `{$wpdb->prefix}sb_stuff`.`sermon_id` = `{$wpdb->prefix}sb_sermons`.`id`
ORDER BY `{$wpdb->prefix}sb_sermons`.`id`";
$sermons = $wpdb->get_results( $query, OBJECT );

foreach ( $sermons as $sermon )
{

	// post information
	$filename = get_bloginfo( 'wpurl' ) .'/'. $sb_options['upload_dir'] . $sermon->filename;
	$wp_filetype = wp_check_filetype( basename( $filename ), null );
	$args = array(
		'post_content' => $sermon->description,
		'post_date' => $sermon->date,
		'post_date_gmt' => $sermon->date,
		'post_title' => $sermon->title,
		'post_status' => 'inherit',
		'post_mime_type' => $wp_filetype['type']
	);

	// add the attachment post (returns post ID or 0)
	$return = wp_insert_attachment( $args );

	// relate the SB file with the SP transitional table (sb_transition_file)
	$sb_id = (int) $sermon->file_id;
	if ( $return != 0 )
	{
		$wpdb->query( "INSERT INTO `{$wpdb->prefix}sb_transition_file` ( `sb_id`, `sp_id` ) VALUES ( {$sb_id}, {$return} )" );
	}
	else
	{
		$error++;
		echo '<p><strong>Error!</strong> The file was not able to be added.</p>';
	}
	
}


/* ========== 4) Creating the WP posts (post_type=sermon_post) from the SB sermons ========== */
echo '<p>Creating WordPress sermon posts from the Sermon Browser sermons...</p>';
/*
4) Creating the WP posts (post_type=sermon_post) from the SB sermons
	Using data from the SB sermons table and the recently created transitional tables, a
	new WP post is generated. An additional table is generated which relates the new
	post(sermon_post) ID to the old SB table, this is called: sb_transition_sermon
*/

// check whether the transition table exists, make it if it doesn't
$table = $wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sb_transition_sermon` ( `sb_id` int NOT NULL, `sp_id` int NOT NULL, PRIMARY KEY( `sb_id`, `sp_id` ) )" );

// grab the sermon information
$query = "SELECT
`{$wpdb->prefix}sb_sermons`.`id` AS 		`sermon_id`,
`{$wpdb->prefix}sb_sermons`.`title` AS 		`title`,
`{$wpdb->prefix}sb_sermons`.`datetime` AS 	`date`,
`{$wpdb->prefix}sb_sermons`.`description` AS `description`
FROM `{$wpdb->prefix}sb_sermons`
ORDER BY `{$wpdb->prefix}sb_sermons`.`id`";
$sermons = $wpdb->get_results( $query, OBJECT );

foreach ( $sermons as $sermon )
{
	
	// prepare the post
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
	
	// insert the post
	$return = wp_insert_post( $args );

	// relate the SB sermon with the SP transitional table (sb_transition_sermon)
	$sb_id = (int) $sermon->sermon_id;
	if ( $return != 0 )
	{
		$wpdb->query( "INSERT INTO `{$wpdb->prefix}sb_transition_sermon` ( `sb_id`, `sp_id` ) VALUES ( {$sb_id}, {$return} )" );
	}
	else
	{
		$error++;
		echo '<p><strong>Error!</strong> The sermon was not able to be added.</p>';
	}

}

/* ========== 5) WP taxonomy terms are related to the new WP post(sermon_post) ========== */
//	This is done using the transitional tables above and adding a row to the _term_relationships table

/* ========== 6) WP posts(attachment) are attached to posts(sermon_post) ========== */
//	This is done using the transitional tables above to change post_parent from 0 to the correct ID




/* ========== 7) Print out final message ========== */
if ( $error != 0 )
{
	?>
	<p>It looks like there were errors during the process!</p>
	<?php
}
else
{
	?>
	<p><strong>Finished!</strong></p>
	<?php
}
?>
</div>
<?php
} } // end of security check
?>