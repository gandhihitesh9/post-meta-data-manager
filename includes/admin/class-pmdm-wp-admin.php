<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 *
 * Manage Admin Panel Class
 *
 * @package Post Meta Data Manager For WordPress
 * @since 1.0
 */

class Pmdm_Wp_Admin {

	public $scripts;

	//class constructor
	function __construct() {

		global $pmdm_wp_scripts;

		$this->scripts = $pmdm_wp_scripts;
	}

	/**
	*
	* Add Meta Box in Posts, Pages and CPT
	*
	* @package Post Meta Data Manager For WordPress
	* @since 1.0
	*/
	public function pmdm_wp_add_meta_boxes ($post_type, $post) {

		$current_screen = get_current_screen();

		if(isset($current_screen->action) &&  $current_screen->action == 'add') { // check new post 
			return;
		}

		if ( ! isset( $post->ID ) ) {	
			return;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}



		$metabox_id      = 'pmdm-wp';
		$metabox_title   = __( 'Post Metadata Manager', 'pmdm_wp' );
		$metabox_screen  = $post_type;
		$metabox_context = 'normal';
		$metabox_priority    = 'low';

		add_meta_box( $metabox_id, $metabox_title, array( $this, 'pmdm_wp_display_post_metadata' ), $metabox_screen, $metabox_context, $metabox_priority, array() );

	}

	/**
	*
	* Dialay Meta data in Meta box in Posts, Pages and CPT
	*
	* @package Post Meta Data Manager For WordPress
	* @since 1.0
	*/

	public function pmdm_wp_display_post_metadata ($post) {

		if ( empty( $post->ID ) ) {
			return;
		}

		$post_meta = get_post_meta( $post->ID );

		?>		

		<table id="pmdm-wp-table" class="display" style="width:100%">
	        <thead>
	            <tr>
	                <th><?php echo __( 'Key', 'pmdm_wp' ); ?></th>
	                <th><?php echo __( 'Value', 'pmdm_wp' ); ?></th>
	                <th><?php echo __( 'Action', 'pmdm_wp' ); ?></th>
	            </tr>
	        </thead>
	        <tbody>
	           <?php
	           foreach( $post_meta as $meta_key => $value ) {

	           		if ( is_array( $value ) ) {	// Check if Array

						foreach ( $value as $num => $el ) {

							$value[ $num ] = maybe_unserialize( $el );
						}
					}else{
						$value = $value;
					}

		           	$is_added = isset( $post_meta[ $meta_key ] ) ? false : true;

					echo '<tr>';

					echo '<td>' . esc_html( $meta_key ) . '</td>';

					echo '<td>' . esc_html( var_export( $value, true ) ) . '</td>';

					echo '<td><a href="#open-modal" data-id="'.$meta_key.'" id="edit-'.$meta_key.'" class="edit-meta">Edit</a> | <a href="javascript:;" data-id="'.$meta_key.'"  id="delete-'.$meta_key.'" class="delete-meta">Delete</a></td></tr>'."\n";
				}

	           ?>
	        </tbody>
	      
	    </table>

	    <div id="open-modal" class="modal-window">
		  <div>
		    <a href="#" title="Close" class="modal-close">x</a>
		    <h1>Meta Value</h1>
		    <div>Meta Value Here</div>
		    
		    </div>
		</div>
    <?php

	}

	/**
	* Delete Meta Ajax
	*
	* @package Post Meta Data Manager For WordPress
	* @since 1.0
	*/

	public function pmdm_wp_ajax_delete_meta() {
		if(isset($_POST) && !empty($_POST['post_id']) && $_POST['meta_id']) {

			$post_id = $_POST['post_id'];
			$meta_id = $_POST['meta_id'];

			delete_post_meta($post_id, $meta_id);

			wp_send_json_success(
				array('msg' => __('Meta successfully deleted', 'pmdm_wp'))
			);

		} else{
			wp_send_json_error(
				array('msg' => __('There is something worong! Please try again', 'pmdm_wp'))
			);
		}

		die();
	}



	/**
	 * Adding Hooks
	 *
	 * @package Post Meta Data Manager For WordPress
	 * @since 1.0
	 */
	function add_hooks(){

		// add data table
		add_action( 'add_meta_boxes', array( $this, 'pmdm_wp_add_meta_boxes' ), 1000, 2 );

		// Delete Ajax
		add_action("wp_ajax_pmdm_wp_delete_meta", array( $this, "pmdm_wp_ajax_delete_meta" ) ) ;
		add_action( "wp_ajax_nopriv_pmdm_wp_delete_meta", array( $this, "pmdm_wp_ajax_delete_meta") );

	}
}
?>