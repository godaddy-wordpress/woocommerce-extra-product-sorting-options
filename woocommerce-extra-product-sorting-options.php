<?php
/**
 * Plugin Name: Extra Product Sorting Options for WooCommerce
 * Plugin URI: http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/
 * Description: Rename default sorting and optionally extra product sorting options.
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com/
 * Version: 2.10.0
 * Text Domain: woocommerce-extra-product-sorting-options
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2014-2023, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2023, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * WC requires at least: 3.9.4
 * WC tested up to: 7.9.0
 */

defined( 'ABSPATH' ) or exit;

// WC version check
if ( ! WC_Extra_Sorting_Options::is_plugin_active( 'woocommerce.php' ) || version_compare( get_option( 'woocommerce_db_version' ), WC_Extra_Sorting_Options::MIN_WOOCOMMERCE_VERSION, '<' ) ) {
	add_action( 'admin_notices', array( 'WC_Extra_Sorting_Options', 'render_outdated_wc_version_notice' ) );
	return;
}

// Make sure we're loaded after WC and fire it up!
add_action( 'plugins_loaded', 'wc_extra_sorting_options' );

/**
 * Sets up the main plugin class.
 *
 * @since 2.0.0
 */
class WC_Extra_Sorting_Options {


	/** plugin version number */
	const VERSION = '2.10.0';

	/** required WooCommerce version number */
	const MIN_WOOCOMMERCE_VERSION = '3.9.4';

	/** @var WC_Extra_Sorting_Options single instance of this plugin */
	protected static $instance;


	/**
	 * Initializes the plugin.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		// modify product sorting settings
		add_filter( 'woocommerce_catalog_orderby',                 [ $this, 'modify_sorting_settings' ], 99 );
		// add new sorting options to order by dropdowns
		add_filter( 'woocommerce_default_catalog_orderby_options', [ $this, 'modify_sorting_settings' ], 99 );

		// if there is only one available sorting option, make it default
		add_filter( 'woocommerce_default_catalog_orderby', [ $this, 'maybe_update_default_catalog_orderby'] );

		// unhook the sorting dropdown completely if there are no options
		add_action( 'wp', [ $this, 'maybe_remove_catalog_orderby' ], 99 );

		// add new product sorting arguments
		add_filter( 'woocommerce_get_catalog_ordering_args', [ $this, 'add_new_shop_ordering_args' ] );

		// load translations
		add_action( 'init', [ $this, 'load_translation' ] );

		// add settings to customizer
		add_action( 'customize_register', [ $this, 'add_customizer_settings' ] );

		if ( is_admin() && ! is_ajax() ) {

			// add plugin links
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'add_plugin_links' ] );

			// run every time
			$this->install();
		}

		// handle HPOS compatibility
		add_action( 'before_woocommerce_init', [ $this, 'handle_hpos_compatibility' ] );
	}


	/**
	 * Declares HPOS compatibility.
	 *
	 * @since 2.10.0
	 *
	 * @internal
	 *
	 * @return void
	 */
	public function handle_hpos_compatibility()
	{
		if ( class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( __FILE__ ), true );
		}
	}


	/**
	 * Adds Settings to WooCommerce Settings > Products page after "Default Product Sorting" setting.
	 *
	 * @internal
	 *
	 * @since 2.7.0
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	public function add_customizer_settings( $wp_customize ) {

		// load our custom control type
		require_once __DIR__ . '/includes/class-wc-eso-customizer-checkbox-multiple.php';

		// make sure we can insert our desired controls where we want them {BR 2018-02-08}
		// this is heavy-handed, but WC core doesn't add priorities for us, shikata ga nai ¯\_(ツ)_/¯
		if ( $catalog_columns_control = $wp_customize->get_control( 'woocommerce_catalog_columns' ) ) {
			$catalog_columns_control->priority = 15;
		}

		if ( $catalog_rows_control = $wp_customize->get_control( 'woocommerce_catalog_rows' ) ) {
			$catalog_rows_control->priority = 15;
		}

		$wp_customize->add_setting(
			'wc_rename_default_sorting',
			[
				'default'           => '',
				'type'              => 'option',
				'capability'        => 'manage_woocommerce',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control(
			'wc_rename_default_sorting',
			[
				'label'       => __( 'New Default Sorting Label', 'woocommerce-extra-product-sorting-options' ),
				'description' => __( 'If desired, enter a new name for the default sorting option, e.g., &quot;Our Sorting&quot;', 'woocommerce-extra-product-sorting-options' ),
				'section'     => 'woocommerce_product_catalog',
				'settings'    => 'wc_rename_default_sorting',
				'type'        => 'text',
				'priority'    => 11,
			]
		);

		$wp_customize->add_setting(
			'wc_extra_product_sorting_options',
			[
				'default'           => [],
				'capability'        => 'manage_woocommerce',
				'sanitize_callback' => [ $this, 'sanitize_option_list' ],
			]
		);

		$wp_customize->add_control(
			new WC_ESO_Customize_Checkbox_Multiple(
				$wp_customize,
				'wc_extra_product_sorting_options',
				[
					'label'       => __( 'Add Product Sorting:', 'woocommerce-extra-product-sorting-options' ),
					'description' => sprintf(
						/* translators: Placeholders: %1$s - <a>, %2$s - </a> */
						__( 'Select sorting options to add to your shop. %1$ssee documentation%2$s for more details.', 'woocommerce-extra-product-sorting-options' ),
						'<a href="http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/faq/" target="_blank">', '</a>'
					),
					'type'        => 'checkbox-multiple',
					'section'     => 'woocommerce_product_catalog',
					'priority'    => 11,
					'choices'     => $this->get_extra_sorting_setting_options(),
				]
			)
		);

		$wp_customize->add_setting(
			'wc_remove_product_sorting',
			[
				'default'           => [],
				'capability'        => 'manage_woocommerce',
				'sanitize_callback' => [ $this, 'sanitize_option_list' ],
			]
		);

		$wp_customize->add_control(
			new WC_ESO_Customize_Checkbox_Multiple(
				$wp_customize,
				'wc_remove_product_sorting',
				[
					'label'       => __( 'Remove Product Sorting:', 'woocommerce-extra-product-sorting-options' ),
					'description' => sprintf(
						/* translators: Placeholders: %1$s - <a>, %2$s - </a> */
						__( 'Select default sorting options to remove from your shop. %1$ssee documentation%2$s for more details.', 'woocommerce-extra-product-sorting-options' ),
						'<a href="http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/faq/" target="_blank">', '</a>'
					),
					'type'        => 'checkbox-multiple',
					'section'     => 'woocommerce_product_catalog',
					'priority'    => 11,
					// ensures the options added by the other setting are not listed in this control
					'choices'     => array_diff_key( $this->get_core_sorting_setting_options( false ), $this->get_extra_sorting_setting_options() ),
				]
			)
		);
	}


	/**
	 * Sanitize the default sorting callback.
	 *
	 * @internal
	 *
	 * @since 2.7.0
	 *
	 * @param string|string[] $values the option value
	 * @return string[]
	 */
	public function sanitize_option_list( $values ) {

		$multi_values = ! is_array( $values ) ? explode( ',', $values ) : $values;

		return ! empty( $multi_values ) ? array_map( 'sanitize_text_field', $multi_values ) : [];
	}


	/**
	 * Gets sorting options as settings options.
	 *
	 * @since 2.7.0
	 *
	 * @return array settings options
	 */
	private function get_extra_sorting_setting_options() {

		return [
			'alphabetical'  => __( 'Name: A to Z',    'woocommerce-extra-product-sorting-options' ),
			'reverse_alpha' => __( 'Name: Z to A',    'woocommerce-extra-product-sorting-options' ),
			'by_stock'      => __( 'Available Stock', 'woocommerce-extra-product-sorting-options' ),
			'review_count'  => __( 'Review Count',    'woocommerce-extra-product-sorting-options' ),
			'on_sale_first' => __( 'On-sale First',   'woocommerce-extra-product-sorting-options' ),
		];
	}


	/**
	 * Gets WooCommerce default sorting options as settings options.
	 *
	 * WooCommerce doesn't store these into an option, and rather hardcodes them wrapped in a filter.
	 *
	 * @since 2.9.0
	 *
	 * @param bool $filter_output whether to filter the output through the WooCommerce core filter (default true)
	 * @return array
	 */
	private function get_core_sorting_setting_options( $filter_output = true ) {

		// WooCommerce textdomain used intentionally
		$options = [
			'menu_order' => __( 'Default sorting', 'woocommerce' ),
			'popularity' => __( 'Sort by popularity', 'woocommerce' ),
			'rating'     => __( 'Sort by average rating', 'woocommerce' ),
			'date'       => __( 'Sort by latest', 'woocommerce' ),
			'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
			'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
		];

		/*  WooCommerce core filter documented in wc-template-functions.php */
		return ! $filter_output ? $options : (array) apply_filters( 'woocommerce_catalog_orderby', $options );
	}


	/**
	 * Gets the number of available sorting options.
	 *
	 * @since 2.9.0
	 *
	 * @return int
	 */
	private function get_available_sorting_options_count() {

		return count( $this->get_available_sorting_options() );
	}


	/**
	 * Gets the available sorting options.
	 *
	 * @since 2.9.0
	 *
	 * @return array
	 */
	private function get_available_sorting_options() {

		// sums up all the default and extra sorting options, minus the default ones removed, if any
		return array_unique( array_filter( array_diff(
			array_merge(
				array_keys( $this->get_core_sorting_setting_options() ),
				(array) get_theme_mod( 'wc_extra_product_sorting_options', [] )
			),
			(array) get_theme_mod( 'wc_remove_product_sorting', [] )
		) ) );
	}


	/**
	 * Modifies WooCommerce sorting settings.
	 *
	 * - Changes "Default Sorting" to the custom name and adds new sorting options
	 * - Adds any extra sorting options
	 * - Removes any default sorting options
	 *
	 * Effective in both admin and frontend options.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 *
	 * @param array $order_by associative array of sorting option keys and names
	 * @return array
	 */
	public function modify_sorting_settings( $order_by ) {

		// maybe rename the "default sorting" label
		$new_default_name = get_option( 'wc_rename_default_sorting', '' );

		if ( ! empty( $new_default_name ) && ( $default_option = get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) ) ) {
			$order_by[ $default_option ] = $new_default_name;
		}

		// add any extra sorting options
		$new_sorting_options = get_theme_mod( 'wc_extra_product_sorting_options', [] );

		foreach( $new_sorting_options as $option ) {

			switch ( $option ) {
				case 'alphabetical':
					$order_by['alphabetical']  = __( 'Sort by name: A to Z', 'woocommerce-extra-product-sorting-options' );
				break;
				case 'reverse_alpha':
					$order_by['reverse_alpha'] = __( 'Sort by name: Z to A', 'woocommerce-extra-product-sorting-options' );
				break;
				case 'by_stock':
					$order_by['by_stock']      = __( 'Sort by availability', 'woocommerce-extra-product-sorting-options' );
				break;
				case 'review_count':
					$order_by['review_count']  = __( 'Sort by review count', 'woocommerce-extra-product-sorting-options' );
				break;
				case 'on_sale_first':
					$order_by['on_sale_first'] = __( 'Show sale items first', 'woocommerce-extra-product-sorting-options' );
				break;
			}
		}

		// remove any default sorting options
		foreach ( (array) get_theme_mod( 'wc_remove_product_sorting', [] ) as $removed_option ) {
			unset( $order_by[ $removed_option ] );
		}

		return $order_by;
	}


	/**
	 * Adds sorting option to WooCommerce sorting arguments.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 *
	 * @param array $sort_args the sorting arguments and query to use for it
	 * @return array updated sorting arguments
	*/
	public function add_new_shop_ordering_args( $sort_args ) {

		// If we have the orderby via URL, let's pass it in.
		// This means we're on a shop / archive, so if we don't have it, use the default.
		if ( isset( $_GET['orderby'] ) ) {
			$orderby_value = wc_clean( $_GET['orderby'] );
		} else {
			/* WooCommerce core filter defined in wc-template-functions.php */
			$orderby_value = (string) apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
		}

		// Since a shortcode can be used on a non-WC page, we won't have $_GET['orderby'].
		// Grab it from the passed in sorting args instead for non-WC pages.
		// Don't use this on WC archives so we don't break the default option.
		if ( isset( $sort_args['orderby'] ) && ! is_post_type_archive( 'product' ) && ! is_shop() ) {
			$orderby_value = $sort_args['orderby'];
		}

		/**
		 * Filters the extra sorting option fallback.
		 *
		 * @since 1.0.0
		 *
		 * @param string $fallback defaults to 'title'
		 * @param string $orderby_value
		 */
		$fallback = (string) apply_filters( 'wc_extra_sorting_options_fallback', 'title', $orderby_value );

		/**
		 * Filters the extra sorting option order.
		 *
		 * @since 1.0.0
		 *
		 * @param string $fallback_order defaults to 'ASC' (ascending)
		 * @param string $orderby_value
		 */
		$fallback_order = (string) apply_filters( 'wc_extra_sorting_options_fallback_order', 'ASC', $orderby_value );

		switch( $orderby_value ) {

			case 'alphabetical':

				$sort_args['orderby'] = 'title';
				$sort_args['order']   = 'asc';

			break;

			case 'reverse_alpha':

				$sort_args['orderby']  = 'title';
				$sort_args['order']    = 'desc';
				$sort_args['meta_key'] = '';

			break;

			case 'by_stock':

				$sort_args['orderby']  = [ 'meta_value_num' => 'DESC', $fallback => $fallback_order ];
				$sort_args['meta_key'] = '_stock';

			break;

			case 'review_count':

				$sort_args['orderby']  = [ 'meta_value_num' => 'DESC', $fallback => $fallback_order ];
				$sort_args['meta_key'] = '_wc_review_count';

			break;

			case 'on_sale_first':

				$sort_args['orderby']  = [ 'meta_value_num' => 'DESC', $fallback => $fallback_order ];
				$sort_args['meta_key'] = '_sale_price';

			break;
		}

		return $sort_args;
	}


	/**
	 * Changes the default sorting if there is only one sorting option available.
	 *
	 * @since 2.9.0
	 *
	 * @internal
	 *
	 * @param string $default_orderby
	 * @return string
	 */
	public function maybe_update_default_catalog_orderby( $default_orderby ) {

		if ( 1 === $this->get_available_sorting_options_count() ) {
			$default_orderby = (string) current($this->get_available_sorting_options());
		}

		return $default_orderby;
	}


	/**
	 * Unhooks the sorting dropdown if all options have been removed.
	 *
	 * @internal
	 *
	 * @since 2.9.0
	 */
	public function maybe_remove_catalog_orderby() {

		/**
		 * Filters whether the sorting dropdown should be unhooked from the shop page when there are no core sorting options.
		 *
		 * Inherited from WooCommerce Remove Product Sorting Options legacy plugin.
		 *
		 * @since 2.9.0
		 *
		 * @param bool $remove true if the dropdown should be removed
		 */
		if ( (bool) apply_filters( 'wc_remove_sorting_options_hide_dropdown', $this->get_available_sorting_options_count() <= 1 ) ) {

			// WooCommerce core output
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

			// Storefront theme
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10 );
			remove_action( 'woocommerce_after_shop_loop',  'woocommerce_catalog_ordering', 10 );
		}
	}


	/**
	 * Gets the plugin instance (singleton pattern).
	 *
	 * @see wc_extra_sorting_options()
	 *
	 * @since 2.2.2
	 *
	 * @return \WC_Extra_Sorting_Options
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 2.4.0
	 */
	public function __clone() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot clone instances of %s.', 'woocommerce-extra-product-sorting-options' ), 'Extra Product Sorting Options for WooCommerce' ), '2.4.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 2.4.0
	 */
	public function __wakeup() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot unserialize instances of %s.', 'woocommerce-extra-product-sorting-options' ), 'Extra Product Sorting Options for WooCommerce' ), '2.4.0' );
	}


	/**
	 * Adds plugin page links.
	 *
	 * @internal
	 *
	 * @since 2.2.2
	 *
	 * @param array $links all plugin links
	 * @return array $links all plugin links + our custom links (i.e., "Settings")
	 */
	public function add_plugin_links( $links ) {

		$configure_url = admin_url( 'customize.php?url=' . wc_get_page_permalink( 'shop' ) . '&autofocus[section]=woocommerce_product_catalog' );
		$plugin_links  = [
			'<a href="' . esc_url( $configure_url ) . '">' . __( 'Configure', 'woocommerce-extra-product-sorting-options' ) . '</a>',
			'<a href="https://wordpress.org/plugins/woocommerce-extra-product-sorting-options/faq/" target="_blank">'. __( 'FAQ', 'woocommerce-extra-product-sorting-options' ) . '</a>',
			'<a href="https://wordpress.org/support/plugin/woocommerce-extra-product-sorting-options" target="_blank">' . __( 'Support', 'woocommerce-extra-product-sorting-options' ) . '</a>',
		];

		return array_merge( $plugin_links, $links );
	}


	/**
	 * Loads translations.
	 *
	 * @internal
	 *
	 * @since 2.1.1
	 */
	public function load_translation() {

		load_plugin_textdomain( 'woocommerce-extra-product-sorting-options', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}


	/**
	 * Determines whether a plugin is active.
	 *
	 * @since 2.7.2
	 *
	 * @param string $plugin_name plugin name, as the plugin-filename.php
	 * @return bool
	 */
	public static function is_plugin_active( $plugin_name ) {

		$active_plugins = (array) get_option( 'active_plugins', [] );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', [] ) ) );
		}

		$plugin_filenames = [];

		foreach ( $active_plugins as $plugin ) {

			if ( false !== strpos( $plugin, '/' ) ) {

				// normal plugin name (plugin-dir/plugin-filename.php)
				list( , $filename ) = explode( '/', $plugin );

			} else {

				// no directory, just plugin file
				$filename = $plugin;
			}

			$plugin_filenames[] = $filename;
		}

		return in_array( $plugin_name, $plugin_filenames, false );
	}


	/**
	 * Renders a notice when WooCommerce version is outdated.
	 *
	 * @internal
	 *
	 * @since 2.4.0
	 */
	public static function render_outdated_wc_version_notice() {

		?>
		<div class="error">
			<p>
				<?php printf(
					/* translators: Placeholders: %1$s <strong>, %2$s - </strong>, %3$s - version number, %4$s - opening HTML <a> link tag, %5$s - closing HTML </a> link tag, %6$s - opening HTML <a> link tag, %7$s - closing HTML </a> link tag */
					esc_html__( '%1$sExtra Product Sorting Options for WooCommerce is inactive.%2$s This plugin requires WooCommerce %3$s or newer. Please %4$supdate WooCommerce%5$s or %6$srun the WooCommerce database upgrade%7$s.', 'woocommerce-extra-product-sorting-options' ),
					'<strong>', '</strong>',
					self::MIN_WOOCOMMERCE_VERSION,
					'<a href="' . admin_url( 'plugins.php' ) . '">', '</a>',
					'<a href="' . admin_url( 'plugins.php?do_update_woocommerce=true' ) . '">', '</a>'
				); ?>
			</p>
		</div>
		<?php
	}


	/**
	 * Checks if WooCommerce is greater than a specific version.
	 *
	 * @since 2.7.0
	 *
	 * @param string $version version number
	 * @return bool
	 */
	public static function is_wc_gte( $version ) {

		return defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, $version, '>=' );
	}


	/**
	 * Checks if WooCommerce is less than than a specific version.
	 *
	 * @since 2.7.0
	 *
	 * @param string $version version number
	 * @return bool
	 */
	public static function is_wc_lt( $version ) {

		return defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, $version, '<' );
	}


	/**
	 * Gets the plugin URL.
	 *
	 * @since 2.7.0
	 *
	 * @return string the plugin URL
	 */
	public function get_plugin_url() {

		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}


	/**
	 * Performs lifecycle routines.
	 *
	 * Runs every time, since the activation hook is not executed when updating a plugin.
	 *
	 * @since 2.0.0
	 */
	private function install() {

		// get current version to check for upgrade
		$installed_version = get_option( 'wc_extra_sorting_options_version' );

		// force upgrade to 2.0.0, prior versions did not have version option set
		if ( ! $installed_version && ! get_option( 'wc_extra_product_sorting_options' ) ) {
			$this->upgrade( '1.2.0' );
		}

		// upgrade if installed version lower than plugin version
		if ( -1 === version_compare( $installed_version, self::VERSION ) ) {
			$this->upgrade( $installed_version );
		}
	}


	/**
	 * Performs any version-related changes.
	 *
	 * @since 2.0.0
	 *
	 * @param int $installed_version the currently installed version of the plugin
	 */
	private function upgrade( $installed_version ) {

		// upgrade from 1.2.0 to 2.0.0
		if ( '1.2.0' === $installed_version ) {

			$new_options = [];
			$old_options = [
				'wc_alphabetical_product_sorting'         => 'alphabetical',
				'wc_reverse_alphabetical_product_sorting' => 'reverse_alpha',
				'wc_on_sale_product_sorting'              => 'on_sale_first',
				'wc_random_product_sorting'               => 'randomize',
			];

			foreach ( $old_options as $old_key => $new_key ) {
				if ( 'yes' === get_option( $old_key ) ) {
					$new_options[] = $new_key;
				}
			}

			update_option( 'wc_extra_product_sorting_options', $new_options );
		}

		// remove random sorting if it was set
		if ( version_compare( $installed_version, '2.5.0', '<' ) ) {

			$settings = get_option( 'wc_extra_product_sorting_options' );

			if ( in_array( 'randomize', $settings, true ) ) {

				unset( $settings[ array_search('randomize', $settings, true ) ] );

				update_option( 'wc_extra_product_sorting_options', $settings );

				add_action( 'admin_notices', [ $this, 'render_2_5_upgrade_notice' ] );
			}
		}

		if ( version_compare( $installed_version, '2.6.0', '<' ) ) {

			$settings = get_option( 'wc_extra_product_sorting_options' );

			// let people know the settings will change / have changed in WC 3.0+
			if ( in_array( 'featured_first', $settings, true ) ) {
				add_action( 'admin_notices', [ $this, 'render_wc_30_update_notice' ] );
			}
		}

		// copy enabled sorting settings to theme mods for WC 3.3+ usage
		if ( version_compare( $installed_version, '2.6.2', '<' ) ) {
			set_theme_mod( 'wc_extra_product_sorting_options', get_option( 'wc_extra_product_sorting_options', [] ) );
		}

		// add the setting option for removing default sorting options unless migrated from legacy free plugin
		if ( version_compare( $installed_version, '2.9.0', '<' ) && ! get_theme_mod( 'wc_remove_product_sorting' ) ) {
			set_theme_mod( 'wc_remove_product_sorting', [] );
		}

		// removes Remove Product Sorting legacy plugin, if found
		add_action( 'admin_init', [ $this, 'remove_legacy_plugin' ] );

		// update the installed version option
		update_option( 'wc_extra_sorting_options_version', self::VERSION );
	}


	/**
	 * Removes WooCommerce Product Sorting.
	 *
	 * The legacy plugin has been merged into Extra Sorting Option so it's no longer necessary.
	 *
	 * @internal
	 *
	 * @since 2.9.0
	 */
	public function remove_legacy_plugin() {

		$legacy_plugin = 'woocommerce-remove-product-sorting/woocommerce-remove-product-sorting.php';

		if ( in_array( $legacy_plugin, (array) get_option( 'active_plugins', [] ), true ) ) {
			deactivate_plugins( $legacy_plugin, true );
		}

		delete_plugins( [ $legacy_plugin ] );
	}


	/**
	 * Renders a notice when upgrading to v2.5 if random sorting was enabled, as this was removed from the plugin.
	 *
	 * @internal
	 *
	 * @since 2.5.0
	 */
	public function render_2_5_upgrade_notice() {

		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php printf(
					/* translators: Placeholders: %1$s - <strong>, %2$s - <strong>, %3$s - <a>, %4$s - </a> */
					esc_html__( '%1$sExtra Product Sorting Options for WooCommerce settings have changed.%2$s Random sorting is now disabled. If you need to re-add this option, please %3$sview our plugin notes%4$s.', 'woocommerce-extra-product-sorting-options' ),
					'<strong>', '</strong>',
					'<a href="http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/other_notes/" target="_blank">', '&nbsp;&raquo;</a>'
				); ?>
			</p>
		</div>
		<?php
	}


	/**
	 * Render a notice for stores when upgrading to v2.6 if they use featured-first sorting, as this is not available when upgrading to WooCommerce 3.0+.
	 *
	 * @internal
	 *
	 * @since 2.6.0
	 */
	public function render_wc_30_update_notice() {

		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php printf(
					/* translators: Placeholders: %1$s - <strong>, %2$s - <strong>, %3$s - <a>, %4$s - </a> */
					esc_html__( '%1$sExtra Product Sorting Options for WooCommerce settings have changed.%2$s Featured sorting is no longer possible with WooCommerce 3.0+ as this product data has changed. Please %3$sview our plugin notes%4$s for more details.', 'woocommerce-extra-product-sorting-options' ),
					'<strong>',
					'</strong>',
					'<a href="http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/other_notes/" target="_blank">',
					'&nbsp;&raquo;</a>'
				); ?>
			</p>
		</div>
		<?php
	}


}


/**
 * Gets the singleton instance of Extra Sorting Options for WooCommerce.
 *
 * @since 2.2.2
 *
 * @return \WC_Extra_Sorting_Options
 */
function wc_extra_sorting_options() {
	return WC_Extra_Sorting_Options::instance();
}
