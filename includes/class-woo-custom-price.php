<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class Woo_Custom_Price.
 *
 * @since 1.0.0
 */
class Woo_Custom_Price {

    public object $admin;

    /**
     * File.
     *
     * @var string $file File.
     *
     * @since 1.0.0
     */
    public string $file;

    /**
     * Plugin Version.
     *
     * @var mixed|string $version Version.
     *
     * @since 1.0.0
     */
    public string $version = '1.0.0';

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( $file, $version = '1.0.0' ) {
        $this->file = $file;
        $this->version = $version;
        $this->define_constant();
        $this->activation();
        $this->init_hooks();
    }

    /**
     * Define constant.
     *
     * @since 1.0.0
     * @return void
     */
    public function define_constant(){
        define( 'WOOCP_VERSION', $this->version );
        define( 'WOOCP_PLUGIN_DIR', plugin_dir_path( $this->file ) );
        define( 'WOOCP_PLUGIN_URL', plugin_dir_url( $this->file ) );
        define( 'WOOCP_PLUGIN_BASENAME', plugin_basename( $this->file ) );
    }

    /**
     * Activation.
     *
     * @since 1.0.0
     * @return void
     */
    public function activation() {
        register_activation_hook( $this->file, array( $this, 'activation_hook' ) );
    }

    /**
     * Activation hook.
     *
     * @since 1.0.0
     * @return void
     */
    public function activation_hook() {
        update_option( 'woocp_version', $this->version );
    }

    /**
     * Init hooks.
     *
     * @since 1.0.0
     * @return void
     */
    public function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'admin_notices', array( $this, 'dependencies_notices' ) );
        add_action( 'woocommerce_init', array( $this, 'init' ) );
    }

    /**
     * Load textdomain.
     *
     * @since 1.0.0
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'woo-custom-price', false, plugin_basename( $this->file ) );
    }

    /**
     * Dependencies notices.
     *
     * @since 1.0.0
     * @return void
     */
    public function dependencies_notices() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            printf( '<div id="message" class="notice is-dismissible notice-warning"><p>%s</p></div>', '"Woo Custom Price" plugin is required to use WooCommerce.' );
        }
    }

    /**
     * Init.
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        if ( is_admin() ) {
            // Include admin classes.
            new Admin();
        }

        if ( 'enable' === get_option( 'woocp_status', 'enable' ) ) {
            // Frontend methods.
            add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'loop_add_to_cart_link' ], PHP_INT_MAX, 2 );
            add_filter( 'woocommerce_get_price_html', [ $this, 'replace_original_price' ], PHP_INT_MAX, 2 );
            add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'custom_price_html' ), PHP_INT_MAX );
            add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'add_to_cart_validation' ], PHP_INT_MAX, 2 );
            add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], PHP_INT_MAX );
            add_filter( 'woocommerce_get_cart_contents', [ $this, 'get_cart_contents' ], PHP_INT_MAX, 1 );
        }
    }

    /**
     * Loop add to cart link.
     *
     * @param string $html Add to cart link HTML.
     * @param object $product Product object.
     *
     * @since 1.0.0
     * @return string
     */
    public function loop_add_to_cart_link( $html, $product ) {
        if ( 'show' !== get_option( 'woocp_loop_add_to_cart_btn', 'show' ) ) {
            return $html;
        }

        $get_post_meta = get_post_meta($product->get_id(), '_woonp_status', true);
        // TODO: Will check it after finishing the plugin settings.
//        if ( ( WoonpHelper::get_setting( 'atc_button', 'show' ) === 'hide' ) &&
//            ( ( WoonpHelper::get_setting( 'global_status', 'enable' ) === 'enable' && $get_post_meta !== 'disable' ) ||
//                ( WoonpHelper::get_setting( 'global_status', 'enable' ) === 'disable' && $get_post_meta === 'overwrite' ) )
//        ) {
//            return '';
//        }

        return $html;
    }

    /**
     * Replace original product price.
     *
     * @param string $price Product price.
     * @param object $product Product object.
     *
     * @since 1.0.0
     * @return string
     */
    public function replace_original_price( $price, $product ) {
        if ( is_admin() ) {
            return $price;
        }

        $get_post_meta = get_post_meta( $product->get_id(), '_woonp_status', true );

        // TODO: Need to check the plugin settings then replace the original price depends on the settings
//        if (
//            ( WoonpHelper::get_setting( 'global_status', 'enable' ) === 'enable' && $get_post_meta !== 'disable' ) ||
//            ( WoonpHelper::get_setting( 'global_status', 'enable' ) === 'disable' && $get_post_meta === 'overwrite' )
//        ) {
//            $suggested_price = apply_filters( 'woonp_suggested_price', WoonpHelper::get_setting( 'suggested_price', /* translators: price */ esc_html__( 'Suggested Price: %s', 'wpc-name-your-price' ) ), $product_id );
//
//            return sprintf( $suggested_price, $price );
//        }

        return $price;
    }



    /**
     * Custom price HTML.
     *
     * @since 1.0.0
     * @return void
     */
    public function custom_price_html() {


        global $product;
        $post_id = get_the_ID();

        $input_label = WoonpHelper::get_input_value($post_id,'_woocp_input_label_text','woocp_input_label_text');
        $minimum_price = WoonpHelper::get_input_value($post_id,'_woocp_minimum_price','woocp_minimum_price');
        $maximum_price = WoonpHelper::get_input_value($post_id,'_woocp_maximum_price','woocp_maximum_price');
        $step = WoonpHelper::get_input_value($post_id,'_woocp_step','woocp_step');
//        var_dump($minimum_price);wp_die();
        //=========

        $product_price=floatval($product->get_price());
        $value=number_format($product_price, 2, '.', '');
//        $input_label = get_option( 'woocp_input_label_text', 'Enter Your Price' );
//        $minimum_price= absint( get_option('woocp_minimum_price',1) );
//        $maximum_price= intval( get_option('woocp_maximum_price',1000) );
//        $step= intval( get_option('woocp_step',1) );

        ob_start();
        ?>

        <div class="woocp-price-input">
            <label for="woocp_custom_price"><?php echo esc_html( $input_label ); ?></label>
            <input type="number" id="woocp_custom_price" name="woocp_custom_price" step="<?php echo esc_attr( $step ); ?>" min="<?php echo esc_attr( $minimum_price ); ?>" max="<?php echo esc_attr( $maximum_price ); ?>" value="<?php echo esc_attr(  $value); ?>" />

        </div>

        <?php
        echo ob_get_clean();


//        global $product;

//        if ( self::is_woonp_product() ) {
//            // $status !== 'disable'
//            $product_id    = $product->get_id();
//            $global_status = WoonpHelper::get_setting( 'global_status', 'enable' );
//            $status        = get_post_meta( $product_id, '_woonp_status', true ) ?: 'default';
//            $type          = $min = $max = $step = $values = '';
//
//            if ( $status === 'overwrite' ) {
//                $global_status = 'enable';
//                $type          = get_post_meta( $product_id, '_woonp_type', true );
//                $min           = get_post_meta( $product_id, '_woonp_min', true );
//                $max           = get_post_meta( $product_id, '_woonp_max', true );
//                $step          = get_post_meta( $product_id, '_woonp_step', true );
//                $values        = get_post_meta( $product_id, '_woonp_values', true );
//            }
//
//            if ( $status === 'default' ) {
//                $type   = WoonpHelper::get_setting( 'type', 'default' );
//                $min    = WoonpHelper::get_setting( 'min' );
//                $max    = WoonpHelper::get_setting( 'max' );
//                $step   = WoonpHelper::get_setting( 'step' );
//                $values = WoonpHelper::get_setting( 'values' );
//            }
//
//            if ( $global_status === 'disable' ) {
//                return;
//            }
//
//            switch ( WoonpHelper::get_setting( 'value', 'price' ) ) {
//                case 'price':
//                    $value = self::sanitize_price( $product->get_price() );
//                    break;
//                case 'min':
//                    $value = self::sanitize_price( $min );
//                    break;
//                case 'max':
//                    $value = self::sanitize_price( $max );
//                    break;
//                default:
//                    $value = '';
//            }
//
//            if ( is_product() && isset( $_REQUEST['woonp'] ) ) {
//                $value = self::sanitize_price( $_REQUEST['woonp'] );
//            }
//
//            $input_id    = 'woonp_' . $product_id;
//            $input_label = apply_filters( 'woonp_input_label', WoonpHelper::get_setting( 'label', /* translators: currency */ esc_html__( 'Name Your Price (%s) ', 'wpc-name-your-price' ) ), $product_id );
//            $label       = sprintf( $input_label, get_woocommerce_currency_symbol() );
//            $price       = '<div class="' . esc_attr( apply_filters( 'woonp_input_class', 'woonp woonp-' . $status . ' woonp-type-' . $type, $product ) ) . '" data-min="' . esc_attr( $min ) . '" data-max="' . esc_attr( $max ) . '" data-step="' . esc_attr( $step ) . '">';
//            $price       .= '<label for="' . esc_attr( $input_id ) . '">' . esc_html( $label ) . '</label>';
//
//            if ( ( $type === 'select' ) && ( $values = WPCleverWoonp::get_values( $values ) ) && ! empty( $values ) ) {
//                // select
//                $select = '<select id="' . esc_attr( $input_id ) . '" class="woonp-select" name="woonp">';
//
//                foreach ( $values as $v ) {
//                    $select .= '<option value="' . esc_attr( $v['value'] ) . '" ' . ( $value == $v['value'] ? 'selected' : '' ) . '>' . $v['name'] . '</option>';
//                }
//
//                $select .= '</select>';
//
//                $price .= apply_filters( 'woonp_input_select', $select, $product );
//            } else {
//                // default
//                $input = '<input type="number" id="' . esc_attr( $input_id ) . '" class="woonp-input" step="' . esc_attr( $step ) . '" min="' . esc_attr( $min ) . '" max="' . esc_attr( 0 < $max ? $max : '' ) . '" name="woonp" value="' . esc_attr( $value ) . '" size="4"/>';
//
//                $price .= apply_filters( 'woonp_input_number', $input, $product );
//            }
//
//            $price .= '</div>';
//
//            echo apply_filters( 'woonp_input', $price, $product );
//        }
    }

    /**
     * Add to cart validation.
     *
     * @param bool $validation Boolean value.
     * @param int $product Product ID.
     *
     * @since 1.0.0
     * @retun void
     */
    public function add_to_cart_validation( $validation, $product ) {

        if ( isset( $_REQUEST['woocp_custom_price'] ) ) {

            $price = (float) $_REQUEST['woocp_custom_price'];

            if ( $price < 0 ) {
                wc_add_notice( esc_html__( 'You can\'t fill the negative price.', 'woo-custom-price' ), 'error' );

                return false;
            }
        }


//        if ( isset( $_REQUEST['woonp'] ) ) {
//            $price = (float) $_REQUEST['woonp'];
//
//            } else {
//                $status = get_post_meta( $product_id, '_woonp_status', true ) ?: 'default';
//                $step   = 1;
//
//                if ( $status === 'overwrite' ) {
//                    $min  = (float) get_post_meta( $product_id, '_woonp_min', true );
//                    $max  = (float) get_post_meta( $product_id, '_woonp_max', true );
//                    $step = (float) ( get_post_meta( $product_id, '_woonp_step', true ) ?: 1 );
//                } elseif ( $status === 'default' ) {
//                    $status = WoonpHelper::get_setting( 'global_status', 'enable' );
//                    $min    = (float) WoonpHelper::get_setting( 'min' );
//                    $max    = (float) WoonpHelper::get_setting( 'max' );
//                    $step   = (float) ( WoonpHelper::get_setting( 'step' ) ?: 1 );
//                }
//
//                if ( $step <= 0 ) {
//                    $step = 1;
//                }
//
//                if ( $status !== 'disable' ) {
//                    $pow = pow( 10, strlen( (string) $step ) );
//                    $mod = ( ( $price * $pow ) - ( $min * $pow ) ) / ( $step * $pow );
//
//                    if ( ( $min && ( $price < $min ) ) || ( $max && ( $price > $max ) ) || ( $mod != intval( $mod ) ) ) {
//                        wc_add_notice( esc_html__( 'Invalid price. Please try again!', 'wpc-name-your-price' ), 'error' );
//
//                        return false;
//                    }
//                }
//            }
//        }

        return $validation;
    }

    /**
     * Add to cart validation.
     *
     * @param array $data Item data.
     *
     * @since 1.0.0
     * @retun array
     */
    public function add_cart_item_data( $data ) {
        if ( isset( $_REQUEST['woocp_custom_price'] ) ) {
            $data['woocp_custom_price'] = self::sanitize_price( $_REQUEST['woocp_custom_price'] );
            unset( $_REQUEST['woocp_custom_price'] );
        }

        return $data;
    }

    /**
     * Get cart contents.
     *
     * @param array $cart_contents Cart contents.
     *
     * @since 1.0.0
     * @retun array Cart contents.
     */
    public function get_cart_contents( $cart_contents ) {
        foreach ( $cart_contents as $cart_item ) {
            $price = $cart_item['woocp_custom_price'];
            if ( ! isset( $cart_item['woocp_custom_price']) ) {
                continue;
            }

            $cart_item['data']->set_price( $price );
            $cart_item['data']->set_regular_price( $price );
            $cart_item['data']->set_sale_price( $price );
        }

        return $cart_contents;
    }

    /**
     * Sanitize price.
     *
     * @param string $price Price.
     *
     * @since 1.0.0
     * @retun float
     */
    public static function sanitize_price( $price ) {
        return filter_var( sanitize_text_field( $price ), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
    }

}