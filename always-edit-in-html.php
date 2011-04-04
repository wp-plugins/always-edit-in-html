<?php

/*
Plugin Name: Always Edit in HTML
Plugin URI: http://www.gravitationalfx.com/always-edit-in-html-wordpress-plugin
Description: Opens page and post editor in HTML mode to preserve formatting.
Version: 1.0
Author: Gravitational FX
Author URI: http://www.gravitationalfx.com/always-edit-in-html-wordpress-plugin/

    Copyright © 2011 Gravitational FX.  www.gravitationalfx.com

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


// Add acctions for adding to post/page and saving the option
add_action('admin_init','always_edit_in_html_create_options_box');
add_action('admin_head','always_edit_in_html_handler');
add_action('save_post','always_edit_in_html_save_postdata');


// Turn off the rich editing capability
// Effectively removed the "Visual" and "HTML" tabs leaving a plain editor.
function always_edit_in_html_handler(){
	global $post;
	$editInHTML = getHTMLEditStatus($post->ID);
	if ($editInHTML){
    	add_filter('user_can_richedit',create_function(null,'return false;'),50);
	}
}

// Adds the option box to Pages and Posts in the RHS column
function always_edit_in_html_create_options_box(){
    add_meta_box('always-edit-in-html',__( 'Always edit in HTML','myplugin_textdomain' ), 
					'always_edit_in_html_custom_box', 'page' , 'side');
    add_meta_box('always-edit-in-html',__( 'Always edit in HTML','myplugin_textdomain' ),
					'always_edit_in_html_custom_box','post','side');
}



// Creates the Edit in HTML options box
function always_edit_in_html_custom_box($post){
	
	// Check that data is from this post
	wp_nonce_field(plugin_basename(__FILE__),'always_edit_in_html_noncename');
	
	// Get the current status for this post
	$editInHTML=getHTMLEditStatus($post->ID);
	
	// Create the form  with the options field and brief explaination of what it does.
	echo '<p>Removes the Visual and HTML editor tabs and opens this page/post in HTML mode.</p>';
	echo '<label for="always_edit_in_html">'.__("Always edit in HTML?",'myplugin_textdomain').'</label> ';
	echo '<input type="checkbox" id="always_edit_in_html" name="always_edit_in_html" value="on" ';
	
	// If the option is currently being used then check the options box
	if ($editInHTML){
		echo 'checked="checked"';
	}
	echo ' />';
}


// Grabs the Always Edit in HTML option field and checks to see if it's set
// Return true for being used (on) and false for unset (off)
function getHTMLEditStatus($id){
	$editInHTML=get_post_meta($id,"editInHTML",true);

	if($editInHTML=="on"){
		return true;
	}
	else{
		return false;
	}

}

// Save the Always Edit in HTML options along with the post update
function always_edit_in_html_save_postdata($post_id){
	//assume data hasn't been saved
	$updateStaus=false;
	
	// Quick check to make sure data belongs to this post
	if(!wp_verify_nonce($_POST['always_edit_in_html_noncename'],plugin_basename(__FILE__))){
		return $post_id;
	}
	
	// Don't do anything for an autosave
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){	
		return $post_id;
	}
	
	// Make aure we have the permissions to update a post or page
	if('page'==$_POST['post_type']){	
		if(!current_user_can('edit_page',$post_id)){
		  return $post_id;
		}	
	}
	else{
		if(!current_user_can('edit_post',$post_id)){	
			return $post_id;
		}
	}

	// Checks all done so save the option
	if(isset($_POST['always_edit_in_html'])){
		update_post_meta($post_id,'editInHTML','on');
		$updateStatus=tue;
	}
	else{
		update_post_meta($post_id,'editInHTML','off');
		$updateStatus=true;
	}
	
	// returns update status to allow for future checcks
	return $updateStatus;	
}

?>