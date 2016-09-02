<?php
// this is the html for the custom post meta box

if( isset ( $security ) )
{
	if ( $security == 1 )
	{



?>
<div class="tlsp_wrap">
	<p>Here are the options:</p>
	<form method="post">
		<input type="hidden" name="tlsp_import" value="true" />
		<!--
		<p><label for="tlsp_folder">New sermon folder location:</label> <input type="text" id="tlsp_folder" name="tlsp_folder" value="<?php echo $sb_options['upload_dir']; ?>" size="40" />
			(Changing the folder location will <strong>copy all sermons</strong> into the new folder location!</p>
		-->
		<p><input type="submit" id="tlsp_import_go" name="tlsp_import_go" value="GO!" /></p>
	</form>
	<p>Options you could add:
	Do you want the new sermons to allow comments? (defualt to no)
	
</div>
<?php

}
}
// it would be even better if you could make it display the same 404 error they would get anywhere else