<?php
// This is how information is stored:

// in custom tables
$table_sermon = $wpdb->prefix . "sermon_reference";
	// sermon post id | start verse number | end verse number
	// 134 | 1 | 4 --> sermon id 134, Genesis 1:1-4
	// multiple rows possible per sermon
$table_thebible = $wpdb->prefix . "sermon_thebible";
	// unique key | id of book | id of chapter | id of verse
	// 1 | 1 | 1 | 1 --> Genesis 1:1
$table_biblebook = $wpdb->prefix . "sermon_biblebook";
	// unique key | book name
	// 3 | Leviticus

// in WP posts
register_post_type( 'sermon_post' , $args );
	// the sermon text information
	// actual uploaded files are stored as attachments to the post type

// in WP taxonomies
register_taxonomy( 'tlsp_preacher', $posts, $args );
	// the preacher name and information
register_taxonomy( 'tlsp_series', $posts, $args );
	// the sermon series and information on that series
register_taxonomy( 'tlsp_service', $posts, $args );
	// the possible church services (e.g., Sunday 9am, Sunday 11am, Wednesday 9pm, etc.)
register_taxonomy( 'tlsp_tag', $posts, $args );
	// tags for sermons (e.g., "holiness", "worldview", "firstworldproblems")

// in the options table
add_option( 'plugin_tlsp_options', $array );
	// general plugin options
add_option( 'plugin_tlsp_taxonomyimages', $array ); // not yet implemented
	// stores the relation between the taxonomy id and the WP post(attachment) id, so you can retrieve the thumbnail

// as post meta
add_post_meta( $post_id, 'tl_sermonposts_external', $args, true );
	// stores information about external files, i.e., files not stored as uploaded WP attachments
	$args = array(
		array(
			'url' => 'http://site.com/file.mp3',
			'type' => 'mp3', // used for icon display
		),
		array( /* more than one file possible */ )
	);


?>