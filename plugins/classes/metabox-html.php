<?php
// this is the html for the custom post meta box

if( isset ( $security ) )
{
if ( $security == 1 )
{

?>
<div class="tlsp_wrap">
	<label for="tlsp_text" />Data to Save:</label>
	<input type="text" id="tlsp_text" name="tlsp_text" value="<?php echo $tlsp_text; ?>" size="35" />
</div>
<?php

}
}
// it would be even better if you could make it display the same 404 error they would get anywhere else