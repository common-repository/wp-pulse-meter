<?php

/*
Plugin Name: WP-Pulse-Meter 
Plugin URI: http://www.freenerd.de/?s=wp-pulse-meter
Description: Generates a graphic with the "pulse" of your blog (please make sure the directory <code>wp-images/wp-pulse-meter/</code> is writable by the web server). Plugin based on WP-Pulse by Skybly http://www.the-witch.net
Author: Freenerd
Author URI: http://www.freenerd.de
Version: 1.0
*/

function showPulseMeterImg($alwaysCreateNew = false, $pulseScope = 30,
 $width = 200, $height = 50, $bgcolor = 'FFFFFF', $linecolor = '000000', $paddingx = 5, 
 $paddingy = 5, $fade = 1, $id = 'wppulsemeter', $path = 'wp-images/wp-pulse-meter/') 
{
	global $wpdb, $tableposts, $tablecomments;
	
	define("SECONDSOFADAY","86400");
	
	// Check if directory exists and is writable
	if(!is_writable($path)) die("Error: " . $path . " is not writable. Please make it writable, so wp-pulse-meter can save images. If you have changed the path, please make sure to end the path with a slash.");
	
	$today = date("d");
	$todays_filename = $path . 'wp-pulse-meter-' . $today . '.png';

	if($alwaysCreateNew == true) @unlink(ABSPATH . $todays_filename);

	if(!file_exists(ABSPATH . $todays_filename))
	{
		// so file is not there, most likely because today is a new day.
		// therefor we have to create a pulse meter for today.
		
		garbageCollectPulseMeter($path);

		// Generate the canvas
		$image = imagecreate($width, $height)
			or die ('Cannot create a new GD image. Please install a newer PHP > 4.3 or GD manually from www.libgd.org');

		// Convert colors to RGB and allocate them
		sscanf($bgcolor, "%2x%2x%2x", $red, $green, $blue);
		$bgcolor=ImageColorAllocate($image,$red,$green,$blue);

		sscanf($linecolor, "%2x%2x%2x", $red, $green, $blue);
		$linecolor=ImageColorAllocate($image,$red,$green,$blue);

		// Fill the canvas with the background color
		ImageFill($image, 0, 0, $bgcolor);
		
		// calculate time borders
		$now = time();
		$now = mktime(0, 0, 0, date("m",$now), date("d", $now), date("y", $now)); // midnight
		$scope_border = $now - (SECONDSOFADAY * $pulseScope); // midnight at when pulse scope begins
		
		// get the latest posts been posted within the pulse scope		
		$latest_posts = $wpdb->get_results("SELECT id, post_date 		
				FROM $wpdb->posts
				WHERE post_date
				BETWEEN '".date('y-m-d', $scope_border)."'  AND '".date('y-m-d', $now)."' 
				AND post_status = 'publish'
				ORDER BY post_date ASC");

		// get the latest comments been written within the pulse scope		
		$latest_comments = $wpdb->get_results("SELECT comment_ID, comment_date 		
				FROM $wpdb->comments
				WHERE comment_date
				BETWEEN '".date('y-m-d', $scope_border)."'  AND '".date('y-m-d', $now)."' 
				AND comment_approved = '1'
				ORDER BY comment_date ASC");

		// we have to get the maximum number of posts+comments for every day within scope
		// meanwhile we create a new array to be stepped through again when creating the image
		$latest_entries_comments_counted = array();
		$max = 1;
		$posts_index = 0;
		$comments_index = 0;
		for($day = 1; $day <= $pulseScope;$day++)
		{
			// $day is a single day
			$current_scope_border = $scope_border + (SECONDSOFADAY * $day);

			$day_height = 0;
			
			// if there are posts on this day
			if(	!empty($latest_posts) && 
				array_key_exists($posts_index, $latest_posts) &&
				(strtotime($latest_posts[$posts_index]->post_date) <= $current_scope_border))
				{
				
				while(	array_key_exists($posts_index, $latest_posts) && 
						strtotime($latest_posts[$posts_index]->post_date) <= $current_scope_border)
					{
					$day_height++;
					$posts_index++;
				}
			}

			// if there are comments on this day			
			if( !empty($latest_comments) && 
				array_key_exists($comments_index, $latest_comments) &&
				(strtotime($latest_comments[$comments_index]->comment_date) <= $current_scope_border))
				{
					
				while(	array_key_exists($comments_index, $latest_comments) &&
						strtotime($latest_comments[$comments_index]->comment_date) <= $current_scope_border)
					{					
					$day_height++;
					$comments_index++;
				}
			}
			
			// so there are $day_height posts+comments on day $day
			$latest_entries_comments_counted[$day] = $day_height;
			
			if($day_height > $max) $max = $day_height;
		}

		// Calculate some numbers used for drawing    
		$baseline = ($height / 2) - 1;
		$pulsestep = ($width - (2 * $paddingx) - (2 * $fade)) / $pulseScope / 4;
		$stepheight = ($height - (2 * $paddingy)) / 2 / $max;

		// Draw the "fade in" line    
		$pos = $paddingx; $nextpos = $pos + $fade;
		imageline($image, $pos, $baseline, $nextpos, $baseline, $linecolor);
		
		// Draw the Pulse
		$day_height = 0;
		for($day = 1; $day <= $pulseScope;$day++)
		{
			$day_height = $latest_entries_comments_counted[$day];
			
			// Draw the "up" line
			$pos = $nextpos; $nextpos = $pos + $pulsestep;
			imageline($image, $pos, $baseline, $nextpos, $baseline + ($stepheight * $day_height), $linecolor);

			// Draw the "down" line
			$pos = $nextpos; $nextpos = $pos + ($pulsestep * 2);
			imageline($image, $pos, $baseline + ($stepheight * $day_height), $nextpos, $baseline - ($stepheight * $day_height), $linecolor);

			// Draw up to baseline again
			$pos = $nextpos; $nextpos = $pos + $pulsestep;
			imageline($image, $pos,  $baseline - ($stepheight * $day_height), $nextpos, $baseline, $linecolor);
		}
		
		// Draw the "fade out" line
		$pos = $nextpos; $nextpos = $pos + $fade;
		imageline($image, $pos, $baseline, $nextpos, $baseline, $linecolor);

		// Save image in PNG format
		imagepng($image,$todays_filename);
	}
	
	$blogurl = get_bloginfo('url');
	return("<img id='$id' src='$blogurl/$todays_filename'>");	
}

// this function deletes all the possible images just to clean up
function garbageCollectPulseMeter($path)
{
	for($i=1; $i<=31; $i++){
		// leading zero for single digits
		$i = sprintf( "%02d", $i );
		// delete all possible previous pulse meter pictures
		@unlink($path . 'wp-pulse-meter-' . $i . '.png');
	}
}