=== Plugin Name ===
Contributors: cabraham
Donate link: http://cjyabraham.com
Tags: geo, mapping, comments
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 0.2

Turns posts and pages into geo-based issue maps.  Comments are geo-located on a map.

== Description ==

The Comment Geo Maps WordPress plugin allows you to turn a post or a page into a geo-based issue map.  This adds value to discussions about issues that are geographically located.  All you need to do is create a prompt in the form of a blog post and then people post their comments.  Each comment is geo-coded and becomes a point on the map.  In this way, a geo-based discussion ensues and the map gets populated with data around a certain topic.

Maps can be started as a post, to make it more time sensitive like in mapping participation at a city-wide event, or as a page, to make it more permanent like for mapping dangerous NYC intersections.  The page format fills the whole page and the post format puts a map at the top of the regular list of comments.

You'll need to be relatively comfortable with php code to integrate this plugin into your WordPress theme.


= Version History =
* **0.1**
 * initial release
* **0.2**
 * added custom pan-zoom controls
 * limited the zoom bar to local range
 * swapped in Google Maps terrain mode which are much faster than cloudmade tiles
 * fixed a bunch of render bugs across browsers
 * swapped in latest release of openlayers
 * a bunch of minor layout and usability improvements
 * replaced the use of simplexml_load_file for geocoding for the more secure curl

== Installation ==

To install the plugin:

1. Unzip the plugin into the /wp-content/plugins directory on your webserver.
1. Activate the plugin.
1. Fill in the "Comment Geo Maps" settings page.  Get a Google Maps API key here: http://code.google.com/apis/maps/signup.html
1. Copy cgm-comment.php to your theme directory.
1. Edit your theme to use cgm-comments.php.  For an example of how to do this, see http://oss.openplans.org/commentgeomaps/wiki/CGMCommentsDiff
1. Edit cgm-comments.php as necessary to make it work with your theme.

== Frequently Asked Questions ==

Please see the comments at [cjyabraham.com](http://cjyabraham.com/projects/comment-geo-maps/) for questions and discussion.


== Screenshots ==

1. This is the full page view, as used on [Streetsblog.org](http://streetsblog.org).
2. This is the regular post view.
