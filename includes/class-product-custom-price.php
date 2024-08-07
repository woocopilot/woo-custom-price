<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class Product_Custom_Price.
 *
 * @since 1.0.0
 */
class Product_Custom_Price {

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
	 * @param string $file Plugin file path.
	 * @param string $version Plugin version.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $file, $version = '1.0.0' ) {
		$this->file    = $file;
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
	public function define_constant() {
		define( 'PCPRICE_VERSION', $this->version );
		define( 'PCPRICE_PLUGIN_DIR', plugin_dir_path( $this->file ) );
		define( 'PCPRICE_PLUGIN_URL', plugin_dir_url( $this->file ) );
		define( 'PCPRICE_PLUGIN_BASENAME', plugin_basename( $this->file ) );
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
		update_option( 'pcprice_version', $this->version );
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
		load_plugin_textdomain( 'product-custom-price', false, plugin_basename( $this->file ) );
	}

	/**
	 * Dependencies notices.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function dependencies_notices() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			printf( '<div id="message" class="notice is-dismissible notice-warning"><p>%s</p></div>', '"Product Custom Price" plugin is required to use WooCommerce.' );
		}
	}

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {

		// Include admin classes.
		if ( is_admin() ) {
			new Admin();
		}

		// Frontend methods.
		if ( 'enable' === get_option( 'pcprice_status', 'enable' ) ) {
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'loop_add_to_cart_link' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_get_price_html', array( $this, 'replace_original_price' ), PHP_INT_MAX, 2 );
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'custom_price_html' ), PHP_INT_MAX );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), PHP_INT_MAX );
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), PHP_INT_MAX );
			add_filter( 'woocommerce_get_cart_contents', array( $this, 'get_cart_contents' ), PHP_INT_MAX, 1 );
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
		if ( 'disable' === get_option( 'pcprice_loop_add_to_cart_btn', 'disable' ) && 'yes' === get_post_meta( $product->id, '_pcprice_is_enabled', true ) ) {
			return '';
		}

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
		$status    = get_post_meta( $product->get_id(), '_woonp_status', true );
		$min_value = absint( self::get_input_option( 'pcprice_minimum_price', $product->get_id(), '1' ) );

		if ( $status && $min_value > $product->get_price() ) {
			return wc_price( $min_value );
		}

		return $price;
	}

	/**
	 * Custom input option.
	 *
	 * @param string $key Option key.
	 * @param int    $post_id Post ID.
	 * @param string $value The default value.
	 *
	 * @since 1.0.0
	 * @return mixed|null
	 */
	public static function get_input_option( $key, $post_id, $value = null ) {

		$meta_value  = get_post_meta( $post_id, '_' . $key, true );
		$input_value = empty( $meta_value ) ? get_option( $key ) : $meta_value;

		return empty( $input_value ) ? $value : $input_value;
	}

	/**
	 * Custom price HTML.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function custom_price_html() {
		global $product;

		// Output the HTML input field.
		$minmum_price   = absint( self::get_input_option( 'pcprice_minimum_price', $product->get_id(), '10' ) );
		$original_price = floatval( $product->get_price() );
		$product_price  = $minmum_price > $original_price ? $minmum_price : $original_price;
		$value          = number_format( $product_price, 2, '.', '' );

		ob_start();
		?>
		<div class="pcprice-price-input">
			<label for="pcprice_custom_price"><?php echo esc_html( self::get_input_option( 'pcprice_input_label_text', $product->get_id(), 'Enter Custom Price' ) ); ?></label>
			<input type="number" id="pcprice_custom_price" name="pcprice_custom_price" step="<?php echo floatval( esc_attr( self::get_input_option( 'pcprice_step', $product->get_id(), '10' ) ) ); ?>" min="<?php echo esc_attr( $minmum_price ); ?>" max="<?php echo esc_attr( absint( self::get_input_option( 'pcprice_maximum_price', $product->get_id(), '10' ) ) ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
		</div>
		<?php
		echo wp_kses_post( ob_get_clean() );
	}

	/**
	 * Add to cart validation.
	 *
	 * @param bool $validation Boolean value.
	 *
	 * @since 1.0.0
	 * @retun void
	 */
	public function add_to_cart_validation( $validation ) {
		wp_verify_nonce( '_nonce' );

		if ( isset( $_REQUEST['pcprice_custom_price'] ) ) {

			$price = (float) $_REQUEST['pcprice_custom_price'];

			if ( $price < 0 ) {
				wc_add_notice( esc_html__( 'You can\'t fill the negative price.', 'product-custom-price' ), 'error' );

				return false;
			}
		}

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
		wp_verify_nonce( '_nonce' );

		if ( isset( $_REQUEST['pcprice_custom_price'] ) ) {
			$data['pcprice_custom_price'] = self::sanitize_price( sanitize_text_field( wp_unslash( $_REQUEST['pcprice_custom_price'] ) ) );
			unset( $_REQUEST['pcprice_custom_price'] );
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

			if ( ! isset( $cart_item['pcprice_custom_price'] ) ) {
				continue;
			}

			$price = $cart_item['pcprice_custom_price'];

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
