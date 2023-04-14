<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
$post_types = get_post_types([], "objects");


$default_post_type_enabled = array(
    "post",
    "page",
    "product",
    "shop_order",
    "shop_coupon",
);
$default_post_type_enabled = apply_filters(PMDM_WP_PREFIX . "_default_post_types", $default_post_type_enabled);
$pmdm_selected_post_types = get_option("pmdm_selected_post_types");
if(!empty($pmdm_selected_post_types)){
    $default_post_type_enabled = $pmdm_selected_post_types;
}
$all_taxonomoies = get_taxonomies([], "objects");
$default_taxonomies_type_enabled = array(
    "category",
    "post_tag",
    "product_cat",
    "product_tag",
);
$default_taxonomies_type_enabled = apply_filters(PMDM_WP_PREFIX . "_default_taxonomies", $default_taxonomies_type_enabled);
$pmdm_selected_taxonomies = get_option("pmdm_selected_taxonomies");
if(!empty($pmdm_selected_taxonomies)){
    $default_taxonomies_type_enabled = $pmdm_selected_taxonomies;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('PMDM General Settings', 'pmdm_wp'); ?></h1>
    <form method="post" action="options.php" novalidate="novalidate">
        <?php settings_fields( 'pmdm_general_settings_group' ); ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="blogname"><?php esc_html_e('Post types', 'pmdm_wp'); ?></label></th>
                    <td> 
                        <fieldset>
                            <?php
                                if(!empty($post_types)){
                                    foreach($post_types as $ptk => $ptv){
                                        $checked = "";
                                        if(in_array($ptk, $default_post_type_enabled)){
                                            $checked = "checked='checked'";
                                        }
                                        ?>
                                            <label class="pmdm_selected_post_types_wrapper">
                                                <input name="pmdm_selected_post_types[]" type="checkbox" value="<?php echo $ptk; ?>" <?php echo $checked; ?>> <?php echo $ptv->label; ?>
                                            </label>
                                        <?php
                                        
                                    }
                                }
                            ?>
                            
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="blogname"><?php esc_html_e('Taxonomies', 'pmdm_wp'); ?></label></th>
                    <td> 
                        <fieldset>
                            <?php
                                if(!empty($all_taxonomoies)){
                                    
                                    foreach($all_taxonomoies as $ptk => $ptv){
                                        $checked = "";
                                        if(in_array($ptk, $default_taxonomies_type_enabled)){
                                            $checked = "checked='checked'";
                                        }
                                        ?>
                                            <label class="pmdm_selected_post_types_wrapper">
                                                <input name="pmdm_selected_taxonomies[]" type="checkbox" value="<?php echo $ptk; ?>" <?php echo $checked; ?>> <?php echo $ptv->label; ?>
                                            </label>
                                        <?php
                                        
                                    }
                                }
                            ?>
                            
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save  "></p>
    </form>

</div>