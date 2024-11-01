=== WP-Pulse-Meter ===
Contributors: freenerd
Tags: activity, graphics, posts, comments, timeline
Requires at least: 2.1
Tested up to: 2.1
Stable tag: trunk

Generates a graphic with the "pulse" of your blog.

== Description ==

The plugin gets active on every site visit looking, if there is already a picture for the current day drawn. If not (there is no properly named picture in the directory) a new one is drawn. This will happen the first time the blog is accessed after midnight. Now, all posts and comments within the pulse scope are fetched. For every day in the scope, the posts and comments will be summed up and shown as a up-down peak on the graph. In the end, the graph image will be saved and the html img-tag will be returned. The graph is always scaled, so that the day with the highest peak uses the maximum height.

You are able to modify the looks of the pulse meter:

echo showPulseMeterImg($alwaysCreateNew = 'false', $pulseScope = 30, $width = 200, $height = 50, $bgcolor = 'FFFFFF', $linecolor = '000000', $paddingx = 5, $paddingy = 5, $fade = 1, $id = 'wppulsemeter', $path = 'wp-imageswp-pulse-meter');

 * $alwaysCreateNew | If 'true', the image will be always redrawn with every call. Helpfull if you want to instantly see changes when testing
 * $pulseScope | The number of days shown on the graph
 * $paddingx  $paddingy | The number of pixel that the graph will always keep distance to the image border
 * $id | css id of the image
 * $path | Path to the directory the image is going to be saved in

== Installation ==

This section describes how to install the plugin and get it working.

1. Download the Plugin here
2. Extract the wp-pulse-meter.php and put it into wp-content/plugins/
3. Create a directory (default: wp-images/wp-pulse-meter/) and make it **writable**
4. Call the function "echo showPulseMeterImg();" from where the image should show up.
5. Activate the Plugin

== Screenshots ==

1. How it looks