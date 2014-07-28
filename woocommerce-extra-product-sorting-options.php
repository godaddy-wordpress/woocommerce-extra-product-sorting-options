<?php
/**
 * Plugin Name: WooCommerce Extra Product Sorting Options
 * Plugin URI: http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/
 * Description: Rename default sorting and optionally add alphabetical and random sorting.
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com/
 * Version: 1.0.0
 * Text Domain: woocommerce-extra-product-sorting-options
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2012-2014 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Extra-Product-Sorting-Options
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2012-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Description
 *
 * Rename default sorting option - helpful if custom sorting used.
 * Add alphabetical sorting options and random sorting options to shop pages.
 *
 */

/**
 *Add Settings to WooCommerce Settings > Products page after "Default Product Sorting" setting
 *
 * @since 1.0.0
 */
function skyverge_wc_extra_sorting_options_add_settings( $settings ) {

	$updated_settings = array();

	foreach ( $settings as $setting ) {

		$updated_settings[] = $setting;

		if ( isset( $setting['id'] ) && 'woocommerce_default_catalog_orderby' === $setting['id'] ) {

			$new_settings = array(
				array(
					'title'    => __( 'New Default Sorting Label', 'woocommerce' ),
					'id'       => 'wc_rename_default_sorting',
					'type'     => 'text',
					'default'  => '',
					'desc_tip' => __( 'If desired, enter a new name for the default sorting option, e.g., &quot;Our Sorting&quot;', 'woocommerce' ),
				),
				array(
					'title'         => __( 'Add Product Sorting Options', 'woocommerce' ),
					'desc'          => __( 'Alphabetical product sorting', 'woocommerce' ),
					'id'            => 'wc_alphabetical_product_sorting',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => 'start'
				),
				array(
					'desc'          => __( 'Random product sorting', 'woocommerce' ),
					'id'            => 'wc_random_product_sorting',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => 'end'
				),
			);

			$updated_settings = array_merge( $updated_settings, $new_settings );
		}
	}
	return $updated_settings;
}
add_filter( 'woocommerce_product_settings', 'skyverge_wc_extra_sorting_options_add_settings' );


/**
 * Change "Default Sorting" to custom name on shop page and in WC Product Settings
 *
 * @since 1.0.0
*/

function skyverge_change_default_sorting_name( $catalog_orderby ) {
	$new_default_name = get_option('wc_rename_default_sorting');

	if($new_default_name == '') {
		return $catalog_orderby;
	} else {
		$catalog_orderby = str_replace("Default sorting", $new_default_name, $catalog_orderby);
		return $catalog_orderby;
	}
}
add_filter( 'woocommerce_catalog_orderby', 'skyverge_change_default_sorting_name' );
add_filter( 'woocommerce_default_catalog_orderby_options', 'skyverge_change_default_sorting_name' );


/**
 * Add Alphabetical sorting option to WC Default Product Sorting / shop pages if enabled
 *
 * @since 1.0.0
*/
function skyverge_alphabetical_woocommerce_shop_ordering( $sort_args ) {

	$alphabetical_enabled = get_option('wc_alphabetical_product_sorting');

	if($alphabetical_enabled == 'yes') {
		$orderby_value = isset( $_GET['orderby'] ) ? woocommerce_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

		if ( 'alphabetical' == $orderby_value ) {
			$sort_args['orderby'] = 'title';
			$sort_args['order'] = 'asc';
			$sort_args['meta_key'] = '';
		}

		return $sort_args;
	} else {
		return $sort_args;
	}
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'skyverge_alphabetical_woocommerce_shop_ordering' );


function skyverge_alphabetical_woocommerce_catalog_orderby( $sortby ) {
	$alphabetical_enabled = get_option('wc_alphabetical_product_sorting');

	if($alphabetical_enabled == 'yes') {
		$sortby['alphabetical'] = __( 'Sort by name: alphabetical', 'woocommerce' );
		return $sortby;
	} else {
		return $sortby;
	}
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'skyverge_alphabetical_woocommerce_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'skyverge_alphabetical_woocommerce_catalog_orderby' );


/**
 * Add random sorting option to WC Default Product Sorting / shop pages if enabled
 *
 * @since 1.0.0
 */
function skyverge_random_woocommerce_shop_ordering( $sort_args ) {
	$random_enabled = get_option('wc_random_product_sorting');

	if($random_enabled == 'yes') {
		$orderby_value = isset( $_GET['orderby'] ) ? woocommerce_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

		if ( 'random_list' == $orderby_value ) {
			$sort_args['orderby'] = 'rand';
			$sort_args['order'] = '';
			$sort_args['meta_key'] = '';
		}

		return $sort_args;
	} else {
		return $sort_args;
	}
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'skyverge_random_woocommerce_shop_ordering' );

function skyverge_random_woocommerce_catalog_orderby( $sortby ) {
	$random_enabled = get_option('wc_random_product_sorting');

	if($random_enabled == 'yes') {
		$sortby['random_list'] = __( 'Sort by: random order', 'woocommerce' );

		return $sortby;
	} else {
		return $sortby;
	}
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'skyverge_random_woocommerce_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'skyverge_random_woocommerce_catalog_orderby' );
