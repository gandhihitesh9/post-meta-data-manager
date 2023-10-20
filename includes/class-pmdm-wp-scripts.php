<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Scripts Class
 *
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 *
 * @package Post Meta Data Manager
 * @since 1.0
 */

class Pmdm_Wp_Scripts {

	//class constructor
	function __construct()
	{
		
	}
	
	/**
	 * Enqueue Scripts on Admin Side
	 * 
	 * @package Post Meta Data Manager
	 * @since 1.0
	 */
	public function pmdm_wp_admin_scripts( $hook ){
		
		if($hook != "post.php" &&  $hook != "profile.php" && $hook != "user-edit.php" && $hook != "term.php" && $hook != "toplevel_page_pmdm-general-settings"){
			return;
		}
		global $post;
		
		if($post != NULL){
			if ( !isset($post->post_type) || (isset($post->post_status) && $post->post_status == "auto-draft") ) {
				return;
			}
		}
		
		
		// Styles
		wp_register_style('pmdm-wp-datatable-styles',  PMDM_WP_INC_URL . '/css/datatables.min.css', array(), '', false);
   		wp_enqueue_style('pmdm-wp-datatable-styles'); 

   		wp_register_style('pmdm-wp-style',  PMDM_WP_INC_URL . '/css/pmdm-wp.css', array(), '', false);
   		wp_enqueue_style('pmdm-wp-style'); 


   		// Js   		
   		wp_register_script('pmdm-wp-datatable-script', PMDM_WP_INC_URL . '/js/datatables.min.js', array(), '', true);
   		wp_enqueue_script('pmdm-wp-datatable-script');   		

   		wp_register_script('pmdm-wp-script', PMDM_WP_INC_URL . '/js/pmdm-wp.js', array(), time(), true);
   		wp_enqueue_script('pmdm-wp-script');

        $pmdm_wp_ajax_arr = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('ajax-security') 
        );

   		wp_localize_script('pmdm-wp-script', 'pmdm_wp_ajax', $pmdm_wp_ajax_arr);
	
	}
	
	/**
	 * Adding Hooks
	 *
	 * Adding hooks for the styles and scripts.
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0
	 */
	function add_hooks(){
		
		//add admin scripts
		add_action('admin_enqueue_scripts', array($this, 'pmdm_wp_admin_scripts'));

	}
}
?>