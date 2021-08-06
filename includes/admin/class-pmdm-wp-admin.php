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
		<style type="text/css">
			div.dataTables_wrapper div.dataTables_length select {
				background-image: none !important;
			}
		</style>

		<table id="pmdm-wp-table" class="table is-striped" style="width:100%">
	        <thead>
	            <tr>
	                <th><?php echo __( 'Key', 'pmdm_wp' ); ?></th>
	                <th><?php echo __( 'Value', 'pmdm_wp' ); ?></th>
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

					echo '<td>' . esc_html( var_export( $value, true ) ) . '</td></tr>' . "\n";
				}

	           ?>
	        </tbody>
	      
	    </table>
    <?php

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

	}
}
?>