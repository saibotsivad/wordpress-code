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

	</head>
	<body>

		<div id="header">
			<p><a href="http://matthewjamestaylor.com/blog/perfect-multi-column-liquid-layouts" title="Perfect multi-column liquid layouts - iPhone compatible">&laquo; Back to the CSS article</a> by <a href="http://matthewjamestaylor.com">Matthew James Taylor</a></p>
			<h1>The Perfect 3 Column Liquid Layout (Percentage widths)</h1>
			<h2>No CSS hacks. SEO friendly. No Images. No JavaScript. Cross-browser &amp; iPhone compatible.</h2>
			<ul>
				<li><a href="http://matthewjamestaylor.com/blog/perfect-3-column.htm" class="active">3 Column <span>Holy Grail</span></a></li>
				<li><a href="http://matthewjamestaylor.com/blog/perfect-3-column-blog-style.htm">3 Column <span>Blog Style</span></a></li>
				<li><a href="http://matthewjamestaylor.com/blog/perfect-2-column-left-menu.htm">2 Column <span>Left Menu</span></a></li>
				<li><a href="http://matthewjamestaylor.com/blog/perfect-2-column-right-menu.htm">2 Column <span>Right Menu</span></a></li>
				<li><a href="http://matthewjamestaylor.com/blog/perfect-2-column-double-page.htm">2 Column <span>Double Page</span></a></li>
				<li><a href="http://matthewjamestaylor.com/blog/perfect-full-page.htm">1 Column <span>Full Page</span></a></li>
				<li><a href="http://matthewjamestaylor.com/blog/perfect-stacked-columns.htm">Stacked <span>columns</span></a></li>
			</ul>
			<p id="layoutdims">Measure columns in: <a href="http://matthewjamestaylor.com/blog/ultimate-3-column-holy-grail-pixels.htm">Pixel widths</a> | <a href="http://matthewjamestaylor.com/blog/ultimate-3-column-holy-grail-ems.htm">Em widths</a> | <strong>Percentage widths</strong></p>
		</div>