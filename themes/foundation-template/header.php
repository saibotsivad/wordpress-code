<?php
/**
 *
 * Header for the theme
 *
 * @package WordPress
 * @subpackage THEME NAME
 */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
		<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); // bloginfo('stylesheet_directory') ?>" />
		<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<?php wp_head(); // Always immediately before closing the head ?>
	</head>
<body <?php body_class(); ?>>

<?php /* Begin theme design after this point. Some tags listed here, just to be handy.
bloginfo('name')
	name = Blog Name
	description = Just another WordPress blog
	admin_email = admin@example.com
	url = http://example/home
	stylesheet_directory = http://example/home/wp/wp-content/themes/child-theme
	stylesheet_url = http://example/home/wp/wp-content/themes/child-theme/style.css
	template_directory = http://example/home/wp/wp-content/themes/parent-theme
	template_url = http://example/home/wp/wp-content/themes/parent-theme
	rss2_url = http://example/home/feed
	atom_url = http://example/home/feed/atom
	rss_url = http://example/home/feed/rss
	rdf_url = http://example/home/feed/rdf
	pingback_url = http://example/home/wp/xmlrpc.php
	comments_rss2_url = http://example/home/comments/feed
	comments_atom_url = http://example/home/comments/feed/atom

	A basic pre-filled is shown below, delete or edit as desired.
*/ ?>

<div id="header">
	<h1><a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"><?php bloginfo('name'); ?></a></h1>
	<?php wp_nav_menu( array( 'container_class' => 'main-menu', 'theme_location' => 'primary' ) ); ?>
</div><!-- #header -->