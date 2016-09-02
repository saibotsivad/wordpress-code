<?php

// ===== Security Check ===== //
if( isset ( $security ) ) {
// ========================== //

	// wordpress globals
	global $post;
	global $wpdb;
	
	// grab the options
	$options = get_option( 'plugin_tlsp_options' );

	$html = '';
	
	// get the books listing from the database
	$books = $wpdb->get_results( "SELECT `{$wpdb->prefix}sermon_biblebook`.`name` FROM `{$wpdb->prefix}sermon_biblebook`", ARRAY_A );
	
	// grab the associated verse ranges, using some self joins
	$query = 	"SELECT	`rvw`.`sermon` AS `id`,
						`c`.`book` AS `start_book`,
						`c`.`chapter` AS `start_chapter`,
						`c`.`verse` AS `start_verse`,
						`m`.`book` AS `end_book`,
						`m`.`chapter` AS `end_chapter`,
						`m`.`verse` AS `end_verse`
				FROM `{$wpdb->prefix}sermon_reference` `rvw`
				JOIN `{$wpdb->prefix}sermon_thebible` `c` ON `rvw`.`start`=`c`.`id`
				JOIN `{$wpdb->prefix}sermon_thebible` `m` ON `rvw`.`end`=`m`.`id`
				WHERE `rvw`.`sermon`=". intval( $post->ID );
	$ranges = $wpdb->get_results( $query, ARRAY_A );
	
	// post meta data
	$metadata = get_post_custom($post->ID);
	
	// complete taxonomy lists
	$taxes['tlsp_service'] = get_terms( 'tlsp_service', array( 'get' => 'all' ) );
	$taxes['tlsp_series'] = get_terms( 'tlsp_series', array( 'get' => 'all' ) );
	$taxes['tlsp_preacher'] = get_terms( 'tlsp_preacher', array( 'get' => 'all' ) );
	
	// associated taxonomy terms
	$taxterms['tlsp_service'] = get_the_terms( $post->ID, 'tlsp_service' );
	$taxterms['tlsp_series'] = get_the_terms( $post->ID, 'tlsp_series' );
	$taxterms['tlsp_preacher'] = get_the_terms( $post->ID, 'tlsp_preacher' );
	
	// taxonomies to html
	foreach ( $taxes as $name => $tax )
	{
	
		$html[$name] = '';
		
		$html[$name] .= '<select style="width:268px;" name="'.$name.'">';
		$html[$name] .= '<option value="0"></option>';
		foreach ( $tax as $term )
		{
			if ( !empty( $taxterms[$name] ) )
			{
				$selected = '';
				if ( $term->term_id == $taxterms[$name][0]->term_id )
				{
					$selected = " selected='yes'";
				}
				$html[$name] .= "<option value='{$term->term_id}'{$selected}>{$term->name}</option>";
			}
			else
			{
				$pre_text = '';
				$selected = '';
				// only auto-select the taxonomy if it's on the "Add New" page
				if ( @$_GET['post_type'] == 'sermon_post' && $options['options'][$name]['val'] == $term->term_id )
				{
					$pre_text = "Default: ";
					$selected = " selected='yes'";
				}
				$html[$name] .= "<option value='{$term->term_id}'{$selected}>{$pre_text}{$term->name}</option>";
			}
		}
		$html[$name] .= '</select>';
	
	}
	
	// date html
	$timestamp = strtotime( get_the_date() );
	$real_day = date( 'd', $timestamp );
	$real_year = date( 'Y', $timestamp );
	$real_month = date( 'F', $timestamp );
	$html['date'] = '<select id="mm" name="mm" tabindex="4">';
	$i = 1;
	do {
		$month = date('F', mktime(0,0,0, $i, 1, 2010) );
		// I place the space in the conditional so the displayed <option> element doesn't have additional characters
		if ( $real_month == $month )
		{
			$selected = ' selected="selected"';
		}
		else
		{
			$selected = ' ';
		}
		if ( $i <10 )
		{
			$html['date'] .= '<option'. $selected .' value="0'. $i .'">'. $month .'</option>';
		}
		else
		{
			$html['date'] .= '<option'. $selected .' value="'. $i .'">'. $month .'</option>';
		}
		$i++;
	} while ( $i < 13 );
	$html['date'] .= '</select>';
	$html['date'] .= '<input id="jj" name="jj" value="'. $real_day .'" size="2" maxlength="2" tabindex="4" autocomplete="off" type="text">, <input id="aa" name="aa" value="'. $real_year .'" size="4" maxlength="4" tabindex="4" autocomplete="off" type="text"> @ <input id="hh" name="hh" value="'. get_the_time( 'H', $post->ID ) .'" size="2" maxlength="2" tabindex="4" autocomplete="off" type="text"> : <input id="mn" name="mn" value="'. get_the_time( 'i', $post->ID ) .'" size="2" maxlength="2" tabindex="4" autocomplete="off" type="text">';

	// books of Bible to html dropdown ( <select> elements are handled in the later html since they are not always the same )
	$i = 0;
	$html['books'] = '<option value="0"></option>';
	foreach ( $books as $book )
	{
		$i++;
		$html['books'] .= '<option value="'.$i.'">'.$book['name'].'</option>';
	}

	// we also remove new lines so it can also be used in the javascript
	$html['books'] = preg_replace( "(\r\n|\r|\n)", '', $html['books'] );	

	// the final HTML output

	// use a nonce field for data entry authentication
	echo '<input type="hidden" name="tlsp_MetaBox_nonce" id="tlsp_noncename" value="'.wp_create_nonce( 'tlsp_metabox_save' ).'" />';

	// echo out the javascript/css files
	include( 'javascript-admin.php' );
	
	?>

<table class="form-table">
	<tbody>
		<tr>
			<td>Description:</td>
			<td><textarea cols="42" rows="7" id="content" name="content"><?php echo $post->post_content; ?></textarea></td>
		</tr>
		<tr>
			<td>Preacher:</td>
			<td>
				<?php echo $html['tlsp_preacher']; ?>
			</td>
		</tr>
		<tr>
			<td>Series:</td>
			<td>
				<?php echo $html['tlsp_series']; ?>
			</td>
		</tr>
		<tr>
			<td>Service:</td>
			<td>
				<?php echo $html['tlsp_service']; ?>
			</td>
		</tr>
		<tr>
			<td>Date:</td>
			<td>
				<?php echo $html['date']; ?>
			</td>
		</tr>
		<tr>
			<td>Bible Passage(s):</td>
			<td>
				<?php

// if no verse ranges are specified, print the default
if ( empty( $ranges ) )
{
	?>
	<div id="tlsp_sermonpassage">
		<table style="border:1px solid #DFDFDF;padding:4px;-moz-border-radius: 4px 4px 4px;">
			<tr>
				<td style="padding:0;margin:0;padding-right:15px;">From:</td>
				<td style="padding:0;margins:0;">
					<select style="width:120px;" name="tlsp_ref[0][from][book]">
						<?php echo $html['books']; ?>
					</select>
					<input type="text" maxlength="3" size="1" name="tlsp_ref[0][from][chapter]"/>
					<input type="text" maxlength="3" size="1" name="tlsp_ref[0][from][verse]" />
				</td>
			</tr>
			<tr>
				<td style="padding:0;margins:0;">Through:</td>
				<td style="padding:0;margins:0;">
					<select style="width:120px;" name="tlsp_ref[0][to][book]">
						<?php echo $html['books']; ?>
					</select>
					<input type="text" maxlength="3" size="1" name="tlsp_ref[0][to][chapter]" />
					<input type="text" maxlength="3" size="1" name="tlsp_ref[0][to][verse]" />
				</td>
			</tr>
		</table>
	</div>
	<div id="tlsp_addmorepassages"><small><a href="javascript:tlspAddPassage(1)">Add more passages.</a></small></div>
	<?php
}
// if there are verse ranges, we'll need to display them
else
{
	$counter = 0;
	echo '<div id="tlsp_sermonpassage">';
	foreach ( $ranges as $range )
	{
	
		?>
		<table style="border:1px solid #DFDFDF;padding:4px;-moz-border-radius: 4px 4px 4px;" class="tlsp-reference-<?php echo $counter; ?>">
			<tr>
				<td style="padding:0;margin:0;padding-right:15px;">From:</td>
				<td style="padding:0;margins:0;">
					<select style="width:120px;" name="tlsp_ref[<?php echo $counter; ?>][from][book]">
<?php
$i = 1;
foreach ( $books as $book )
{
	if ( $i == $range['start_book'] )
	{
		echo "<option value='{$i}' selected='selected'>{$book['name']}</option>";
	}
	else
	{
		echo "<option value='{$i}'>{$book['name']}</option>";
	}
	$i++;
}
?>
					</select>
					<input type="text" maxlength="3" size="1" name="tlsp_ref[<?php echo $counter; ?>][from][chapter]" value="<?php echo $range['start_chapter']; ?>" />
					<input type="text" maxlength="3" size="1" name="tlsp_ref[<?php echo $counter; ?>][from][verse]" value="<?php echo $range['start_verse']; ?>" />
				</td>
				<td style="padding:0;margins:0;"><a href="javascript:tlspRemovePassage(<?php echo $counter; ?>)" style="cursor:pointer;">delete</a></td>
			</tr>
			<tr>
				<td style="padding:0;margins:0;">Through:</td>
				<td style="padding:0;margins:0;">
					<select style="width:120px;" name="tlsp_ref[<?php echo $counter; ?>][to][book]">
<?php
$i = 1;
foreach ( $books as $book )
{
	if ( $i == $range['end_book'] )
	{
		echo "<option value='{$i}' selected='selected'>{$book['name']}</option>";
	}
	else
	{
		echo "<option value='{$i}'>{$book['name']}</option>";
	}
	$i++;
}
?>
					</select>
					<input type="text" maxlength="3" size="1" name="tlsp_ref[<?php echo $counter; ?>][to][chapter]" value="<?php echo $range['end_chapter']; ?>" />
					<input type="text" maxlength="3" size="1" name="tlsp_ref[<?php echo $counter; ?>][to][verse]" value="<?php echo $range['end_verse']; ?>" />
				</td>
			</tr>
		</table>
		<?php
		$counter++;
	}
	echo '</div>';
	echo '<div id="tlsp_addmorepassages"><small><a href="javascript:tlspAddPassage('.$counter.')">Add more passages.</a></small></div>';
}

				?>
			</td>
		</tr>
		<tr>
			<td>Files:</td>
			<td>
				<a class="button-secondary thickbox" id="tlsp_thickbox_add_file" title="Add Sermon Files" href="<?php echo $this->thickbox_url; ?>" title="Browse">Add/View Sermon Files</a>
			</td>
		</tr>
	</tbody>
</table>
<?php	
// ===== End Security Check ===== //
}
// ============================== //
?>