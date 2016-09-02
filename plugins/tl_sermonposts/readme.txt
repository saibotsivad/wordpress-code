This plugin is basically a replacement for the "Sermon Browser" plugin, making it
to use the extra functionality of Wordpress 3.0 (really, about 2.8 or so).

It's not really a re-write, since it doesn't really use the code from that plugin. It is
a fresh go at it, but hopefully without the bugs that a fresh attempt would have :-/

The data associated with the sermon (except the passages) is stored using existing Wordpress
methods, so no additional search methods are needed.

The sermon passages are stored in a separate database table, I couldn't find a way to fit them
into the existing database structure. Basically, each passage is treated as a range of verses,
so a row has three columns: | post_id | verse_start | verse_end |

Because of this range method of data storage, you can use comparisons in your SQL query when looking
for sermons that preach on a particular verse, for example. This search is much more efficient
and scalable than storing the verses of a sermon in an array, as the Sermon Browser plugin did.