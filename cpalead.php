<?php
/*
Plugin Name: CPALead WordPress Plugin
Version: 1.0
Plugin URI: http://www.seanbluestone.com/cpalead-wordpress-plugin
Author: Sean Bluestone
Author URI: http://www.seanbluestone.com
Description: A Simple CPALead Plugin which allows you to drop CPALeads into any post or page.

Copyright 2008  Sean Bluestone  (email : thedux0r@gmail.com)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

register_activation_hook(__FILE__, 'cpalead_install');
add_action('admin_menu', 'cpalead_menu');
global $wpdb;
define("CPALEAD_TABLE",$wpdb->prefix."cpaleads");

function cpalead_install(){
	$getTable=mysql_query("SHOW TABLES LIKE ".CPALEAD_TABLE);

	if(!@mysql_result($getTable,0,0)){
		mysql_query("CREATE TABLE ".CPALEAD_TABLE." (`id` INT NOT NULL AUTO_INCREMENT,`name` VARCHAR( 255 ) NOT NULL ,`content_type` VARCHAR( 255 ) NOT NULL ,`cpa_type` VARCHAR( 255 ) NOT NULL,PRIMARY KEY (`id`))")or die(mysql_error());
		add_option('cpalead_id','8369');
	}
}


function cpalead_menu(){
	add_options_page('CPALeads','CPALeads', 8, __FILE__, 'cpalead_options');
}


function cpalead_options(){

	if($_POST['Submit']=='Create Now'){
		mysql_query("INSERT INTO ".CPALEAD_TABLE." VALUES ('','{$_POST['name']}','{$_POST['content_type']}','{$_POST['cpa_type']}')");
		echo '<div id="message" class="updated fade"><b>Lead Created</b></div>';
	}elseif($_POST['Submit']=='Update'){
		mysql_query("UPDATE ".CPALEAD_TABLE." SET name = '{$_POST['name']}', content_type = '{$_POST['content_type']}', cpa_type = '{$_POST['cpa_type']}' WHERE id = {$_POST['id']}");
		echo '<div id="message" class="updated fade"><b>Lead Updated</b></div>';
	}elseif($_POST['Submit']=='Delete'){
		mysql_query("DELETE FROM ".CPALEAD_TABLE." WHERE id = {$_POST['id']}");
		echo '<div id="message" class="updated fade"><b>Lead Deleted</b></div>';
	}

	echo '<div class="wrap"><h2>CPALead WordPress Plugin</h2>

	<form method="post" action="options.php" name="cpalead_options">';

	wp_nonce_field('update-options');

	echo '<table class="form-table"><tr valign="top">
	<tr valign="top"><td><b>'.__('CPALead ID',$WPLD_Domain).'</b></td>
	<td><input type="text" name="cpalead_id" value="'.get_option('cpalead_id').'"></td>
	<td>Your CPALead ID is unique to you and is displayed as the Affiliate ID #:<b>xxxx</b> in the main page of CPALead.com.</td></tr>
	<tr><td colspan="3" align="right"><input type="hidden" name="action" value="update" /><input type="submit" name="Submit" value="'.__('Save Changes',$WPLD_Domain).'" /></td></tr>
	</table>
	</form>

	<h3>Create New CPALead</h3>
	Enter the name of the Post or Page, select whether it\'s a Post or Page and then select whether you want a close button or not.
	<form method="post" action="" name="new_cpalead">
	<table class="form-table"><tr><th>Post/Page Name</th><th>Content Type</th><th>CPA Type</th></tr>
	<tr>
	<td><input type="text" name="name" value=""></td>
	<td><select name="content_type"><option value="Post">Post</option><option value="Page">Page</option></td>
	<td><select name="cpa_type"><option value="1">With Close Button</option><option value="2">Without Close Button</option></td>
	</tr>
	<tr><td colspan="3" align="right"><input type="hidden" name="action" value="update" /><input type="submit" name="Submit" value="Create Now" /></td></tr>
	</table>
	</form>';

	$getCPALeads=mysql_query("SELECT * FROM ".CPALEAD_TABLE);

	if(mysql_num_rows($getCPALeads)>0){
		echo '<h3>CPALeads</h3>
		<table class="form-table"><tr><th>Post/Page Name</th><th>Content Type</th><th>CPA Type</th></tr>';

		while($CPA=mysql_fetch_assoc($getCPALeads)){
			if($CPA['content_type']=='Post'){ $Post=' SELECTED'; $Page=''; }else{ $Post=''; $Page=' SELECTED'; }
			if($CPA['cpa_type']==1){ $One=' SELECTED'; $Two=''; }else{ $One=''; $Two=' SELECTED'; }
			$x++;

			echo '<tr>
			<td><form method="post" action="" name="cpaleads_'.$x.'"><input type="hidden" name="id" value="'.$CPA['id'].'"><input type="text" name="name" value="'.$CPA['name'].'"></td>
			<td><select name="content_type"><option value="Post"'.$Post.'>Post</option><option value="Page"'.$Page.'>Page</option></td>
			<td><select name="cpa_type"><option value="1"'.$One.'>Without Close Button</option><option value="2"'.$Two.'>With Close Button</option></td>
			<tr><td colspan="3" align="right"><input type="submit" name="Submit" value="Delete" /><input type="submit" name="Submit" value="Update" /></form></td></tr>
			</tr>';
		}
		echo '</table>';
	}
}


function cpalead_output(){
	global $HeadEcho;
	$CPAID=get_option('cpalead_id');

	$getCPALeads=mysql_query("SELECT * FROM ".CPALEAD_TABLE);

	while($CPA=mysql_fetch_assoc($getCPALeads)){
		if($CPA['content_type']=='Post'){
			$Posts[$CPA['name']]=$CPA['cpa_type'];
		}elseif($CPA['content_type']=='Page'){
			$Pages[$CPA['name']]=$CPA['cpa_type'];
		}
		$x++;
	}

	if(count($Posts)>0){
		foreach($Posts as $Name => $Type){
			if(is_single($Name)){
				echo '<script src="http://www.cpalead.com/gateway'.( $Type==2 ? '_v2' : '' ).'.php?pub='.$CPAID.'" language="JavaScript" type="text/javascript"></script>';
			}
		}
	}

	if(count($Pages)>0){
		foreach($Pages as $Name => $Type){
			if(is_page($Name)){
				echo '<script src="http://www.cpalead.com/gateway'.( $Type==2 ? '_v2' : '' ).'.php?pub='.$CPAID.'" language="JavaScript" type="text/javascript"></script>';
			}
		}
	}
}

?>