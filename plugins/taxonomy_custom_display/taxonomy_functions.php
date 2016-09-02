<?php

// This grabs an array of all posts (by ID) that have a taxonomy 'tcd_singer' and taxonomy item of ID '14'
$array = get_objects_in_term( 14, 'tcd_singer', '' ); // 14 is  item 'test'
print_r($array);
echo '<br />';

// This grabs a PHP object for the taxonomy 'tcd_singer' object 14
$object = get_term(14, 'tcd_singer');
print_r($object);
echo '<br />';

$taxonomies = array(
	'tcd_singer'
	// you can get_terms for multiple taxonomies at once
	// if any of the taxonomies do not exist, it will return WP_Error
	// can use a string instead of an array
);
$args = array(
	'get' => 'all' // lists *all* taxonomy terms
	// see wp-includes/taxonomy.php line 722 for additional paramaters
);
$array = get_terms($taxonomies, $args);
//$array = get_terms('tcd_singer'); // this default method lists only the taxonomy terms associated with the current post
print_r($array);

?>

<!-- This is the HTML presented on the post page, for reference use. -->
<div class="inside">
<div class="tagsdiv" id="tcd_singer">
	<div class="jaxtag">
	<div class="nojs-tags hide-if-js">
	<p>Add or remove tags</p>
	<textarea name="tax_input[tcd_singer]" rows="3" cols="20" class="the-tags" id="tax-input-tcd_singer"></textarea></div>
 		<div class="ajaxtag hide-if-no-js">
		<label class="screen-reader-text" for="new-tag-tcd_singer">Singers</label>

		<div class="taghint">Add New Singer</div>
		<p><input id="new-tag-tcd_singer" name="newtag[tcd_singer]" class="newtag form-input-tip" size="16" autocomplete="off" value="" type="text">
		<input class="button tagadd" value="Add" tabindex="3" type="button"></p>
	</div>
	<p class="howto">Separate tags with commas</p>
		</div>
	<div class="tagchecklist"></div>
</div>
<p class="hide-if-no-js"><a href="#titlediv" class="tagcloud-link" id="link-tcd_singer">Choose from the most used tags</a></p>
</div>


Array
(
    [0] =&gt; stdClass Object
        (
            [term_id] =&gt; 13
            [name] =&gt; Bobby Blue
            [slug] =&gt; bobby-blue
            [term_group] =&gt; 0
            [term_taxonomy_id] =&gt; 13
            [taxonomy] =&gt; tcd_singer
            [description] =&gt; He singes jazz and makes women cry.
            [parent] =&gt; 0
            [count] =&gt; 1
        )

    [1] =&gt; stdClass Object
        (
            [term_id] =&gt; 14
            [name] =&gt; test
            [slug] =&gt; test
            [term_group] =&gt; 0
            [term_taxonomy_id] =&gt; 14
            [taxonomy] =&gt; tcd_singer
            [description] =&gt; 
            [parent] =&gt; 0
            [count] =&gt; 2
        )

    [2] =&gt; stdClass Object
        (
            [term_id] =&gt; 15
            [name] =&gt; test2
            [slug] =&gt; test2
            [term_group] =&gt; 0
            [term_taxonomy_id] =&gt; 15
            [taxonomy] =&gt; tcd_singer
            [description] =&gt; 
            [parent] =&gt; 0
            [count] =&gt; 1
        )

)
