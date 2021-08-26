<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 *
 * Manage Admin Panel Class
 *
 * @package Post Meta Data Manager
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
	* @package Post Meta Data Manager
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
		$metabox_title   = esc_html__( 'Post Metadata Manager', 'pmdm_wp' );
		$metabox_screen  = $post_type;
		$metabox_context = 'normal';
		$metabox_priority    = 'low';

		add_meta_box( $metabox_id, $metabox_title, array( $this, 'pmdm_wp_display_post_metadata' ), $metabox_screen, $metabox_context, $metabox_priority, array() );

	}

	/**
	*
	* Dialay Meta data in Meta box in Posts, Pages and CPT
	*
	* @package Post Meta Data Manager
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
	                <th><?php echo esc_html__( 'Key', 'pmdm_wp' ); ?></th>
	                <th><?php echo esc_html__( 'Value', 'pmdm_wp' ); ?></th>
	                <th><?php echo esc_html__( 'Action', 'pmdm_wp' ); ?></th>
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

					?>
						<tr>
							<td><?php echo esc_html( $meta_key ); ?></td>
							<td><?php echo esc_html( var_export( $value, true ) ); ?></td>
							<td>
								<a href="javascript:;" data-id="<?php echo $meta_key; ?>" id="edit-<?php echo $meta_key; ?>" class="edit-meta"><?php echo esc_html__( 'Edit', 'pmdm_wp' ); ?></a> 

								<div id="javascript:;" class="modal-window">
									<div>
										<a href="javascript:;" title="Close" class="modal-close">x</a>
										<h1><strong><?php echo esc_html__( 'Currently you are editing', 'pmdm_wp' ); ?></strong>: <?php echo $meta_key; ?></h1>
										<div class="model-body">
											<form method="post" action="">
											<?php wp_nonce_field( 'change_post_meta_action', 'change_post_meta_field' ); ?>

											<?php 
												$get_meta_field_values = get_post_meta($post->ID, $meta_key, true);
												if(is_array($get_meta_field_values)){

													$this->pmdm_wp_get_recursively_inputs($meta_key, $get_meta_field_values);
													
												}else{

													$value_len = strlen($get_meta_field_values);
													?>
														<div class="input_wrapper">
															<p class="display_label_key">Key: <strong><?php echo esc_html($meta_key); ?></strong></p>

															<?php if($value_len > 20) { ?>
																<textarea name="<?php echo esc_html($meta_key); ?>" rows="10" cols="60"><?php echo htmlspecialchars($get_meta_field_values, ENT_QUOTES); ?></textarea>
															<?php } else { ?>
															<input type="text" name="<?php echo esc_html($meta_key); ?>" class="input_box" value="<?php echo htmlspecialchars($get_meta_field_values, ENT_QUOTES); ?>" />
														<?php } ?>

														</div>
													<?php
												}
											?>
												<input type="hidden" value="<?php echo esc_html($post->ID); ?>" name="current_post_id" />

												<input type="submit" value="<?php echo esc_html__( 'Change', 'pmdm_wp' ); ?>" class="change_btn" />

											</form>
										</div>
									</div>
								</div>

								| 
								<a href="javascript:;" data-id="<?php echo esc_html($meta_key); ?>"  id="delete-<?php echo ($meta_key); ?>" class="delete-meta"><?php echo esc_html__( 'Delete', 'pmdm_wp' ); ?></a>
							</td>
						</tr>
			
	    
    	<?php

		}


		?>

				</tbody>
			</table>
		<?php

	}

	/**
	* Delete Meta Ajax
	*
	* @package Post Meta Data Manager
	* @since 1.0
	*/

	public function pmdm_wp_ajax_delete_meta() {
		if(isset($_POST) && !empty($_POST['post_id']) && $_POST['meta_id']) {

			$post_id = intval($_POST['post_id']);
			$meta_id = intval($_POST['meta_id']);

			delete_post_meta($post_id, $meta_id);

			wp_send_json_success(
				array('msg' => esc_html__('Meta successfully deleted', 'pmdm_wp'))
			);

		} else{
			wp_send_json_error(
				array('msg' => esc_html__('There is something worong! Please try again', 'pmdm_wp'))
			);
		}

		die();
	}

	/**
	* print recursive input box using this function
	*
	* @package Post Meta Data Manager
	* @since 1.0
	*/
	public function pmdm_wp_get_recursively_inputs($meta_main_key, $get_meta_field_values, $level_key = array()){
		
		if(is_array($get_meta_field_values)){
			
			
			foreach($get_meta_field_values as $gmfvk => $gmfvv){
				if(is_array($gmfvv)){
					$store_keys = array_merge($level_key,array($gmfvk));
					$this->pmdm_wp_get_recursively_inputs($meta_main_key, $gmfvv, $store_keys);
				}else{
					$input_name = $meta_main_key;

					$display_label = $meta_main_key;

					if(!empty($level_key)){
						foreach($level_key as $skk){
							$input_name .= "[".$skk."]";
							$display_label .= "=>".$skk;
						}
					}
					$input_name .= "[".$gmfvk."]";
					$display_label .= "=>".$gmfvk;
					?>
						<div class="input_wrapper">
							<p class="display_label_key">Key: <strong><?php echo esc_html($display_label); ?></strong></p>
							<input type="text" name="<?php echo $input_name; ?>" class="input_box" value="<?php echo htmlentities($gmfvv, ENT_QUOTES); ?>" />
						</div>
					<?php
					
					
				}
				
			}
		}else{
			?>
				<input type="text" name="<?php echo $meta_main_key; ?>" value="<?php echo htmlentities($get_meta_field_values, ENT_QUOTES); ?>" /> <br/>
			<?php
		}
		
	}


	/**
	* save post meta data using approprite key
	*
	* @package Post Meta Data Manager
	* @since 1.0
	*/
	public function pmdm_wp_change_post_meta(){

		if (isset( $_POST['change_post_meta_field'] ) && wp_verify_nonce( $_POST['change_post_meta_field'], 'change_post_meta_action' ) ) {
			
			if(!empty($_POST)){
				
				foreach($_POST as $pk => $pv){
					if($pk == "change_post_meta_field" || $pk == "_wp_http_referer" || $pk == "current_post_id"){
						continue;
					}
					if(is_array($pv)){
						$pv = $this->pmdm_wp_escape_slashes_deep($pv);
					}else{
						$pv = wp_kses_post($pv);
					}
					
					update_post_meta(intval($_POST["current_post_id"]), $pk, $pv);

				}
			}
		} 

	}
	/**
	* Strip Slashes From Array
	*
	* @package Post Meta Data Manager
	* @since 1.0
	*/
	public function pmdm_wp_escape_slashes_deep($data = array(), $flag=false, $limited = false){
		
		if( $flag != true ) {
			
			$data = $this->pmdm_wp_nohtml_kses($data);
			
		} else {
			
			if( $limited == true ) {
				$data = wp_kses_post( $data );
			}
			
		}
		$data = stripslashes_deep($data);
		return $data;
	}

	/**
	 * Strip Html Tags 
	 * 
	 * It will sanitize text input (strip html tags, and escape characters)
	 * 
	 * @package Post Meta Data Manager
	 * @since 1.0
	 */
	public function pmdm_wp_nohtml_kses($data = array()) {
		
		
		if ( is_array($data) ) {
			
			$data = array_map(array($this,'pmdm_wp_nohtml_kses'), $data);
			
		} elseif ( is_string( $data ) ) {
			
			$data = wp_kses_post($data);
		}
		
		return $data;
	}	

	/**
	 * Adding Hooks
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0
	 */
	function add_hooks(){

		// add data table
		add_action( 'add_meta_boxes', array( $this, 'pmdm_wp_add_meta_boxes' ), 1000, 2 );

		// Delete Ajax
		add_action("wp_ajax_pmdm_wp_delete_meta", array( $this, "pmdm_wp_ajax_delete_meta" ) ) ;
		add_action( "wp_ajax_nopriv_pmdm_wp_delete_meta", array( $this, "pmdm_wp_ajax_delete_meta") );
		add_action( "admin_init", array( $this, "pmdm_wp_change_post_meta") );

	}
}
?>