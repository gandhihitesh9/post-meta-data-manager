<?php
/*
 * Plugin Name: Post Meta Data Manager
 * Plugin URI: http://www.wpexpertplugins.com/
 * Description: Post Meta management Posts, Pages, Custom Post Types, Users, Taxonomoies.
 * Version: 1.1.3
 * Author: WpExpertPlugins
 * Text Domain: pmdm_wp
 * Author URI: http://www.wpexpertplugins.com/contact-us/
 * Requires at least: 6.0.1
 * WC tested up to: 5.8
 * Domain Path: /languages
*/

/**
 * Basic plugin definitions 
 * 
 * @package Post Meta Data Manager
 * @since 1.0
 */
if( !defined( 'PMDM_WP_DIR' ) ) {
  define( 'PMDM_WP_DIR', dirname( __FILE__ ) );      // Plugin dir
}
if( !defined( 'PMDM_WP_VERSION' ) ) {
  define( 'PMDM_WP_VERSION', '1.1.3' );      // Plugin Version
}
if( !defined( 'PMDM_WP_URL' ) ) {
  define( 'PMDM_WP_URL', plugin_dir_url( __FILE__ ) );   // Plugin url
}
if( !defined( 'PMDM_WP_INC_DIR' ) ) {
  define( 'PMDM_WP_INC_DIR', PMDM_WP_DIR.'/includes' );   // Plugin include dir
}
if( !defined( 'PMDM_WP_INC_URL' ) ) {
  define( 'PMDM_WP_INC_URL', PMDM_WP_URL.'includes' );    // Plugin include url
}
if( !defined( 'PMDM_WP_ADMIN_DIR' ) ) {
  define( 'PMDM_WP_ADMIN_DIR', PMDM_WP_INC_DIR.'/admin' );  // Plugin admin dir
}
if(!defined('PMDM_WP_BASE_NAME')) {
  define('PMDM_WP_BASE_NAME', 'pmdm_wp'); // Plugin folder name
}
if(!defined('PMDM_WP_PREFIX')) {
  define('PMDM_WP_PREFIX', 'pmdm_wp'); // Plugin Prefix
}
if(!defined('PMDM_WP_VAR_PREFIX')) {
  define('PMDM_WP_VAR_PREFIX', '_pmdm_wp_'); // Variable Prefix
}

/**
 * Load Text Domain
 *
 * This gets the plugin ready for translation.
 *
 * @package Post Meta Data Manager
 * @since 1.0
 */
add_action( 'plugins_loaded',  'pmdm_wp_init_textdomain' );

function pmdm_wp_init_textdomain () {

	// Filter for Language directory
	$pmdm_wp_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$pmdm_wp_lang_dir = apply_filters('pmdm_wp_languages_directory', $pmdm_wp_lang_dir);

	//Wordpress Locale file
	$locale = apply_filters('plugin_locale', get_locale(), 'pmdm_wp');
	$mofile = sprintf('%1$s-%2$s.mo', 'pmdm_wp', $locale);

	//Setup path to current locale
	$mofile_locale = $pmdm_wp_lang_dir.$mofile;
	$mofile_global = WP_LANG_DIR. "/".PMDM_WP_BASE_NAME. "/" .$mofile;	

	if(file_exists($mofile_global)) { // look in global Languages folder
		load_textdomain( 'pmdm_wp', $mofile_global );
	}elseif(file_exists($mofile_locale)) { // look in local plugin Languages folder
		load_textdomain( 'pmdm_wp', $mofile_locale );
	} else { // Load the default Languages file
		load_plugin_textdomain( 'pmdm_wp', false, $pmdm_wp_lang_dir );
	}
}

/**
 * Activation Hook
 *
 * Register plugin activation hook.
 *
 * @package Post Meta Data Manager
 * @since 1.0
 */
register_activation_hook( __FILE__, 'pmdm_wp_install' );

function pmdm_wp_install(){
	
}

/**
 * Deactivation Hook
 *
 * Register plugin deactivation hook.
 *
 * @package Post Meta Data Manager
 * @since 1.0
 */
register_deactivation_hook( __FILE__, 'pmdm_wp_uninstall');

function pmdm_wp_uninstall(){
  
}

// Global variables
global $pmdm_wp_scripts, $pmdm_wp_admin;

// Script class handles most of script functionalities of plugin
include_once( PMDM_WP_INC_DIR.'/class-pmdm-wp-scripts.php' );
$pmdm_wp_scripts = new Pmdm_Wp_Scripts();
$pmdm_wp_scripts->add_hooks();

// Admin class handles most of admin panel functionalities of plugin
include_once( PMDM_WP_ADMIN_DIR.'/class-pmdm-wp-admin.php' );
$pmdm_wp_admin = new Pmdm_Wp_Admin();
$pmdm_wp_admin->add_hooks();
?>