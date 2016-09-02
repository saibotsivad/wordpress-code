This template was made to make it easier to start designing a fresh Wordpress theme.

This template is NOT meant to be used as a parent/child theme!

Other pages that you might make:
	single.php							Used on individual posts, aka, http://example.com/2010/03/01/post-name
	page.php							Used on individual pages, aka, http://example.com/about
	archive.php							Used on archive pages, aka, http://example.com/2010/03
	date.php							Same as archive.php I think?
	category.php						Used on the category page, aka, http://example.com/category/videos
	tag.php								Used on tag listing pages, aka, http://example.com/tags/actor
	search.php							Used when displaying search results
	attachment.php						Not really sure, but maybe there's a page for listing attachments?

Obviously, you can (an should) use conditional tags, but you could also name template files things like this:
	single-{post type}.php				If you make custom posts, aka, single-videos.php
	page-{slug}.php						The name slug of a post/page, aka, page-recent-news.php
	page-{id}.php						The number ID of the page, aka, page-6.php
	category-{slug}.php					The name slug of the category, aka, category-videos.php
	category-{id}.php					The number ID of the category, aka, category-3.php
	tag-{slug}.php						The name slug of the tag, aka, tag-actor.php
	tag-{id}.php						The number id of the tag, aka, tag-5.php
	taxonomy-{taxonomy}-{term}.php		The taxonomy name slug and the term name slug in that taxonomy, aka, taxonomy-actor-billy.php
	taxonomy-{taxonomy}.php				The taxonomy name slug of that taxonomy, aka, taxonomy-actor.php
	author-{name}.php					The slug user id of the author, aka, author-admin.php
	author-{id}.php						The user number, aka, author-11.php