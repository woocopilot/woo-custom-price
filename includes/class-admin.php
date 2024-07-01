<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin.
 *
 * @since 1.0.0
 */
class Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 59 );
        add_action( 'admin_post_woocp_update_settings', array( $this, 'update_settings' ) );

        // Product metaboxes.
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_data_tab' ) );
        add_action('woocommerce_product_data_panels', array( $this, 'add_data_panel' ) );
        add_action('woocommerce_process_product_meta', array( $this, 'save_product_meta' ) );

        // Enqueue admin scripts.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Add sub menu.
     *
     * @since 1.0.0
     * @return void
     */
    public function admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Woo Custom Prices', 'woo-custom-price' ),
            __( 'Woo Custom Prices', 'woo-custom-price' ),
            'manage_woocommerce',
            'woo-custom-price',
            array( $this, 'render_settings_page' ),
        );
    }

    /**
     * Settings.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_settings_page() {
        ?>
        <div class="wrap wooco-wrap">
            <h1><?php esc_html_e( 'Woo Custom Price', 'woo-custom-price' ); ?></h1>
            <p><?php esc_html_e( 'Below are the plugin options that will determine how the plugin will work.', 'woo-custom-price' ); ?></p>

            <form class="woocp-settings-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

                <div class="form-heading">
                    <h2><?php esc_html_e( 'General Options', 'woo-custom-price' ); ?></h2>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="woocp_status_enabled"><?php esc_html_e( 'Status', 'woo-custom-price' );?></label>
                    </div>
                    <div class="form-field">
                        <label for="woocp_status_enabled"><input type="radio" id="woocp_status_enabled" name="woocp_status" value="enable" <?php checked('enable', get_option( 'woocp_status' ) ); ?>><?php esc_html_e( 'Enable', 'woo-custom-price' ); ?></label>
                        <label for="woocp_status_disabled"><input type="radio" id="woocp_status_disabled" name="woocp_status" value="disable" <?php checked('disable', get_option( 'woocp_status' ) ); ?>><?php esc_html_e( 'Disable', 'woo-custom-price' ); ?></label>
                        <p><?php esc_html_e( 'Enable this to apply the plugin features.', 'woo-custom-price' ); ?></p>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="woocp_loop_add_to_cart_btn_show"><?php esc_html_e( 'Add to cart button', 'woo-custom-price' );?></label>
                    </div>
                    <div class="form-field">
                        <label for="woocp_loop_add_to_cart_btn_show"><input type="radio" id="woocp_loop_add_to_cart_btn_show" name="woocp_loop_add_to_cart_btn" value="enable" <?php checked('enable', get_option( 'woocp_loop_add_to_cart_btn' )); ?>><?php esc_html_e( 'Show', 'woo-custom-price' ); ?></label>
                        <label for="woocp_loop_add_to_cart_btn_hide"><input type="radio" id="woocp_loop_add_to_cart_btn_hide" name="woocp_loop_add_to_cart_btn" value="disable" <?php checked('disable', get_option( 'woocp_loop_add_to_cart_btn' )); ?>><?php esc_html_e( 'Hide', 'woo-custom-price' ); ?></label>
                        <p><?php esc_html_e( 'Show/hide add to cart button on the shop/archive page.', 'woo-custom-price' ); ?></p>
                    </div>
                </div>

                <div class="form-heading">
                    <h2><?php esc_html_e( 'Options for Custom Price Input', 'woo-custom-price' ); ?></h2>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="woocp_input_label_text"><?php esc_html_e( 'Label', 'woo-custom-price' ); ?></label>
                    </div>
                    <div class="form-field">
                        <input type="text" name="woocp_input_label_text" id="woocp_input_label_text" placeholder="Name Your Price" value="<?php echo esc_attr( get_option('woocp_input_label_text', 'Enter Your Price' ) ); ?>" />
                        <p><?php esc_html_e( 'Enter the custom price field label.', 'woo-custom-price' ); ?></p>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="woocp_minimum_price"><?php esc_html_e( 'Minimum', 'woo-custom-price' ); ?></label>
                    </div>
                    <div class="form-field">
                        <input type="number" id="woocp_minimum_price" name="woocp_minimum_price" step="any" placeholder="Enter the minimum value" value="<?php echo esc_attr( get_option( 'woocp_minimum_price', floatval( '1' ) ) ); ?>" />
                        <p><?php esc_html_e( 'Enter the minimum value. You can still override it on a per-product basis.', 'woo-custom-price' ); ?></p>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="woocp_maximum_price"><?php esc_html_e( 'Maximum', 'woo-custom-price' ); ?></label>
                    </div>
                    <div class="form-field">
                        <input type="number" id="woocp_maximum_price" step="any" name="woocp_maximum_price" placeholder="Enter the maximum value" value="<?php echo esc_attr( get_option( 'woocp_maximum_price', floatval( '1000' ) ) ); ?>" />
                        <p><?php esc_html_e( 'Enter the maximum value. You can still override it on a per-product basis.', 'woo-custom-price' ); ?></p>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="woocp_step"><?php esc_html_e( 'Step', 'woo-custom-price' ); ?></label>
                    </div>
                    <div class="form-field">
                        <input type="number" id="woocp_step" name="woocp_step" step="any" value="<?php echo esc_attr( get_option( 'woocp_step', floatval( '0.01' ) ) ); ?>" />
                        <p><?php esc_html_e( 'Enter the step value. You can still override it on a per-product basis.', 'woo-custom-price' ); ?></p>
                    </div>
                </div>

                <?php wp_nonce_field( 'woocp_update_settings' ); ?>
                <input type="hidden" name="action" value="woocp_update_settings">
                <?php submit_button( __( 'Save Settings', 'woo-custom-price' ) ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Update settings.
     *
     * @since 1.0.0
     * @return void
     */
    public function update_settings() {
        check_admin_referer( 'woocp_update_settings' );
        $referrer = wp_get_referer();

        $status = isset( $_POST['woocp_status'] ) ? sanitize_key( $_POST['woocp_status'] ) : '';
        $loop_add_to_cart_btn = isset( $_POST['woocp_loop_add_to_cart_btn'] ) ? sanitize_key( $_POST['woocp_loop_add_to_cart_btn'] ) : '';
        $input_label = isset( $_POST['woocp_input_label_text'] ) ? sanitize_text_field( $_POST['woocp_input_label_text'] ) : '';
        $minimum_price = isset( $_POST['woocp_minimum_price'] ) ? floatval( $_POST['woocp_minimum_price'] ) : '';
        $maximum_price = isset( $_POST['woocp_maximum_price'] ) ? floatval( $_POST['woocp_maximum_price'] ) : '';
        $step = isset( $_POST['woocp_step'] ) ? floatval( $_POST['woocp_step'] ) : '';

        // Update settings.
        update_option( 'woocp_status', $status );
        update_option( 'woocp_loop_add_to_cart_btn', $loop_add_to_cart_btn );
        update_option( 'woocp_input_label_text', $input_label );
        update_option( 'woocp_minimum_price', $minimum_price );
        update_option( 'woocp_maximum_price', $maximum_price );
        update_option( 'woocp_step', $step );

        wp_safe_redirect( $referrer );
    }

    /**
     * Add product data tab.
     *
     * @since 1.0.0
     * @retun array
     */
    public function add_data_tab($tabs) {
        $tabs['woocp_custom_price'] = array(
            'label'    => __('Woo Custom Price', 'woop-custom-price'),
            'target'   => 'woocp_product_data',
            'class'    => array('show_if_simple', 'show_if_variable'),
            'priority' => 21,
        );

        return $tabs;
    }

    /**
     * Add product data panel.
     *
     * @since 1.0.0
     * @retun void
     */
    public function add_data_panel() {

        global $woocommerce, $post;
        $post_id = get_the_ID();
        $price_label = WoonpHelper::get_input_value($post_id,'_woocp_input_label_text','woocp_input_label_text');
        $woocp_minimum_price = WoonpHelper::get_input_value($post_id,'_woocp_minimum_price','woocp_minimum_price');
        $woocp_maximum_price = WoonpHelper::get_input_value($post_id,'_woocp_maximum_price','woocp_maximum_price');
        $woocp_step = WoonpHelper::get_input_value($post_id,'_woocp_step','woocp_step');

        ?>
        <div id='woocp_product_data' class='panel woocommerce_options_panel'>
            <div class='options_group'>
                <?php
                woocommerce_wp_text_input(array(
                    'id'          => '_woocp_input_label_text',
                    'label'       => __('Price Label', 'woo-custom-price'),
                    'placeholder' => 'Enter Your Price',
                    'description' => __('Enter the custom price field label.', 'woo-custom-price'),
                    'desc_tip'    => 'true',
                    'value'       => esc_attr( $price_label ),
                    'type'        => 'text',
                ));

                woocommerce_wp_text_input(array(
                    'id'          => '_woocp_minimum_price',
                    'label'       => __('Minimum', 'woo-custom-price'),
                    'placeholder' => 'Enter the minimum value',
                    'description' => __('Enter the minimum value (ex: 1). Keep this empty or enter 0 for global settings.', 'woo-custom-price'),
                    'desc_tip'    => 'true',
                    'type'        => 'number',
                    'value'       => esc_attr( $woocp_minimum_price ),
                    'custom_attributes' => array(
                        'step' => 'any',
                        'min'  => 0,
                    ),
                ));

                woocommerce_wp_text_input(array(
                    'id'          => '_woocp_maximum_price',
                    'label'       => __('Maximum', 'woo-custom-price'),
                    'placeholder' => 'Enter the maximum value',
                    'description' => __('Enter the maximum value (ex: 1000). Keep this empty or enter 0 for global settings.', 'woo-custom-price'),
                    'desc_tip'    => 'true',
                    'type'        => 'number',
                    'value'       => esc_attr( $woocp_maximum_price ),
                    'custom_attributes' => array(
                        'step' => 'any',
                        'min'  => 0,
                    ),

                ));

                woocommerce_wp_text_input(array(
                    'id'          => '_woocp_step',
                    'label'       => __('Step', 'woo-custom-price'),
                    'placeholder' => 'Enter the step value',
                    'description' => 'Enter the step value. Keep this empty or enter 0 for global settings.',
                    'desc_tip'    => 'true',
                    'type'        => 'number',
                    'value'       => esc_attr( $woocp_step ),
                    'custom_attributes' => array(
                        'step' => 'any',
                        'min'  => 0.01,
                    ),
                    'data_type' => 'price'
                ));
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save product meta.
     *
     * @since 1.0.0
     * @retun void
     */
    public function save_product_meta($post_id) {
        $price_label = isset($_POST['_woocp_input_label_text']) ? sanitize_text_field($_POST['_woocp_input_label_text']) : '';
        $min = isset($_POST['_woocp_minimum_price']) ? floatval($_POST['_woocp_minimum_price']) : '';
        $max = isset($_POST['_woocp_maximum_price']) ? floatval($_POST['_woocp_maximum_price']) : '';
        $step = isset($_POST['_woocp_step']) ? floatval($_POST['_woocp_step']) : '';

        update_post_meta($post_id, '_woocp_input_label_text', $price_label);
        update_post_meta($post_id, '_woocp_minimum_price', $min);
        update_post_meta($post_id, '_woocp_maximum_price', $max);
        update_post_meta($post_id, '_woocp_step', $step);
    }

    /**
     * Admin scripts.
     *
     * @param string $hook Page hook.
     *
     * @since 1.0.0
     * @retun void
     */
    public function enqueue_scripts( $hook ) {
        if ( 'woocommerce_page_woo-custom-price' === $hook ) {
            wp_enqueue_style( 'woocp_admin_style', WOOCP_PLUGIN_URL . 'assets/css/admin-style.css', array(), WOOCP_VERSION );
        }
    }

//    public function get_input_value( $post_id, $meta_key, $option_key ) {
//        $meta_value = get_post_meta($post_id, $meta_key, true);
//        if (metadata_exists('post', $post_id, $meta_key) && !empty($meta_value)) {
//            return $meta_value;
//        } else {
//            $option_value = get_option($option_key, '');
//            return !empty($option_value) ? esc_html($option_value) : '';
//        }
//    }
}