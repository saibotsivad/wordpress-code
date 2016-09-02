<?php

// ===== Security Check ===== //
if( isset ( $security ) ) {
// ========================== //

// if the page is accessed using the "submit" button, save the changes
if ( isset( $_POST['tlsp_OptionsPage_submit'] ) )
{

	if ( wp_verify_nonce( $_POST['tlsp_options_saveoptions'], 'tlsp_options_saveoptions' ) )
	{
		// grab the options
		$options = get_option( 'plugin_tlsp_options' );
		// loop through them, checking for changes
		foreach ( $options['options'] as $key => $option )
		{
			if ( isset( $_POST[$key] ) )
			{
				if ( $option['type'] == 'int' )
				{
					$options['options'][$key]['val'] = (int)$_POST[$key];
				}
				elseif ( $option['type'] == 'text' )
				{
					$options['options'][$key]['val'] = wp_kses( $_POST[$key] );
				}
				elseif ( $option['type'] == 'tax' )
				{
					$term_id = (int)$_POST[$key];
					// if the term exists
					if ( get_term_by( 'id', $term_id, $key ) )
					{
						$options['options'][$key]['val'] = (int)$_POST[$key];
					}
					// otherwise set it as zero
					else
					{
						$options['options'][$key]['val'] = 0;
					}
				}
			}
		}
		// update the options
		update_option( 'plugin_tlsp_options', $options );
		// then print out a message, if all goes well
		echo '<div id="message" class="updated fade"><p>Options updated!</p></div>';
	}
	else
	{
		echo '<div id="message" class="error fade"><p>Failed to save options, nonce invalid.</p></div>';
	}

}

// grab the plugin options:
$options = get_option( 'plugin_tlsp_options' );
$options = $options['options'];
// now print out the options page
?>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br></div>
	<h2>Sermon Posts Options</h2>
	<div id="ajax-response"></div>
	<form id="" name="" action="options-general.php?page=tl-sermon-posts" method="post">
		<input type="hidden" name="" value="" />
		<?php wp_nonce_field( 'tlsp_options_saveoptions', 'tlsp_options_saveoptions' ); ?>
		<p>There aren't many options here yet, only <?php echo count( $options ); ?> of them, but you
		can help add some by leaving comments and suggestions at the <a href="http://tobiaslabs.com/sermonposts/">Sermon Post website</a>, or by
		sending the plugin <a href="mailto:sermonposts@tobiaslabs.com">author an email</a>.</p>
		<table class="form-table">
			<tbody>
<?php
foreach ( $options as $key => $option )
{
	echo "<tr valign='top'>";
	echo "<th scope='row'><label for='{$key}'>{$option['name']}</label></th>";
	if ( $option['type'] == 'int' || $option['type'] == 'text' )
	{
		echo "<td><input name='{$key}' id='{$key}' value='{$option['val']}' class='regular-text' type='text' />";
	}
	elseif ( $option['type'] == 'tax' )
	{
		$terms = get_terms( $key );
		if ( $terms )
		{
			echo "<td><select name='{$key}'>";
			echo "<option value='0'></option>";
			foreach ( $terms as $term )
			{
				$select = '';
				if ( $option['val'] == $term->term_id ) $select = " selected='yes'";
				echo "<option value='{$term->term_id}'{$select}>{$term->name}</option>";
			}
			echo "</select> <span class='description'>{$option['desc']}</span></td>";
		}
	}
	echo "</tr>";
}
?>
			</tbody>
		</table>
		<p>That's all for now, I guess.</p>
		<input name="tlsp_OptionsPage_submit" id="tlsp_OptionsPage_submit" class="button-primary" type="submit" value="Save Changes" />
		<h3>Additional Links</h3>
		<p>If you have finished importing your sermons from the Sermon Browser plugin, or
		if you are wise beyond your years, you can delete the transitional database tables
		and the Sermon Browser plugin tables through the interface available
		<a href="options-general.php?page=tl-sermon-posts&editdatabase=1">here</a>.</p>
	</form>
</div>

<!--
	<p>Some things this will eventually manage:</p>
		<p>Default service, series, preacher, mp3 album art, featured image for sermons (different for each series/etc?),
		display the books of the bible in alphabetical or normal order?</p>
		<p>Maybe enabling ID3 tags should be optional because of server constraints?</p>
		<br><br><br>
		<p>If you have suggestions for what other options should be manageable, please send
		them to me: <a href="mailto:sermonposts@tobiaslabs.com">sermonposts@tobiaslabs.com</a></p>
-->

<?php
// ===== End Security Check ===== //
}
// ============================== //
?>