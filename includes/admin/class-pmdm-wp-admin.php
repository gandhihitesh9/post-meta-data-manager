<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
	exit;
}
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Admin Class
 *
 * Manage Admin Panel Class
 *
 * @package Post Meta Data Manager
 * @since 1.0
 */

class Pmdm_Wp_Admin
{


	public $scripts;

	// class constructor
	function __construct()
    {

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
	public function pmdm_wp_add_meta_boxes($post_type, $post)
    {

		$current_screen = get_current_screen();

		$metabox_id               = 'pmdm-wp';
		$metabox_title            = esc_html__('Post Metadata Manager', 'pmdm_wp');
		$metabox_screen           = $post_type;
		$metabox_context          = 'normal';
		$metabox_priority         = 'low';
		$pmdm_selected_post_types = get_option('pmdm_selected_post_types');

		if (class_exists(OrderUtil::class) && class_exists(CustomOrdersTableController::class)) {
			if (OrderUtil::custom_orders_table_usage_is_enabled() && isset($_GET['page']) && $_GET['page'] == 'wc-orders') {
				if (! $post->get_id()) {
					return;
				}

				if (! current_user_can('edit_post', $post->get_id())) {
					return;
				}
				if ($post_type == 'woocommerce_page_wc-orders' && $_GET['action'] != 'edit') {  // HPOS
					return;
				}
				$pmdm_selected_post_types[] = 'woocommerce_page_wc-orders';

				if (in_array($post_type, $pmdm_selected_post_types)) {
					$metabox_screen = wc_get_container()->get(
						CustomOrdersTableController::class
					)->custom_orders_table_usage_is_enabled()
						? wc_get_page_screen_id('shop-order')
						: 'shop_order';

					add_meta_box($metabox_id, $metabox_title, array( $this, 'pmdm_wp_display_post_metadata' ), $metabox_screen, $metabox_context, $metabox_priority, array());
				}
			} else {
				if (isset($current_screen->action) && $current_screen->action == 'add') { // check new post
					return;
				}
				if (! $post->ID) {
					return;
				}

				if (! current_user_can('edit_post', $post->ID)) {
					return;
				}
				if (empty($pmdm_selected_post_types)) {
					$pmdm_selected_post_types = array(
						'post',
						'page',
						'product',
						'shop_order',
						'shop_coupon',
					);
				}

				if (in_array($post_type, $pmdm_selected_post_types)) {
					add_meta_box($metabox_id, $metabox_title, array( $this, 'pmdm_wp_display_post_metadata' ), $metabox_screen, $metabox_context, $metabox_priority, array());
				}
			}
		} else {
			if (isset($current_screen->action) && $current_screen->action == 'add') { // check new post
				return;
			}
			if (! $post->ID) {
				return;
			}

			if (! current_user_can('edit_post', $post->ID)) {
				return;
			}
			if (empty($pmdm_selected_post_types)) {
				$pmdm_selected_post_types = array(
					'post',
					'page',
					'product',
					'shop_order',
					'shop_coupon',
				);
			}
			if (in_array($post_type, $pmdm_selected_post_types)) {
				add_meta_box($metabox_id, $metabox_title, array( $this, 'pmdm_wp_display_post_metadata' ), $metabox_screen, $metabox_context, $metabox_priority, array());
			}
		}
	}

	/**
	 *
	 * Dialay Meta data in Meta box in Posts, Pages and CPT
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0
	 */
	public function pmdm_wp_display_post_metadata($post)
    {

		$post_meta = array();

		if (class_exists(OrderUtil::class)) {
			if (OrderUtil::custom_orders_table_usage_is_enabled() && isset($_GET['page']) && $_GET['page'] == 'wc-orders') {
				if (! $post->get_id()) {
					return;
				}
				$order = wc_get_order($post->get_id());

				if ($order) { // HPOS
					$order_meta = $order->get_meta_data();
					if (! empty($order_meta)) {
						foreach ($order_meta as $meta_data) {
							$post_meta[ $meta_data->key ] = $meta_data->value;
						}
					}
					
					$date_paid = $order->get_date_paid('edit');
					if ($date_paid) {
						$post_meta[ "date_paid" ] = wc_format_datetime($date_paid);
					}
				}
			} else {
                if (! $post->ID) {
                    return;
                }
                $post_meta = get_post_meta($post->ID);
            }
		} else {
			if (! $post->ID) {
				return;
			}
			$post_meta = get_post_meta($post->ID);
		}

		/**
		 * @since 1.0.2
		 */
		require_once PMDM_WP_ADMIN_DIR . '/html/pmdm_wp_display_post_metadata_html.php';
	}

	/**
	 * Delete Meta Ajax
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0
	 */
	public function pmdm_wp_ajax_delete_meta()
    {
		if (isset($_POST) && ! empty($_POST['post_id']) && $_POST['meta_id'] && current_user_can('administrator') && wp_verify_nonce($_POST['security'], 'ajax-security')) {
			$meta_value = '';
			$post_id    = intval($_POST['post_id']);
			$meta_id    = esc_html($_POST['meta_id']);
			$post_type = get_post_type($post_id);
			
			$is_hpos = false;
			if (class_exists(OrderUtil::class) && $post_type == "shop_order") {
				if (OrderUtil::custom_orders_table_usage_is_enabled()) {
					$is_hpos = true;
				}
			}

			if ($is_hpos) {
				$order = wc_get_order($post_id);
				if ($order) {
					// HPOS
					if ($order) {
						$meta_value = $order->get_meta($meta_id, true);
					}
					
					if (empty($meta_value) && $meta_value != '0') {
						wp_send_json_error(
							array( 'msg' => esc_html__('You have enter incorrect meta_id ! Please try again', 'pmdm_wp') )
						);
					}

					if ($order) { // HPOS
						$order->delete_meta_data($meta_id);
						$order->save_meta_data();
						wp_send_json_success(
							array( 'msg' => esc_html__('Meta successfully deleted', 'pmdm_wp') )
						);
					}
				}
			} else {
				$meta_value = get_post_meta($post_id, $meta_id, true);
				
				if (empty($meta_value) && $meta_value != '0') {
					wp_send_json_error(
						array( 'msg' => esc_html__('You have enter incorrect meta_id ! Please try again', 'pmdm_wp') )
					);
				}

				delete_post_meta($post_id, $meta_id);

				wp_send_json_success(
					array( 'msg' => esc_html__('Meta successfully deleted', 'pmdm_wp') )
				);
			}
		} else {
			wp_send_json_error(
				array( 'msg' => esc_html__('There is something worong! Please try again', 'pmdm_wp') )
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
	public function pmdm_wp_delete_user_meta()
    {
		if (isset($_POST) && ! empty($_POST['user_ID']) && $_POST['meta_id'] && current_user_can('administrator') && wp_verify_nonce($_POST['security'], 'ajax-security')) {
			$user_ID = intval($_POST['user_ID']);
			$meta_id = esc_html($_POST['meta_id']);

			$user_meta_value = get_user_meta($user_ID, $meta_id, true);

			if (empty($user_meta_value) && $user_meta_value != '0') {
				wp_send_json_error(
					array( 'msg' => esc_html__('You have enter incorrect meta_id ! Please try again', 'pmdm_wp') )
				);
			}

			delete_user_meta($user_ID, $meta_id);

			wp_send_json_success(
				array( 'msg' => esc_html__('Meta successfully deleted', 'pmdm_wp') )
			);
		} else {
			wp_send_json_error(
				array( 'msg' => esc_html__('There is something worong! Please try again', 'pmdm_wp') )
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
	public function pmdm_wp_get_recursively_inputs($meta_main_key, $get_meta_field_values, $level_key = array())
    {

		if (is_array($get_meta_field_values)) {
			$is_editable = true;
			foreach ($get_meta_field_values as $gmfvk => $gmfvv) {
				if (is_array($gmfvv)) {
					$store_keys = array_merge($level_key, array( $gmfvk ));
					$this->pmdm_wp_get_recursively_inputs($meta_main_key, $gmfvv, $store_keys);
				} elseif (is_string($gmfvv)) {
						$input_name = $meta_main_key;

						$display_label = $meta_main_key;

					if (! empty($level_key)) {
						foreach ($level_key as $skk) {
							$input_name    .= '[' . $skk . ']';
							$display_label .= '=>' . $skk;
						}
					}
						$input_name    .= '[' . $gmfvk . ']';
						$display_label .= '=>' . $gmfvk;
					?>
						<div class="input_wrapper input_wrapper_arr">
							<p class="display_label_key">Key: <strong><?php echo esc_html($display_label); ?></strong></p>
							<input type="text" name="<?php echo $input_name; ?>" class="input_box" value="<?php echo htmlentities($gmfvv, ENT_QUOTES); ?>" />
						</div>
						<?php
				} else {
					$is_editable = false;
				}
			}
			if (! $is_editable) {
				esc_html_e('This key contains some object information. So it\'s not editable.', 'pmdm_wp');
			}
		} else {
			?>
			<input type="text" name="<?php echo $meta_main_key; ?>" value="<?php echo htmlentities($get_meta_field_values, ENT_QUOTES); ?>" /> <br />
			<?php
		}
	}


	/**
	 * save post meta data using approprite key
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0
	 */
	public function pmdm_wp_change_post_meta()
    {

		if (isset($_POST['change_post_meta_field']) && wp_verify_nonce($_POST['change_post_meta_field'], 'change_post_meta_action') && current_user_can('administrator')) {
			if (! empty($_POST)) {
				$is_hpos = false;
				if (class_exists(OrderUtil::class) && isset($_GET['page']) && $_GET['page'] == 'wc-orders') {
					if (OrderUtil::custom_orders_table_usage_is_enabled()) {
						$is_hpos = true;
					}
				}

				foreach ($_POST as $pk => $pv) {
					if ($pk == 'change_post_meta_field' || $pk == '_wp_http_referer' || $pk == 'current_post_id') {
						continue;
					}

					if (isset($_POST['changed_keys']) && $pk == $_POST['changed_keys']) {
						if ($is_hpos) {
								$order = wc_get_order($_POST['current_post_id']);

								$is_meta_exists = $order->get_meta($pk, true);

                            if (! empty($is_meta_exists)) {
                                if (is_array($pv)) {
                                    $pv = $this->pmdm_wp_escape_slashes_deep($pv);
                                } else {
                                    $pv = wp_kses_post($pv);
                                }
                                $order->update_meta_data($pk, $pv);
                                $order->save();
                            }
						} else {
							$is_meta_exists = get_post_meta(intval($_POST['current_post_id']), $pk, true);
							if (! empty($is_meta_exists)) {
								if (is_array($pv)) {
									$pv = $this->pmdm_wp_escape_slashes_deep($pv);
								} else {
									$pv = wp_kses_post($pv);
								}

								if (( $pk == 'product_ids' || $pk == 'exclude_product_ids' ) && isset($_POST['post_type']) && $_POST['post_type'] == 'shop_coupon') {
									$product_ids = implode(',', $pv);
									update_post_meta(intval($_POST['current_post_id']), $pk, $product_ids);
								} else {
									update_post_meta(intval($_POST['current_post_id']), $pk, $pv);
								}
							}
						}
					}
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
	public function pmdm_wp_change_user_meta()
    {

		if (isset($_POST['change_user_meta_field']) && wp_verify_nonce($_POST['change_user_meta_field'], 'change_user_meta_action') && current_user_can('administrator')) {
			if (! empty($_POST)) {
				foreach ($_POST as $pk => $pv) {
					if ($pk == 'change_user_meta_field' || $pk == '_wp_http_referer' || $pk == 'current_user_id') {
						continue;
					}
					if (isset($_POST['changed_keys']) && $pk == $_POST['changed_keys']) {
						if (is_array($pv)) {
							$pv = $this->pmdm_wp_escape_slashes_deep($pv);
						} else {
							$pv = wp_kses_post($pv);
						}

						update_user_meta(intval($_POST['current_user_id']), $pk, $pv);
					}
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
	public function pmdm_wp_escape_slashes_deep($data = array(), $flag = false, $limited = false)
    {

		if ($flag != true) {
			$data = $this->pmdm_wp_nohtml_kses($data);
		} elseif ($limited == true) {
				$data = wp_kses_post($data);
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
	public function pmdm_wp_nohtml_kses($data = array())
    {

		if (is_array($data)) {
			$data = array_map(array( $this, 'pmdm_wp_nohtml_kses' ), $data);
		} elseif (is_string($data)) {
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
	public function pmdm_wp_user_metadata_box($user)
    {
		global $current_screen;
		if (( $current_screen->id == 'user-edit' ) || ( $current_screen->id == 'profile' )) {
			$user_meta = get_user_meta($user->ID);

			require_once PMDM_WP_ADMIN_DIR . '/html/pmdm_wp_display_user_metadata_html.php';
		}
	}

	/**
	 * Display meta box in term edit page.
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0.3
	 */
	public function pmdm_wp_taxonomy_metadata_box($term)
    {

		$term_meta = get_term_meta($term->term_id);
		// update_term_meta
		// delete_term_meta

		require_once PMDM_WP_ADMIN_DIR . '/html/pmdm_wp_display_term_metadata_html.php';
	}
	/**
	 * Add meta box action for all taxonomies.
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0.3
	 */
	public function pmdm_add_html_for_all_taxonomy()
    {
		$all_taxonomies           = get_taxonomies();
		$pmdm_selected_taxonomies = get_option('pmdm_selected_taxonomies');
		if (empty($pmdm_selected_taxonomies)) {
			$pmdm_selected_taxonomies = array(
				'category',
				'post_tag',
				'product_cat',
				'product_tag',
			);
		}
		if (! empty($all_taxonomies)) {
			foreach ($all_taxonomies as $taxk => $taxv) {
				if (in_array($taxk, $pmdm_selected_taxonomies)) {
					add_action($taxk . '_edit_form', array( $this, 'pmdm_wp_taxonomy_metadata_box' ), 99);
				}
			}
		}
	}


	/**
	 * Save taxonomy meta data using approprite key
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0.3
	 */
	public function pmdm_wp_change_taxonomy_meta()
    {

		if (isset($_POST['change_term_meta_field']) && wp_verify_nonce($_POST['change_term_meta_field'], 'change_term_meta_action') && current_user_can('administrator')) {
			if (! empty($_POST)) {
				$disallow_term_key_array = apply_filters(
					PMDM_WP_PREFIX . '_disallow_term_keys',
					array(
						'action',
						'tag_ID',
						'taxonomy',
						'_wp_original_http_referer',
						'_wpnonce',
						'_wp_http_referer',
						'name',
						'slug',
						'parent',
						'description',
						'display_type',
						'product_cat_thumbnail_id',
						'pmdm_wp_term_table_length',
						'change_term_meta_field',
						'current_term_id',
					)
				);
				foreach ($_POST as $pk => $pv) {
					if (in_array($pk, $disallow_term_key_array)) {
						continue;
					}
					if (isset($_POST['changed_keys']) && $pk == $_POST['changed_keys']) {
						if (is_array($pv)) {
							$pv = $this->pmdm_wp_escape_slashes_deep($pv);
						} else {
							$pv = wp_kses_post($pv);
						}

						update_term_meta(intval($_POST['current_term_id']), $pk, $pv);
					}
				}

				/*
				if(isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"])){
					wp_redirect($_SERVER["HTTP_REFERER"]);
					die;
				} */
			}
		}
	}

	/**
	 * Delete term Meta Ajax
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0.3
	 */
	public function pmdm_wp_delete_term_meta()
    {
		if (isset($_POST) && ! empty($_POST['term_id']) && $_POST['meta_id'] && current_user_can('administrator') && wp_verify_nonce($_POST['security'], 'ajax-security')) {
			$term_value = '';
			$term_id    = intval($_POST['term_id']);
			$meta_id    = esc_html($_POST['meta_id']);

			$term_value = get_term_meta($term_id, $meta_id, true);

			if (empty($term_value) && $term_value != '0') {
				wp_send_json_error(
					array( 'msg' => esc_html__('You have enter incorrect meta_id ! Please try again', 'pmdm_wp') )
				);
			}

			delete_term_meta($term_id, $meta_id);

			wp_send_json_success(
				array( 'msg' => esc_html__('Meta successfully deleted', 'pmdm_wp') )
			);
		} else {
			wp_send_json_error(
				array( 'msg' => esc_html__('There is something worong! Please try again', 'pmdm_wp') )
			);
		}

		die();
	}

	public function pmdm_admin_menus()
    {
		$parent_page_slug = 'pmdm-general-settings';
		add_menu_page(
			esc_html__('PMDM Settings', 'pmdm_wp'),
			esc_html__('PMDM Settings', 'pmdm_wp'),
			'manage_options',
			$parent_page_slug,
			array( $this, 'pmdm_general_settings_cb' ),
		);
		add_submenu_page(
			$parent_page_slug,
			esc_html__('Help', 'pmdm_wp'),
			esc_html__('Help', 'pmdm_wp'),
			'manage_options',
			'pmdm-help',
			array( $this, 'pmdm_help_cb' ),
		);
	}

	public function pmdm_general_settings_cb()
    {
		require_once PMDM_WP_ADMIN_DIR . '/html/pmdm_general_settings_html.php';
	}
	public function pmdm_help_cb()
    {
		require_once PMDM_WP_ADMIN_DIR . '/html/pmdm_help_html.php';
	}

	public function pmdm_register_general_settings_cb()
    {
		register_setting('pmdm_general_settings_group', 'pmdm_selected_post_types');
		register_setting('pmdm_general_settings_group', 'pmdm_selected_taxonomies');
	}

	public function pmdm_hpos_declare_compatibility()
    {
		if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', PMDM_WP_PLUGIN_MAIN_FILE_PATH, true);
		}
	}

	/**
	 * Adding Hooks
	 *
	 * @package Post Meta Data Manager
	 * @since 1.0
	 */
	public function add_hooks()
    {

		// post details page hooks
		add_action('add_meta_boxes', array( $this, 'pmdm_wp_add_meta_boxes' ), 1000, 2);
		add_action('admin_init', array( $this, 'pmdm_wp_change_post_meta' ), 10);

		add_action('wp_ajax_pmdm_wp_delete_meta', array( $this, 'pmdm_wp_ajax_delete_meta' ));

		// user details page hooks
		add_action('edit_user_profile', array( $this, 'pmdm_wp_user_metadata_box' ), 99);
		add_action('show_user_profile', array( $this, 'pmdm_wp_user_metadata_box' ), 99);
		add_action('admin_init', array( $this, 'pmdm_wp_change_user_meta' ), 11);
		add_action('wp_ajax_pmdm_wp_delete_user_meta', array( $this, 'pmdm_wp_delete_user_meta' ));

		// taxonomy details page hooks
		add_action('admin_init', array( $this, 'pmdm_add_html_for_all_taxonomy' ), 99);
		add_action('admin_init', array( $this, 'pmdm_wp_change_taxonomy_meta' ), 12);
		add_action('wp_ajax_pmdm_wp_delete_term_meta', array( $this, 'pmdm_wp_delete_term_meta' ));

		add_action('admin_menu', array( $this, 'pmdm_admin_menus' ), 10);
		add_action('admin_init', array( $this, 'pmdm_register_general_settings_cb' ));

		add_action('before_woocommerce_init', array( $this, 'pmdm_hpos_declare_compatibility' ));
	}
}
?>
