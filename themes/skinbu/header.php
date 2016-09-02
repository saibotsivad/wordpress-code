<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head>
<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" />
<link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/favicon.ico" />
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>

<body>
<!-- HEADER -->
<div id="wrapper">

	<div id="header">
	
		<div id="raccoglitore">
			<div id="logo"><h1><a href="<?php echo get_option('home'); ?>" title="<?php bloginfo('description'); ?>"><?php bloginfo('name'); ?></a></h1><h2><?php bloginfo('description'); ?></h2></div>
			<div id="rss"><a href="<?php bloginfo('rss2_url'); ?>" ><img alt="Tieniti sempre aggiornato sulle ultime di Skimbu!" src="<?php bloginfo('template_directory'); ?>/images/rss.png" /></a></div>
		</div>
		<div id="navbar_top"></div>
		<div id="navbar">
			<ul>
				<li class="page_item"><a id="linkbutton" href="<?php echo get_option('home'); ?>">Home</a></li> <?php wp_list_pages('sort_order=desc&title_li='); ?>
			</ul>
		</div>
		<div id="navbar_bottom"></div>
	</div>
			
	<div id="content">
	<!-- END HEADER -->

