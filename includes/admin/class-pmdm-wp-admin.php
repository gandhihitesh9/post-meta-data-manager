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

		/**
		 * @since 1.0.2
		 */
		require_once(PMDM_WP_ADMIN_DIR . "/html/pmdm_wp_display_post_metadata_html.php");

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
			$meta_id = esc_html($_POST['meta_id']);

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
	* Delete User Meta Ajax
	*
	* @package Post Meta Data Manager
	* @since 1.0.2
	*/

	public function pmdm_wp_delete_user_meta() {
		if(isset($_POST) && !empty($_POST['user_ID']) && $_POST['meta_id']) {

			$user_ID = intval($_POST['user_ID']);
			$meta_id = esc_html($_POST['meta_id']);

			delete_user_meta($user_ID, $meta_id);
			
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
						<div class="input_wrapper input_wrapper_arr">
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
	* save user meta data using approprite key
	*
	* @package Post Meta Data Manager
	* @since 1.0.2
	*/
	public function pmdm_wp_change_user_meta(){

		if (isset( $_POST['change_user_meta_field'] ) && wp_verify_nonce( $_POST['change_user_meta_field'], 'change_user_meta_action' ) ) {
			
			if(!empty($_POST)){
				
				foreach($_POST as $pk => $pv){
					if($pk == "change_user_meta_field" || $pk == "_wp_http_referer" || $pk == "current_user_id"){
						continue;
					}
					if(is_array($pv)){
						$pv = $this->pmdm_wp_escape_slashes_deep($pv);
					}else{
						$pv = wp_kses_post($pv);
					}
					
					update_user_meta(intval($_POST["current_user_id"]), $pk, $pv);

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
	 * Display meta box in user edit page.
	 * 
	 * @package Post Meta Data Manager
	 * @since 1.0.2
	 */
	public function pmdm_wp_user_metadata_box($user) {
		$user_meta = get_user_meta( $user->ID );
		
		require_once(PMDM_WP_ADMIN_DIR . "/html/pmdm_wp_display_user_metadata_html.php");
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
		
		add_action("wp_ajax_pmdm_wp_delete_user_meta", array( $this, "pmdm_wp_delete_user_meta" ) ) ;
		add_action( "wp_ajax_nopriv_pmdm_wp_delete_user_meta", array( $this, "pmdm_wp_delete_user_meta") );
		
		add_action( "admin_init", array( $this, "pmdm_wp_change_post_meta") );

		
		add_action('edit_user_profile', array($this, 'pmdm_wp_user_metadata_box'), 99);
		add_action('admin_init', array($this, 'pmdm_wp_change_user_meta'));

	}
}
?>