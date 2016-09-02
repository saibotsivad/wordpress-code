# [UNMAINTAINED] WordPress Code

Between 2010 and 2012 I did a lot of WordPress development,
primarily for my own enjoyment but also for a few clients
and for some friends, mostly *pro bono*.

In the process I learned a lot about WordPress and dove in
pretty deep. I even dove into the WordPress internals enough
that I [contributed](https://core.trac.wordpress.org/ticket/19541)
to WordPress itself!

At some point I moved away from a self-hosted SVN server to
Github, and these WordPress snippets were lost to the digital
sands of time.

For years I've been in the Java development world, so my WordPress
skills have become a bit rusty, but recently I've been given a
financial incentive to refresh my memory.

Therefore, I'm reviving this code now, **unwashed and unclean**,
primarily so that I can reference it easier when I need to.

# Status

All this code is unmaintained, but as I refresh my memory and
look at it, if I decide that something is worth pulling out
into its own repository I'll do that and add a linked list here.

As of now, however, that list is empty.

# Memorable Things

While a lot of this code is just half-baked ramblings, there
are a few in here that I'm actually still proud of. (Note that
this doesn't mean they will work or follow best-practice for
WordPress as it currently is.)

* [add_thickbox_popup](./plugins/add_thickbox_popup): A tutorial-like plugin
	demonstrating how to make the popup modal, which used thickbox at the time.
* [tl_amazon_url](./plugins/tl_amazon_url): A widget that you existed on
	the Post page, that lets you put in an Amazon URL and it'll give you
	the ASIN number.
* [tl_cmsplugin](./plugins/tl_cmsplugin): I don't think I ever finished
	this one, but I thought it was clever. Create and manage content
	inside a familiar WordPress admin, but it's actually displayed
	a different framework.
* [tl_mobilebrowser](./plugins/tl_mobilebrowser): I don't know that this
	one was ever finished either, but the idea was pretty interesting.
	It let you specify a different template for mobile browsers.
* [tl_sermonposts](./plugins/tl_sermonposts): This is one I probably worked
	the most on. It got moved to
	[it's own repository](https://github.com/saibotsivad/sermon_posts),
	but I'm leaving this code here because I haven't checked that it
	all got transferred to the other repo.
* [tl_templateswitcher](./plugins/tl_templateswitcher): This one let
	you switch which templates were used for a given page and all
	it's children pages.

# License

I formally release all content within this repository under
the [Very Open License](http://veryopenlicense.com/):

```
Very Open License (VOL)

The contributor(s) to this creative work voluntarily grant permission
to any individual(s) or entities of any kind
- to use the creative work in any manner,
- to modify the creative work without restriction,
- to sell the creative work or derivatives thereof for profit, and
- to release modifications of the creative work in part or whole under any license
with no requirement for compensation or recognition of any kind.
```

Go crazy with it.

Make something awesome, and make lots of money in the process!

***From me to you with ♥***
