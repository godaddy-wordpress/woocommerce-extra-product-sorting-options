<?php
/**
 * Plugin Name: WooCommerce Extra Product Sorting Options
 * Plugin URI: http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/
 * Description: Rename default sorting and optionally extra product sorting options.
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com/
 * Version: 2.6.1
 * Text Domain: woocommerce-extra-product-sorting-options
 *
 * Copyright: (c) 2014-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Extra-Product-Sorting-Options
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * # Plugin Description
 *
 * Rename default sorting option - helpful if custom sorting is used.
 * Adds sorting by name, on sale, featured, availability, and random to shop pages.
 */

// Check if WooCommerce is active
if ( ! WC_Extra_Sorting_Options::is_woocommerce_active() ) {
	return;
}

// WC version check
if ( version_compare( get_option( 'woocommerce_db_version' ), '2.4.0', '<' ) ) {
	add_action( 'admin_notices', array( 'WC_Extra_Sorting_Options', 'render_outdated_wc_version_notice' ) );
	return;
}

// Make sure we're loaded after WC and fire it up!
add_action( 'plugins_loaded', 'wc_extra_sorting_options' );

/**
 * Class \WC_Extra_Sorting_Options
 * Sets up the main plugin class.
 *
 * @since 2.0.0
 */

class WC_Extra_Sorting_Options {


	const VERSION = '2.6.1';

	/** @var WC_Extra_Sorting_Options single instance of this plugin */
	protected static $instance;


	/**
	 * WC_Extra_Sorting_Options constructor. Initializes the plugin.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		// modify product sorting settings
		add_filter( 'woocommerce_catalog_orderby', array( $this, 'modify_sorting_settings' ) );

		// add new sorting options to orderby dropdown
		add_filter( 'woocommerce_default_catalog_orderby_options', array( $this, 'modify_sorting_settings' ) );

		// add new product sorting arguments
		add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'add_new_shop_ordering_args' ) );

		// load translations
		add_action( 'init', array( $this, 'load_translation' ) );

		if ( is_admin() && ! is_ajax() ) {

			// add settings
			add_filter( 'woocommerce_product_settings', array( $this, 'add_settings' ) );

			// add plugin links
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_links' ) );

			// run every time
			$this->install();
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Extra Sorting Instance, ensures only one instance is/can be loaded.
	 *
	 * @since 2.2.2
	 * @see wc_extra_sorting_options()
	 * @return WC_Extra_Sorting_Options
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
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
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot clone instances of %s.', 'woocommerce-extra-product-sorting-options' ), 'WooCommerce Extra Product Sorting Options' ), '2.4.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 2.4.0
	 */
	public function __wakeup() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot unserialize instances of %s.', 'woocommerce-extra-product-sorting-options' ), 'WooCommerce Extra Product Sorting Options' ), '2.4.0' );
	}


	/**
	 * Adds plugin page links.
	 *
	 * @since 2.2.2
	 * @param array $links all plugin links
	 * @return array $links all plugin links + our custom links (i.e., "Settings")
	 */
	public function add_plugin_links( $links ) {

		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=display' ) . '">' . __( 'Configure', 'woocommerce-extra-product-sorting-options' ) . '</a>',
			'<a href="https://wordpress.org/plugins/woocommerce-extra-product-sorting-options/faq/">'. __( 'FAQ', 'woocommerce-extra-product-sorting-options' ) . '</a>',
			'<a href="https://wordpress.org/support/plugin/woocommerce-extra-product-sorting-options" target="_blank">' . __( 'Support', 'woocommerce-extra-product-sorting-options' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}


	/**
	 * Load Translations
	 *
	 * @since 2.1.1
	 */
	public function load_translation() {
		// localization
		load_plugin_textdomain( 'woocommerce-extra-product-sorting-options', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}


	/**
	 * Checks if WooCommerce is active.
	 *
	 * @since 2.4.0
	 * @return bool true if WooCommerce is active, false otherwise
	 */
	public static function is_woocommerce_active() {

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}


	/**
	 * Renders a notice when WooCommerce version is outdated.
	 *
	 * @since 2.4.0
	 */
	public static function render_outdated_wc_version_notice() {

		$message = sprintf(
			/* translators: Placeholders: %1$s <strong>, %2$s - </strong>, %3$s and %5$s - <a> tags, %4$s - </a> */
			esc_html__( '%1$sWooCommerce Extra Product Sorting Options is inactive.%2$s This plugin requires WooCommerce 2.4 or newer. Please %3$supdate WooCommerce%4$s or %5$srun the WooCommerce database upgrade%4$s.', 'woocommerce-extra-product-sorting-options' ),
			'<strong>',
			'</strong>',
			'<a href="' . admin_url( 'plugins.php' ) . '">',
			'</a>',
			'<a href="' . admin_url( 'plugins.php?do_update_woocommerce=true' ) . '">'
		);

		printf( '<div class="error"><p>%s</p></div>', $message );
	}


	/**
	 * Checks if WooCommerce is greater than v3.0.
	 *
	 * @since 2.6.0
	 * @return bool true if > v3.0
	 */
	public static function is_wc_gte_30() {
		return defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, '3.0', '>=' );
	}


	/** Plugin methods ******************************************************/


	/**
	 * Add Settings to WooCommerce Settings > Products page after "Default Product Sorting" setting.
	 *
	 * @since 1.0.0
	 * @param array $settings the current product settings
	 * @return array updated settings
	 */
	public function add_settings( $settings ) {

		$updated_settings = array();
		$settings_options = array(
			'alphabetical'  => __( 'Name: A to Z',    'woocommerce-extra-product-sorting-options' ),
			'reverse_alpha' => __( 'Name: Z to A',    'woocommerce-extra-product-sorting-options' ),
			'by_stock'      => __( 'Available Stock', 'woocommerce-extra-product-sorting-options' ),
			'review_count'  => __( 'Review Count',    'woocommerce-extra-product-sorting-options' ),
			'on_sale_first' => __( 'On-sale First',   'woocommerce-extra-product-sorting-options' ),
		);

		if ( ! WC_Extra_Sorting_Options::is_wc_gte_30() ) {
			$settings_options['featured_first'] = __( 'Featured First', 'woocommerce-extra-product-sorting-options' );
		}

		foreach ( $settings as $setting ) {

			$updated_settings[] = $setting;

			if ( isset( $setting['id'] ) && 'woocommerce_default_catalog_orderby' === $setting['id'] ) {

				$new_settings = array(

					array(
						'title'    => __( 'New Default Sorting Label', 'woocommerce-extra-product-sorting-options' ),
						'id'       => 'wc_rename_default_sorting',
						'type'     => 'text',
						'default'  => '',
						'desc_tip' => __( 'If desired, enter a new name for the default sorting option, e.g., &quot;Our Sorting&quot;', 'woocommerce-extra-product-sorting-options' ),
					),

					array(
						'name'              => __( 'Add Product Sorting:', 'woocommerce-extra-product-sorting-options' ),
						'desc_tip'          => __( 'Select sorting options to add to your shop. "Available Stock" sorts products with the most stock first.', 'woocommerce-extra-product-sorting-options' ),
						/* translators: Placeholders: %1$s - <strong>, %2$s - </strong>, %3$s - <a>, %4$s - </a> */
						'desc'              => '<br />' . sprintf( __( '"On-sale First" shows %1$ssimple%2$s products on sale first; %3$ssee documentation%4$s for more details.', 'woocommerce-extra-product-sorting-options' ),
								'<strong>',
								'</strong>',
								'<a href="http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/faq/" target="_blank">',
								'</a>'
							),
						'id'                => 'wc_extra_product_sorting_options',
						'type'              => 'multiselect',
						'class'             => 'chosen_select',
						'options'           => $settings_options,
						'default'           => '',
						'custom_attributes' => array(
							'data-placeholder' => __( 'Select sorting options to add to your shop.', 'woocommerce-extra-product-sorting-options' ),
						),
					),
				);

				$updated_settings = array_merge( $updated_settings, $new_settings );
			}
		}

		return $updated_settings;
	}


	/**
	 * Change "Default Sorting" to custom name and add new sorting options; added to admin + frontend dropdown.
	 *
	 * @since 2.0.0
	 * @param array $sortby array or sorting option keys and names
	 * @return array the updated sort by options
	 */
	public function modify_sorting_settings( $sortby ) {

		$new_default_name = get_option( 'wc_rename_default_sorting' );

		if ( $new_default_name ) {
			$sortby = str_replace( 'Default sorting', $new_default_name, $sortby );
		}

		$new_sorting_options = get_option('wc_extra_product_sorting_options', array() );

		foreach( $new_sorting_options as $option ) {

			switch ( $option ) {

				case 'alphabetical':
					$sortby['alphabetical']   = __( 'Sort by name: A to Z', 'woocommerce-extra-product-sorting-options' );
				break;

				case 'reverse_alpha':
					$sortby['reverse_alpha']  = __( 'Sort by name: Z to A', 'woocommerce-extra-product-sorting-options' );
				break;

				case 'by_stock':
					$sortby['by_stock']       = __( 'Sort by availability', 'woocommerce-extra-product-sorting-options' );
				break;

				case 'review_count':
					$sortby['review_count']   = __( 'Sort by review count', 'woocommerce-extra-product-sorting-options' );
				break;

				case 'on_sale_first':
					$sortby['on_sale_first']  = __( 'Show sale items first', 'woocommerce-extra-product-sorting-options' );
				break;

				case 'featured_first':
					if ( ! WC_Extra_Sorting_Options::is_wc_gte_30() ) {
						$sortby['featured_first'] = __( 'Show featured items first', 'woocommerce-extra-product-sorting-options' );
					}
				break;

			}
		}

		return $sortby;
	}


	/**
	 * Add sorting option to WC sorting arguments.
	 *
	 * @since 2.0.0
	 * @param array $sort_args the sorting arguments and query to use for it
	 * @return array updated sorting arguments
	*/
	public function add_new_shop_ordering_args( $sort_args ) {

		// If we have the orderby via URL, let's pass it in.
		// This means we're on a shop / archive, so if we don't have it, use the default.
		if ( isset( $_GET['orderby'] ) ) {
			$orderby_value = wc_clean( $_GET['orderby'] );
		} else {
			$orderby_value = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
		}

		// Since a shortcode can be used on a non-WC page, we won't have $_GET['orderby'] --
		// grab it from the passed in sorting args instead for non-WC pages.
		// Don't use this on WC pages since it breaks the default option!
		if ( ! is_woocommerce() && isset( $sort_args['orderby'] ) ) {
			$orderby_value = $sort_args['orderby'];
		}

		$fallback       = apply_filters( 'wc_extra_sorting_options_fallback', 'title', $orderby_value );
		$fallback_order = apply_filters( 'wc_extra_sorting_options_fallback_order', 'ASC', $orderby_value );

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

				$sort_args['orderby']  = array( 'meta_value_num' => 'DESC', $fallback => $fallback_order );
				$sort_args['meta_key'] = '_stock';

			break;

			case 'review_count':

				$sort_args['orderby']  = array( 'meta_value_num' => 'DESC', $fallback => $fallback_order );
				$sort_args['meta_key'] = '_wc_review_count';

			break;

			case 'on_sale_first':

				$sort_args['orderby']  = array( 'meta_value_num' => 'DESC', $fallback => $fallback_order );
				$sort_args['meta_key'] = '_sale_price';

			break;

			case 'featured_first':

				if ( ! WC_Extra_Sorting_Options::is_wc_gte_30() ) {
					$sort_args['orderby']  = array( 'meta_value' => 'DESC', $fallback => $fallback_order );
					$sort_args['meta_key'] = '_featured';
				}

			break;

		}

		return $sort_args;
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Run every time.  Used since the activation hook is not executed when updating a plugin.
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
	 * Perform any version-related changes.
	 *
	 * @since 2.0.0
	 * @param int $installed_version the currently installed version of the plugin
	 */
	private function upgrade( $installed_version ) {

		// upgrade from 1.2.0 to 2.0.0
		if ( '1.2.0' === $installed_version ) {

			$old_options = array(
				'wc_alphabetical_product_sorting'         => 'alphabetical',
				'wc_reverse_alphabetical_product_sorting' => 'reverse_alpha',
				'wc_on_sale_product_sorting'              => 'on_sale_first',
				'wc_random_product_sorting'               => 'randomize',
			);

			$new_options = array();

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

				unset( $settings[ array_search( 'randomize', $settings ) ] );
				update_option( 'wc_extra_product_sorting_options', $settings );

				add_action( 'admin_notices', array( $this, 'render_2_5_upgrade_notice' ) );
			}
		}

		if ( version_compare( $installed_version, '2.6.0', '<' ) ) {

			$settings = get_option( 'wc_extra_product_sorting_options' );

			// let people know the settings will change / have changed in WC 3.0+
			if ( in_array( 'featured_first', $settings, true ) ) {
				add_action( 'admin_notices', array( $this, 'render_wc_30_update_notice' ) );
			}
		}

		// update the installed version option
		update_option( 'wc_extra_sorting_options_version', self::VERSION );
	}


	/**
	 * Renders a notice when upgrading to v2.5 if random sorting was enabled
	 *  as this was removed from the plugin.
	 *
	 * @since 2.5.0
	 */
	public function render_2_5_upgrade_notice() {

		$message = sprintf(
			/* translators: Placeholders: %1$s - <strong>, %2$s - <strong>, %3$s - <a>, %4$s - </a> */
			esc_html__( '%1$sWooCommerce Extra Product Sorting Options settings have changed.%2$s Random sorting is now disabled. If you need to re-add this option, please %3$sview our plugin notes%4$s.', 'woocommerce-extra-product-sorting-options' ),
			'<strong>',
			'</strong>',
			'<a href="http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/other_notes/" target="_blank">',
			'&nbsp;&raquo;</a>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message );
	}


	/**
	 * Render a notice for stores when upgrading to v2.6 if they use featured-first sorting
	 *  as this is not available when upgrading to WooCommerce 3.0+.
	 *
	 * @since 2.6.0
	 */
	public function render_wc_30_update_notice() {

		if ( WC_Extra_Sorting_Options::is_wc_gte_30() ) {

			/* translators: Placeholders: %1$s - <strong>, %2$s - <strong>, %3$s - <a>, %4$s - </a> */
			$text = __( '%1$sWooCommerce Extra Product Sorting Options settings have changed.%2$s Featured sorting is no longer possible with WooCommerce 3.0+ as this product data has changed. Please %3$sview our plugin notes%4$s for more details.', 'woocommerce-extra-product-sorting-options' );

		} else {

			/* translators: Placeholders: %1$s - <strong>, %2$s - <strong>, %3$s - <a>, %4$s - </a> */
			$text = __( 'Please note: %1$sWooCommerce Extra Product Sorting Options settings%2$s will change when you upgrade to WooCommerce 3.0+. Featured sorting is not possible with WooCommerce 3.0+ as this product data will change. Please %3$sview our plugin notes%4$s for more details.', 'woocommerce-extra-product-sorting-options' );
		}

		$message = sprintf(
			esc_html( $text ),
			'<strong>',
			'</strong>',
			'<a href="http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/other_notes/" target="_blank">',
			'&nbsp;&raquo;</a>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message );
	}


}


/**
 * Returns the One True Instance of WC Extra Sorting.
 *
 * @since 2.2.2
 * @return WC_Extra_Sorting_Options
 */
function wc_extra_sorting_options() {
	return WC_Extra_Sorting_Options::instance();
}
