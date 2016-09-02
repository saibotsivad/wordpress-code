<?php /* remove the default date entry field */ ?>
<style>
div.curtime{
	display: none !important;
}
</style>


<?php /* ========== JavaScript ========== */ ?>
<script language="javascript">

<?php /* add a new passage reference field (From+Through) when clicked */ ?>
function tlspAddPassage(iteration) {
	newpassage = '<table style="border:1px solid #DFDFDF;padding:4px;-moz-border-radius: 4px 4px 4px;"><tr><td style="padding:0;margin:0;padding-right:15px;">From:</td><td style="padding:0;margins:0;">';
	newpassage += ( '<select style="width:120px;" name="<?php echo $prefix; ?>ref[' + iteration + '][from][book]">' );
	newpassage += '<?php echo $html['books']; ?></select> ';
	newpassage += ( '<input type="text" maxlength="3" size="1" name="<?php echo $prefix; ?>ref[' + iteration + '][from][chapter]"/> ' );
	newpassage += ( '<input type="text" maxlength="3" size="1" name="<?php echo $prefix; ?>ref[' + iteration + '][from][verse]" /> ' );
	newpassage += '</td></tr><tr><td style="padding:0;margins:0;">Through:</td><td style="padding:0;margins:0;"> ';
	newpassage += ( '<select style="width:120px;" name="<?php echo $prefix; ?>ref[' + iteration + '][to][book]">' );
	newpassage += '<?php echo $html['books']; ?></select> ';
	newpassage += ( '<input type="text" maxlength="3" size="1" name="<?php echo $prefix; ?>ref[' + iteration + '][to][chapter]" /> ' );
	newpassage += ( '<input type="text" maxlength="3" size="1" name="<?php echo $prefix; ?>ref[' + iteration + '][to][verse]" /> ' );
	newpassage += '</td></tr></table> ';
	jQuery('#tlsp_sermonpassage').append( newpassage );
	document.getElementById('tlsp_addmorepassages').innerHTML = ( '<small><a href="javascript:tlspAddPassage(' + ( iteration + 1 ) + ')">Add more</a></small>' );
}

<?php /* remove passage field when clicked */ ?>
function tlspRemovePassage(passage) {
	removepassage = '.tlsp-reference-' + passage;
	jQuery( removepassage ).remove();
}

<?php /* END OF SCRIPTS */ ?>
</script>