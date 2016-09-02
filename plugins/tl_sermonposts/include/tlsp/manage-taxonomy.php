<?php

// ===== Security Check ===== //
if( isset ( $security ) ) :
// ========================== //

// add a new taxonomy
if( isset( $_POST['submit'] ) && isset( $_GET['addnew'] ) )
{
	if ( wp_verify_nonce( $_POST['tlsp_addnew_'.$_GET['addnew']], 'tlsp_addnew_'.$_GET['addnew'] )
		&& !term_exists( $_POST['name'], 'tlsp_'.$_GET['addnew'] ) )
	{
		$args = array(
			'description' => $_POST['description'],
			'slug' => $_POST['slug'],
		);
		$results = wp_insert_term( $_POST['name'], 'tlsp_'.$_GET['addnew'], $args );
		if ( is_array( $results ) ) echo "<div id='message' class='updated fade'><p>New {$type} created: {$_POST['name']}</p></div>";
		else echo '<div id="message" class="error fade"><p>Invalid taxonomy or term, please try again.</p></div>';
	}
}
// change an old taxonomy
elseif ( isset( $_POST['savechanges'] ) )
{
	// grab the number
	$key = absint( key( $_POST['savechanges'] ) );
	// grab the taxonomy type
	$tax = 'tlsp_' . $_POST['tax_type'];
	
	if ( wp_verify_nonce( $_POST['tlsp_edit_tax'], 'tlsp_edit_tax' ) && get_term_by( 'id', $key, $tax ) )
	{
		// grab the values
		$args = array(
			'name' => $_POST['tax'][$key]['name'],
			'description' => $_POST['tax'][$key]['desc'],
			'slug' => $_POST['tax'][$key]['slug']
		);
		$results = wp_update_term( $key, $tax, $args );
		$new_type = ucfirst( $type );
		if ( is_array( $results ) ) echo "<div id='message' class='updated fade'><p>{$new_type} updated!</p></div>";
		else echo "<div id='message' class=error fade'><p>{$new_type} failed to update, do you have sufficient priveleges?</p></div>";
	}
}
// confirm delete a taxonomy or a group of taxonomies
elseif ( isset( $_POST['doaction'] ) && @$_POST['bulk_action'] == 'trash' &&
		isset( $_POST['tax_term'] ) && wp_verify_nonce( $_POST['tlsp_edit_tax'], 'tlsp_edit_tax' ) )
{
	$to_deletes = $_POST['tax_term'];
	$tax = 'tlsp_' . $_POST['tax_type'];
	$new_type = ucfirst( $type );
	$error = false;
	foreach ( $to_deletes as $index => $to_delete )
	{
		$key = absint( $to_delete );
		$result = wp_delete_term( absint( $to_delete ), $tax );
		if ( !$result ) $error = true;
	}
	if ( $error ) echo "<div id='message' class=error fade'><p>{$new_type} failed to delete, do you have sufficient priveleges?</p></div>";
	else echo "<div id='message' class='updated fade'><p>{$new_type} deleted!</p></div>";
}

// grab the taxonomies
$args = array(
	'orderby' => 'name',
	'hide_empty' => false
);
$taxes = get_terms( 'tlsp_'.$type, $args );

// this is the main display, the "Add New" dialogue is hidden by
// default, and exposed when clicking "Add New"
?>
<script language='javascript' type='text/javascript'>
	function hideAddNew() {
		if (document.getElementById) {
			document.getElementById('addnew_screen').style.display = 'none';
			document.getElementById('addnew_button').style.visibility = 'visible';
		}
	}

	function showAddNew() {
		if (document.getElementById) {
			document.getElementById('addnew_screen').style.display = 'block';
			document.getElementById('addnew_button').style.visibility = 'hidden';
		}
	}
	
	function showEditTax(element_id) {
		if (document.getElementById) {
			document.getElementById('view_tax_'+element_id).style.display = 'none';
			document.getElementById('edit_tax_'+element_id).style.display = '';
		}
	}
	
	function hideEditTax(element_id) {
		if (document.getElementById) {
			document.getElementById('view_tax_'+element_id).style.display = '	';
			document.getElementById('edit_tax_'+element_id).style.display = 'none';
		}
	}
</script> 
<div class="wrap">
	<div id="icon-edit" class="icon32"><br></div>
	<h2><?php echo $name; ?>
	<div id="addnew_button" style="display:inline;">
		<a class="button add-new-h2" href="#" onclick="showAddNew();return false;">Add New</a>
	</div>
	</h2>
	<div id="ajax-response"></div>
	<div id="addnew_screen" style="display:none;">
		<h2>Add New <?php echo $name; ?></h2>
		<form id="add<?php echo $type; ?>" action="edit.php?post_type=sermon_post&page=tl-sermon-posts-<?php echo $type; ?>&addnew=<?php echo $type; ?>" method="post">
			<?php wp_nonce_field( "tlsp_addnew_{$type}", "tlsp_addnew_{$type}" ); ?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="name">Name</label></th>
						<td>
							<input name="name" id="name" value="" class="regular-text" type="text" /><br />
							<span class="description">Human readable name.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="description">Description</label></th>
						<td>
							<textarea name="description" id="description" class="regular-text" cols="50" rows="6"></textarea><br />
							<span class="description">In a few words, explain this <?php echo $type; ?>.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="slug">Slug</label></th>
						<td>
							<input name="slug" id="slug" value="" class="regular-text" type="text" /><br />
							<span class="description">The "slug" is the URL-friendly version of the name. It will be auto-generated from the name if you don't make one. It is usually all lowercase and contains only letters, numbers, and hyphens..</span>
						</td>
					</tr>
					<tr valign="bottom">
						<td><p>
							<input class="button-primary" type="submit" name="submit" value="Add New <?php echo ucwords($type); ?>" />
							<a href="#" onclick="hideAddNew();return false;" class="button-secondary">Cancel</a>
						</p></td>
					</tr>
				<tbody>
			</table>
		</form>
	</div>
	<form id="<?php echo $type; ?>-tax-filter" action="" method="post">
		<?php wp_nonce_field( "tlsp_edit_tax", "tlsp_edit_tax" ); ?>
		<input type="hidden" name="tax_type" value="<?php echo $type; ?>" />
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
					<th><?php echo $name; ?> Name</th>
					<th>Description</th>
					<th>Slug</th>
					<th>Count</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $taxes as $tax ) : ?>
				<tr id="view_tax_<?php echo $tax->term_id; ?>">
					<th scope="row" class="check-column"><input name="tax_term[]" value="<?php echo $tax->term_id; ?>" type="checkbox"></th>
					<td style="width:20%;"><?php echo $tax->name; ?>
						<div class="row-actions">
							<a href="#" onclick="showEditTax('<?php echo $tax->term_id; ?>');return false;" title="Edit this <?php echo $name; ?>">Edit</a>
						</div>
					</td>
					<td style="width:50%;"><?php echo $tax->description; ?></td>
					<td><?php echo $tax->slug; ?></td>
					<td><a href="<?php echo site_url(); ?>/wp-admin/edit.php?tlsp_<?php echo $type; ?>=<?php echo $tax->slug; ?>&post_type=sermon_post"><?php echo $tax->count; ?></a></td>
				</tr>
				<tr style="display:none;" id="edit_tax_<?php echo $tax->term_id; ?>">
					<th></th>
					<td>
						<label><?php echo $name; ?> Name</label><br />
						<input type="text" value="<?php echo $tax->name; ?>" name="tax[<?php echo $tax->term_id; ?>][name]" />
					</td>
					<td>
						<label>Description</label><br />
						<textarea id="desc_<?php echo $tax->term_id; ?>" name="tax[<?php echo $tax->term_id; ?>][desc]" rows="6" cols="40"><?php echo $tax->description; ?></textarea>
					</td>
					<td>
						<label>Slug</label><br />
						<input type="text" value="<?php echo $tax->slug; ?>" name="tax[<?php echo $tax->term_id; ?>][slug]" />
						<p style="padding-top:20px;"><a href="#" onclick="hideEditTax('<?php echo $tax->term_id; ?>');return false;" class="button-secondary">Cancel</a></p>
						<p><input type="submit" class="button-primary" name="savechanges[<?php echo $tax->term_id; ?>]" value="Save Changes" /></p>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
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
endif;
// ============================== //
?>