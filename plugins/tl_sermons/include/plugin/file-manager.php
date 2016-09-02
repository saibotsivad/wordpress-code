<?php
/**
 * Copied and Modified from the Media Library administration panel file
 * This whole dumb file could go away if I could add hooks to the file manager screen...
*/

// ===== Security Check ===== //
if( isset ( $security ) ) {
// ========================== //

// global wordpress class
global $wpdb;

// the query arguments are held here
$query_args = array();

// get the options for managing files
$options = get_option( 'plugin_tlsp_options' );

$per_page = (int)$options['options']['manage-files-count']['val'];

// list of taxonomies
$taxonomies = array(
	'Preacher'	=> get_terms('tlsp_preacher'),
	'Series' 	=> get_terms('tlsp_series'),
	'Service'	=> get_terms('tlsp_service')
);

// determine taxonomy filters
$tax_filter = array();
$tax_ids = '';
$tax_count = 0;
foreach ( $taxonomies as $tax => $data )
{
	if ( isset( $_GET[$tax] ) && (int)$_GET[$tax] != 0 )
	{
		// append the ids to filter
		$tax_ids .= (int)$_GET[$tax] .',';
		// add to the count
		$tax_count++;
		// store for later use
		$tax_filter[$tax] = (int)$_GET[$tax];
	}
	unset( $data );
}

// if there were taxonomies, we'll create the appropriate query strings
$q_join = '';
$q_having = '';
$q_count = '';
if ( $tax_count > 0 )
{
	// remove the comma
	$tax_ids = substr( $tax_ids, 0, strlen( $tax_ids ) - 1 );
	// setup the queries
	$q_join = "   JOIN `{$wpdb->prefix}term_relationships`
				ON `{$wpdb->prefix}term_relationships`.`object_id` = `{$wpdb->prefix}posts`.`ID`
				AND `{$wpdb->prefix}term_relationships`.`term_taxonomy_id` IN( {$tax_ids} ) ";
	$q_having = " HAVING `NumberOfTaxonomies` = {$tax_count} ";
	$q_count = ", COUNT(*) AS `NumberOfTaxonomies` ";
	
	// we also reset the current page to 1, if the user added a filter while on a numbered page
	if ( isset( $_GET['tax_count'] ) && $tax_count != (int)$_GET['tax_count'] )
	{
		unset( $_GET['paged'] );
	}
}

// determine pagination number
$offset = 0;
$current_page = 1;
if ( isset( $_GET['paged'] ) && (int)$_GET['paged'] != 0 && (int)$_GET['paged'] != 1 )
{
	$offset = $per_page * (int)$_GET['paged'];
	$current_page = (int)$_GET['paged'];
}

// these are the things we can order by
$tablehead = array(
	array( 'order'=>'asc', 'html'=>'post_title', 'name'=>'Title' ),
	array( 'order'=>'asc', 'html'=>'post_date', 'name'=>'Date' ),
	array( 'order'=>'asc', 'html'=>'guid', 'name'=>'Filename' )
);

// check if the user ordered things
$q_order = '';
$dummy = array();
foreach ( $tablehead as $th )
{
	if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) && $_GET['orderby'] == $th['html'] )
	{
		if ( $_GET['order'] == 'asc' )
		{
			$q_order = " ORDER BY `a`.`{$th['html']}` {$_GET['order']} ";
			$th['order'] = 'desc';
		}
		elseif ( $_GET['order'] == 'desc' )
		{
			$q_order = " ORDER BY `a`.`{$th['html']}` {$_GET['order']} ";
			$th['order'] = 'asc';
		}
	}
	$dummy[] = $th;
}
$tablehead = $dummy;
// fallback default order
if ( $q_order == '' ) $q_order = " ORDER BY `a`.`post_date` DESC ";

// assemble the query
$query = "SELECT
SQL_CALC_FOUND_ROWS
`a`.`ID` AS `post_id`,
`a`.`post_date` AS `post_date`,
`a`.`post_title` AS `post_title`,
`a`.`post_name` AS `post_name`,
`a`.`guid` AS `post_guid`,
`{$wpdb->prefix}posts`.`ID` AS `parent_id`,
`{$wpdb->prefix}posts`.`post_title` AS `parent_title`,
`{$wpdb->prefix}posts`.`post_name` AS `parent_name`,
`{$wpdb->prefix}posts`.`guid` AS `parent_guid`{$q_count}
FROM `{$wpdb->prefix}posts`
JOIN `{$wpdb->prefix}posts` `a` ON `{$wpdb->prefix}posts`.`ID`=`a`.`post_parent`
{$q_join}
WHERE `{$wpdb->prefix}posts`.`post_type`='sermon_post'
GROUP BY `{$wpdb->prefix}posts`.`ID`, `a`.`ID`
{$q_having}
{$q_order}
LIMIT {$per_page}
OFFSET {$offset}";

// make the query
$files = $wpdb->get_results( $query );
//wp_die( var_dump( $files ) );

// the secondary query determines the number of rows without LIMIT
$total_files = $wpdb->get_results( "SELECT FOUND_ROWS() AS `rows`" );
$total_files = (int)$total_files[0]->rows;

// calculate the number of pages
$total_pages = ceil( $total_files / $per_page ) - 1;

// generate links
$link = get_bloginfo('url').'/wp-admin/edit.php?post_type=sermon_post&page=tlsp-file-manager';
$shortlink = chop( $_SERVER['PHP_SELF'], 'edit.php' );

// build the table
?>
<div class="wrap">
	<div id="icon-upload" class="icon32"></div>
	<h2>Manage Files</h2>
	<div id="ajax-response"></div>
	<form id="sermon-files-filter" action="edit.php" method="get">
		<?php wp_nonce_field('tlsp_managefiles','tlsp_managefiles'); ?>
		<input type="hidden" name="post_type" value="sermon_post" />
		<input type="hidden" name="page" value="tlsp-file-manager" />
		<input type="hidden" name="tax_count" value="<?php echo $tax_count; ?>" />
		<input type="hidden" name="paged_ref" value="<?php echo $current_page; ?>" />
<!--		<p class="search-box">
			<label class="screen-reader-text" for="post-search-input">Search Files:</label>
			<input id="post-search-input" name="s" value="" type="text">
			<input value="Search Files" class="button" type="submit">
		</p> -->
		<div class="alignleft actions">
			Filter Files:
			<select name="date">
				<option selected="selected" value="0">All dates</option>
				<option value="201109">September 2011</option>
			</select>

<?php
			foreach ( $taxonomies as $name => $tax )
			{
				echo "<select name='{$name}'>";
				if ( isset( $tax_filter[$name] ) ) echo "<option value='0'>All {$name}</option>";
				else echo "<option selected='selected' value='0'>All {$name}</option>";
				foreach ( $tax as $obj )
				{
					if ( isset( $tax_filter[$name] ) && $tax_filter[$name] == $obj->term_id ) echo "<option selected='selected' value='{$obj->term_id}'>{$obj->name}</option>";
					else echo "<option value='{$obj->term_id}'>{$obj->name}</option>";
				}
				echo '</select>';
			}
?>
			<input id="post-query-submit" value="Filter" class="button-secondary" type="submit">

		</div>

		<div class="tablenav">
			<div class="tablenav-pages">
<?php /* ===== Pagination ===== */

// print out the file count
echo "<span class='displaying-num'>{$total_files} items</span>";

// only display pagination numbers if there are actual pages
if ( $total_pages > 1 )
{
	
	// if this is the first page, disable the << and < buttons
	$disable_text = '';
	if ( $current_page == 1 ) $disable_text = ' disabled';
	
	// left arrows
	$arrow_num = $current_page - 1;
	echo "<a class='first-page{$disable_text}' href='{$link}&paged=1' title='Go to the first page'>&#171;</a>";
	echo "<a class='first-page{$disable_text}' href='{$link}&paged={$arrow_num}' title='Go to the previous page'>&#139;</a>";
	
	// input box
	echo '<span class="paging-input">';
	echo "<input class='current-page' type='text' size='2' value='{$current_page}' name='paged' title='Current page' />";
	echo "of <span class='total-pages'>{$total_pages}</span></span>";
	
	// if this is the last page, disable the > and >> buttons
	$disable_text = '';
	if ( $current_page == $total_pages ) $disable_text = ' disabled';
	
	// right arrows
	$arrow_num = $current_page + 1;
	echo "<a class='next-page{$disable_text}' href='{$link}&paged={$arrow_num}' title='Go to the next page'>&#155;</a>";
	echo "<a class='last-page{$disable_text}' href='{$link}&paged={$total_pages}' title='Go to the last page'>&#187;</a>";
	
}
/* ===== End of Pagination ===== */ ?>
			</div>
		</div>	

		<table class="widefat">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
					<?php foreach ( $tablehead as $th ) : ?>
						<th id="<?php echo $th['html']; ?>" class="sortable <?php echo $th['order']; ?>">
							<a href="<?php echo $link; ?>&orderby=<?php echo $th['html']; ?>&order=<?php echo $th['order']; ?>">
								<span><?php echo $th['name']; ?></span>
								<span class='sorting-indicator'></span>
							</a>
						</th>
					<?php endforeach; ?>
				</tr>
			</thead>
<?php if ( count( $files ) ) : ?>
			<tbody>
<?php foreach ( $files as $file ) : ?>
				<tr>
					<th scope="row" class="check-column"><input name="file[]" value="<?php echo $file->post_id; ?>" type="checkbox"></th>
					<td><strong><a href="<?php echo $link; ?>&editfileid=<?php echo $file->post_id; ?>" title="Edit this file"><?php echo $file->parent_title; ?></a></strong>
						<div class="row-actions">
							<a href="<?php echo $link; ?>&editfileid=<?php echo $file->post_id; ?>" title="Edit this file">Edit File</a> |
							<a href="<?php echo get_bloginfo('url'); ?>/wp-admin/post.php?post=<?php echo $file->parent_id; ?>&action=edit" title="Edit this Sermon">Edit Sermon</a> |
							<span class="trash"><a class="submitdelete" title="Move this item to the Trash" href="<?php echo $link; ?>&trashfile=<?php echo $file->post_id; ?>">Delete</a></span>
						</div>
					</td>
					<td><?php echo reset( explode( ' ', $file->post_date ) ); ?></td>
					<td><?php echo end( explode( "/", $file->post_guid ) ); ?>
						<div class="row-actions">
							<a href="<?php echo $file->post_guid; ?>">Direct File Link</a> |
							<a href="<?php echo get_bloginfo('url').'/'.$file->parent_name.'/'.$file->post_name.'/'; ?>">Attachment Page</a>
						</div>
					</td>
				</tr>

				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
<?php else: ?>
		</table>
		<p>No files found.</p>
<?php endif; ?>
		
		<div class="alignleft actions">
			<select name="bulk_action">
				<option value="0" selected="selected">Bulk Actions</option>
				<option value="trash">Delete Permanently</option>
			</select>
			<input value="Apply" name="doaction" id="doaction" class="button-secondary action" type="submit">
		</div>

	</form>
</div>

<?php
// ===== End Security Check ===== //
}
// ============================== //
?>