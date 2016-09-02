<?php
$themename = "Skinbu";
$themeversion = "1.5.0";
$shortname = "sk";

if ( function_exists('register_sidebar') )
    register_sidebar(array(
        'before_widget' => '',
        'after_widget' => '</div>',
        'before_title' => '<div id="box"><h2>',
        'after_title' => '</h2>',
    ));

function review_shortcode( $atts, $content = null ) {
   return '<div id="review"><div style="overflow:auto; height: 64px;">' . $content . '</div></div>';
}
add_shortcode('review', 'review_shortcode');
    
function link_shortcode( $atts, $content = null ) {
   return '<div id="slink">' . $content . '</div>';
}
add_shortcode('link', 'link_shortcode');


?>