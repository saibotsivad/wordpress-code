<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
	<head>

		<title><?php wp_title(''); if (function_exists('is_tag') and is_tag()) { ?>Tag Archive for <?php echo $tag; } if (is_archive()) { ?> archive<?php } elseif (is_search()) { ?> Search for <?php echo wp_specialchars($s,1); } if ( !(is_404()) && (is_search()) or (is_single()) or (is_page()) or (function_exists('is_tag') and is_tag()) or (is_archive()) ) { ?> | <?php } ?> <?php bloginfo('name'); ?></title>

		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<meta name="robots" content="index, follow" />

		<link rel="shortcut icon" type="image/ico" href="<?php bloginfo('template_url'); ?>/favicon.ico" />
		<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
		<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
		<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

		<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" type="text/css" media="screen" />
		<?php wp_head(); ?>

		<!--[if lt IE 8]>
		<style media="screen" type="text/css">
		#col1, #col2 {
			width:100%;
		}
		</style>
		<![endif]-->
		
	</head>
	<body>

		<div id="header">
			<p><a href="http://matthewjamestaylor.com/blog/split-page-3-column-css-liquid-layout" title="'Split Page' 3 column CSS Liquid-Layout">&laquo; Back to the CSS article</a> by <a href="http://matthewjamestaylor.com">Matthew James Taylor</a></p>
			<h1>'Split Page' 3 Column CSS Liquid-Layout (Center: Fixed-Width, Left/Right: Fluid)</h1>
			<h2>No CSS hacks. SEO friendly 1-3-2 column order. Cross-browser. Equal height columns.</h2>
		</div>