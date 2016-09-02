This plugin creates new post types, accesible by any user with the "author" role and with custom
RSS/Atom feeds, to manage the following items:

* Writings
* Audio
* Video

Those items are available to the site viewer in the following possible ways:

* Affiliate links to Amazon
* Other external links
* On-site downloads
* Purchased directly and mailed/delivered

Donations can be made, either to the institute directly or to various managed projects. These
projects are each assigned their own page, which provides detailed information about the project,
and projects can be placed in specific places using a custom function. A donations widget is available.

Advertisements for notable institutions can be managed, they can have images, links, and additional
descriptive text can be added for the administrators use.


Data associated with the three post types is listed here:
Documents:
	Author (multiple, by tagging)
	Publisher (individual, by dropdown or similar)
	Edition (digit, e.g., 1 for first edition)
	Volume Number (digit)
	Binding (Hardback/Soft cover/Pamphlet print/Ring binding/loose leaf/etc. Drop down list.)
	Page Length (digit)
	Complete File Upload (if it's a downloadable pdf)
	Partial File Upload (if it's a sample chapter or similar. Also needs a label associated with it "First Chapter")
Audio:
	Category (Dropdown list: Music, Audio book, Sermon, Lecture, etc.)
	Speaker (multiple, by tagging)
	Series (dropdown list, something like "Basement Tapes" series)
	Volume Number (digit)
	Album Artist (multiple, by tagging)
	Album Conductor (multiple, by tagging)
	Publishing Label (individual, by dropdown or similar)
	Number of discs/tapes (digit)
	Recording length (in minutes)
	Medium (MP3 download/Cassette/CD. Dropdown list)
	Complete File Upload (If it's MP3s. Multiple files. Each need a label.)
	Partial File Upload (A sample track, for example. Multiple files. Each needs a label.)
Video:
	Category (Dropdown list: Movie, Sermon, Lecture, etc.)
	Speakers (multiple, by tagging)
	Directors (multiple, by tagging)
	Producers (multiple, by tagging)
	Actors (multiple, by tagging)
	Series (dropdown list, something like "Evolution vs Creation" series)
	Volume (digit)
	Number of discs/tapes (digit)
	Subtitles (multiple, by tagging)
	Publisher (individual)
	Recording length (in minutes)
	Format (Tags: Animated, color, b&w, real life, etc.)
	Medium (list: Blu-Ray, DVD, VHS, online (youtube/vimeo), downloadable)
	I'm not going to support file uploads of videos at this time. If it ever gets asked for, maybe.
	Embedded URL (the html that Youtube/Vimeo give for embedding)

These things are common to all three post types, although may not be included:
	Title (Text.)
	Description (Text. Supports HTML markup and inserting of pictures.)
	Thumbnail (Picture of product.)
	Language (Multiple. Selectable list.)
	Normal Category (Multiple tags. Ex: Family living, Sanctification, etc.)
	Publication/Release Date (Dropdown list of DD/MM/YY with none selected. May be in DD/MM/YY, or just year.)
	ASIN (Alphanumeric code. Unique.)
	ISBN-10 (Digit. Unique.)
	ISBN-13 (Digit. Unique.)
	Condition (Dropdown List: New/Barely Used/Moderately Used/Badly Worn.)
	Quantity Available (Digit: If someone actually buys it, it should deduct one.)
	Shipping Weight (Decimal: In ounces. Used to calculate shipping cost.)
	Customer Rating (This would be handy to have later on.)
	
These things I have as taxonomies:
	Author
	Publisher
	Speaker
	Album Artist
	Album Conductor
	Director
	Producer
	Actors