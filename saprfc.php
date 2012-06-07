<?php
/**
 * Plugin Name: SAPRFC
 * Plugin URI:  Call ABAP functions for display content from SAP
 * Text Domain: saprfc
 * Domain Path: /languages
 * Description: Get Data from SAP R/3
 * Version:     0.0.1
 * Author:      Frank Bültge
 * Author URI:  http://bueltge.de
 * License:     GPLv3
 */

/**
License:
==============================================================================
Copyright 2012 Frank Bültge  (email : frank@bueltge.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Requirements:
==============================================================================
This plugin requires WordPress >= 3.3 and tested with PHP Interpreter >= 5.3
*/

// avoid direct calls to this file, because now WP core and framework has been used.
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

require_once 'inc/saprfc.php';

// Create saprfc-instance
$sap = new saprfc( array(
	'logindata' => array(
		  'ASHOST'  => 'your_system.main.org' // application server
		, 'SYSNR'   => '0815'  // system number
		, 'CLIENT'  => '900' // client
		, 'USER'    => 'Your_User'  // user
		, 'PASSWD'  => 'Your_Password'  // password
	)
	, 'show_errors' => TRUE  // let class printout errors
	, 'debug' => FALSE  // detailed debugging information
) ) ;

require_once 'widgets/class-sap_material_widget.php';
require_once 'widgets/class-sap_userlist_widget.php';
