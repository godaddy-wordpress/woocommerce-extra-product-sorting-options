<?php
/**
 * Plugin Name: WooCommerce Extra Product Sorting Options
 * Plugin URI: http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/
 * Description: Rename default sorting and optionally extra product sorting options.
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com/
 * Version: 2.2.3
 * Text Domain: wc-extra-sorting-options
 *
 * Copyright: (c) 2014-2015 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Extra-Product-Sorting-Options
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2015, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Description
 *
 * Rename default sorting option - helpful if custom sorting is used.
 * Adds sorting by name, on sale, featured, availability, and random to shop pages.
 *
 */


class WC_Extra_Sorting_Options {
	
	
	const VERSION = '2.2.3';
	
	
	/** @var WC_Extra_Sorting_Options single instance of this plugin */
	protected static $instance;
	
	
	public function __construct() {
	
		// modify product sorting settings
		add_filter( 'woocommerce_catalog_orderby', array( $this, 'modify_sorting_settings' ) );
		
		// add new sorting options to orderby dropdown
		add_filter( 'woocommerce_default_catalog_orderby_options', array( $this, 'modify_sorting_settings' ) );
		
		// add new product sorting arguments
		add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'add_new_shop_ordering_args' ) );
		
		// load translations
		add_action( 'init', array( $this, 'load_translation' ) );
		
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		
			// add settings
			add_filter( 'woocommerce_product_settings', array( $this, 'add_settings' ) );
			
			// add plugin links
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_links' ) );
			
			// run every time
			$this->install();
		}
	}
	
	
	/**
	 * Load Translations
	 *
	 * @since 2.1.1
	 */
	public function load_translation() {
		// localization
		load_plugin_textdomain( 'wc-extra-sorting-options', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}
	
	
	/** Helper methods ******************************************************/
	
	
	/**
	 * Main Extra Sorting Instance, ensures only one instance is/can be loaded
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
	 * Adds plugin page links
	 * 
	 * @since 2.2.2
	 * @param array $links all plugin links
	 * @return array $links all plugin links + our custom links (i.e., "Settings")
	 */
	public function add_plugin_links( $links ) {
	
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=display' ) . '">' . __( 'Configure', 'wc-extra-sorting-options' ) . '</a>',
			'<a href="https://wordpress.org/support/plugin/woocommerce-extra-product-sorting-options" target="_blank">' . __( 'Support', 'wc-extra-sorting-options' ) . '</a>',
		);
		
		return array_merge( $plugin_links, $links );
	}
	
	
	/**
	 * Add Settings to WooCommerce Settings > Products page after "Default Product Sorting" setting
	 *
	 * @since 1.0.0
	 */
	public function add_settings( $settings ) {

		$updated_settings = array();

		foreach ( $settings as $setting ) {

			$updated_settings[] = $setting;

			if ( isset( $setting['id'] ) && 'woocommerce_default_catalog_orderby' === $setting['id'] ) {

				$new_settings = array(
					array(
						'title'    => __( 'New Default Sorting Label', 'wc-extra-sorting-options' ),
						'id'       => 'wc_rename_default_sorting',
						'type'     => 'text',
						'default'  => '',
						'desc_tip' => __( 'If desired, enter a new name for the default sorting option, e.g., &quot;Our Sorting&quot;', 'wc-extra-sorting-options' ),
					),
					array(
						'name'     => __( 'Add Product Sorting:', 'wc-extra-sorting-options' ),
						'desc_tip' => __( 'Select sorting options to add to your shop. "Available Stock" sorts products with the most stock first.', 'wc-extra-sorting-options' ),
						'desc'     => '<br/>' . sprintf( __( '"On-sale First" shows <strong>simple</strong> products on sale first; <a href="%s" target="_blank">see documentation</a> for more details.', 'wc-extra-sorting-options' ), 'https://wordpress.org/plugins/woocommerce-extra-product-sorting-options/faq/' ),
						'id'       => 'wc_extra_product_sorting_options',
						'type'     => 'multiselect',
						'class'    => 'chosen_select',
						'options'  => array(
							'alphabetical'   => __( 'Name: A to Z', 'wc-extra-sorting-options' ),
							'reverse_alpha'  => __( 'Name: Z to A', 'wc-extra-sorting-options' ),
							'by_stock'   	 => __( 'Available Stock', 'wc-extra-sorting-options' ),
							'featured_first' => __( 'Featured First', 'wc-extra-sorting-options' ),
							'on_sale_first'  => __( 'On-sale First', 'wc-extra-sorting-options' ),
							'randomize'      => __( 'Random', 'wc-extra-sorting-options' ),
						),
						'default'  => '',
					),
				);

				$updated_settings = array_merge( $updated_settings, $new_settings );
		
			}
		}
		
		return $updated_settings;
	}
	
	
	/** Plugin methods ******************************************************/
	
	
	/**
	 * Change "Default Sorting" to custom name and add new sorting options; added to admin + frontend dropdown
	 *
	 * @since 2.0.0
	 */
	public function modify_sorting_settings( $sortby ) {
	
		$new_default_name = get_option( 'wc_rename_default_sorting' );

		if ( $new_default_name ) {
			$sortby = str_replace( "Default sorting", $new_default_name, $sortby );
		}
		
		$new_sorting_options = get_option('wc_extra_product_sorting_options', array() );
	
		foreach( $new_sorting_options as $option ) {
	
			switch( $option ) {
		
				case 'alphabetical':
					$sortby['alphabetical'] = __( 'Sort by name: A to Z', 'wc-extra-sorting-options' );
					break;
				
				case 'reverse_alpha':
					$sortby['reverse_alpha'] = __( 'Sort by name: Z to A', 'wc-extra-sorting-options' );
					break;
				
				case 'by_stock':
					$sortby['by_stock'] = __( 'Sort by availability', 'wc-extra-sorting-options' );
					break;
					
				case 'on_sale_first':
					$sortby['on_sale_first'] = __( 'Show sale items first', 'wc-extra-sorting-options' );
					break;
			
				case 'featured_first':
					$sortby['featured_first'] = __( 'Show featured items first', 'wc-extra-sorting-options' );
					break;
				
				case 'randomize':
					$sortby['rand'] = __( 'Sort by: random order', 'wc-extra-sorting-options' );
					break;
				 
			}
		
		}
	
		return $sortby;
	}


	/**
	 * Add sorting option to WC sorting arguments
	 *
	 * @since 2.0.0
	*/
	public function add_new_shop_ordering_args( $sort_args ) {
		
		// If we have the orderby via URL, let's pass it in
		// This means we're on a shop / archive, so if we don't have it, use the default
		if ( isset( $_GET['orderby'] ) ) {
			$orderby_value = wc_clean( $_GET['orderby'] );
		} else {
			$orderby_value = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
		}
		
		// Since a shortcode can be used on a non-WC page, we won't have $_GET['orderby']
		// Grab it from the passed in sorting args instead for non-WC pages
		// Don't use this on WC pages since it breaks the default
		if ( ! is_woocommerce() && isset( $sort_args['orderby'] ) ) {
			$orderby_value = $sort_args['orderby'];
		}

		$fallback = apply_filters( 'wc_extra_sorting_options_fallback', 'title', $orderby_value );
		$fallback_order = apply_filters( 'wc_extra_sorting_options_fallback_order', 'ASC', $orderby_value );
		
		switch( $orderby_value ) {
	
			case 'alphabetical':
				$sort_args['orderby'] = 'title';
				$sort_args['order'] = 'asc';
				break;
		
			case 'reverse_alpha':
				$sort_args['orderby']  = 'title';
				$sort_args['order']    = 'desc';
				$sort_args['meta_key'] = '';
				break;
				
			case 'by_stock':
				$sort_args['orderby'] = array( 'meta_value_num' => 'DESC', $fallback => $fallback_order );
				$sort_args['meta_key'] = '_stock';
				break;
				
								
			case 'on_sale_first':
				$sort_args['orderby'] = array( 'meta_value_num' => 'DESC', $fallback => $fallback_order );
				$sort_args['meta_key'] = '_sale_price';
				break;
				
			case 'featured_first':
				$sort_args['orderby'] = array( 'meta_value' => 'DESC', $fallback => $fallback_order );
				$sort_args['meta_key'] = '_featured';
				break;
		
		}
	
		return $sort_args;
	}
	
	
	/** Lifecycle methods ******************************************************/
	
	
	/**
	 * Run every time.  Used since the activation hook is not executed when updating a plugin
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
				'wc_alphabetical_product_sorting' => 'alphabetical',
				'wc_reverse_alphabetical_product_sorting' => 'reverse_alpha',
				'wc_on_sale_product_sorting' => 'on_sale_first',
				'wc_random_product_sorting' => 'randomize',
			);
			
			$new_options = array();
			
			foreach ( $old_options as $old_key => $new_key ) {
			
				if ( 'yes' === get_option( $old_key ) ) {
				
					$new_options[] = $new_key;
				}
			}
			
			update_option( 'wc_extra_product_sorting_options', $new_options );
		}
		
		// update the installed version option
		update_option( 'wc_extra_sorting_options_version', self::VERSION );
	}

} // end \WC_Extra_Sorting_Options class


/**
 * Returns the One True Instance of WC Extra Sorting
 *
 * @since 2.2.2
 * @return WC_Extra_Sorting_Options
 */
function wc_extra_sorting_options() {
    return WC_Extra_Sorting_Options::instance();
}


/**
 * The WC_Extra_Sorting_Options global object, exists only for backwards compat
 *
 * @deprecated 2.2.2
 * @name $wc_extra_sorting_options
 * @global WC_Extra_Sorting_Options $GLOBALS['wc_extra_sorting_options']
 */
$GLOBALS['wc_extra_sorting_options'] = wc_extra_sorting_options();