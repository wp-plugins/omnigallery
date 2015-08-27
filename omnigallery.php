<?php
/*
Plugin Name: OmniGallery
Plugin URI: http://colorlabsproject.com/
Description: OmniGallery helps you to create photo gallery from your social network.
Version: 1.0.8
Author: Colorlabs & Company
Author URI: http://colorlabsproject.com/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

require_once('includes/omnigallery.class.php');
global $omnigallery, $scinstagram, $scfacebook, $scflickr;
$omnigallery = new OmniGallery(__FILE__);
$omnigallery->version = omnigallery_plugin_get_version();

if (class_exists('OmniGallery')):
    if (isset($omnigallery)){
		register_activation_hook(__FILE__, array(&$omnigallery, 'activation'));
    }
endif;

// Includes Classes
require_once('includes/scinstagram.class.php');
require_once('includes/scflickr.class.php');
require_once('includes/scpicasa.class.php');
require_once('includes/scpinterest.class.php');
require_once('includes/scdribble.class.php');

if (class_exists('SCInstagram')):
    $scinstagram = SCInstagram::getInstance();

	if (isset($scinstagram)){
		register_activation_hook(__FILE__, array(&$scinstagram, 'install'));
	}
endif;

/**
 * Returns current plugin version.
 * 
 * @return string Plugin version
 */

function omnigallery_plugin_get_version() {

    if ( is_admin() ) {

      if ( ! function_exists( 'get_plugin_data' ) )
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

      $plugin_data = get_plugin_data( __FILE__, false, false );

      $plugin_version = $plugin_data['Version'];

      return $plugin_version;

    }

}

// Add Omnivideo Documentation
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'omnigallery_action_links' );
function omnigallery_action_links( $links ) {

	$plugin_links = array(
		'<a href="http://colorlabsproject.com/documentation/omnigallery/" target="_blank">' . __( 'Documentation' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}