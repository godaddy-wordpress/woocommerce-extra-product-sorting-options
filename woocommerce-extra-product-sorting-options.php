<?php
/**
 * Plugin Name: WooCommerce Extra Product Sorting Options
 * Plugin URI: http://www.skyverge.com/product/woocommerce-extra-product-sorting-options/
 * Description: Rename default sorting and optionally extra product sorting options.
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com/
 * Version: 2.0.0
 * Text Domain: woocommerce-extra-product-sorting-options
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
 * Rename default sorting option - helpful if custom sorting is used.
 * Adds sorting by name, on sale, featured, availability, and random to shop pages.
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
					'name'     => __( 'Add Product Sorting:', 'woocommerce' ),
					'desc_tip' => __( 'Select sorting options to add to your shop. "Available Stock" sorts products with the most stock first.', 'woocommerce' ),
				'desc'     	   => '<br/>' . __( 'Tip: These options will be added to the sorting menu in the order that you select them.', 'woocommerce' ),
					'id'       => 'wc_extra_product_sorting_options',
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => array(
						'alphabetical'   => __( 'Name: A to Z', 'woocommerce' ),
						'reverse_alpha'  => __( 'Name: Z to A', 'woocommerce' ),
						'on_sale_first'  => __( 'On-sale First', 'woocommerce' ),
						'featured_first' => __( 'Featured First', 'woocommerce' ),
						'by_stock'   	 => __( 'Available Stock', 'woocommerce' ),
						'randomize'      => __( 'Random', 'woocommerce' ),
					),
					'default'  => '',
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
	$new_default_name = get_option( 'wc_rename_default_sorting' );

	if( $new_default_name == '' ) {
		return $catalog_orderby;
	} else {
		$catalog_orderby = str_replace("Default sorting", $new_default_name, $catalog_orderby);
		return $catalog_orderby;
	}
}
add_filter( 'woocommerce_catalog_orderby', 'skyverge_change_default_sorting_name' );
add_filter( 'woocommerce_default_catalog_orderby_options', 'skyverge_change_default_sorting_name' );


/**
 * Add sorting option to WC sorting arguments
 *
 * @since 2.0.0
*/

function skyverge_add_new_shop_ordering_args( $sort_args ) {
		
	$orderby_value = isset( $_GET['orderby'] ) ? woocommerce_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

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
			$sort_args['orderby'] = 'meta_value_num';
			$sort_args['order'] = 'desc';
			$sort_args['meta_key'] = '_stock';
			break;
				
		case 'featured_first':
			$sort_args['orderby'] = 'meta_value';
			$sort_args['order'] = 'desc';
			$sort_args['meta_key'] = '_featured';
			break;
				
		case 'on_sale_first':
			$sort_args['orderby'] = 'meta_value_num';
			$sort_args['order'] = 'desc';
			$sort_args['meta_key'] = '_sale_price';
			break;
				
		case 'randomize':
			$sort_args['orderby'] = 'rand';
			$sort_args['order'] = '';
			$sort_args['meta_key'] = '';
			break;
		
	}
	
	return $sort_args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'skyverge_add_new_shop_ordering_args' );


function skyverge_add_new_catalog_orderby( $sortby ) {

	$new_sorting_options = get_option('wc_extra_product_sorting_options', array() );
	
	foreach( $new_sorting_options as $option ) {
	
		switch( $option ) {
		
			case 'alphabetical':
				$sortby['alphabetical'] = __( 'Sort by name: A to Z', 'woocommerce' );
				break;
				
			case 'reverse_alpha':
				$sortby['reverse_alpha'] = __( 'Sort by name: Z to A', 'woocommerce' );
				break;
				
			case 'by_stock':
				$sortby['by_stock'] = __( 'Sort by availability', 'woocommerce' );
				break;
			
			case 'featured_first':
				$sortby['featured_first'] = __( 'Show featured items first', 'woocommerce' );
				break;
				
			case 'on_sale_first':
				$sortby['on_sale_first'] = __( 'Show sale items first', 'woocommerce' );
				break;
				
			case 'randomize':
				$sortby['random_list'] = __( 'Sort by: random order', 'woocommerce' );
				break;
				 
		}
		
	}
	
	return $sortby;
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'skyverge_add_new_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'skyverge_add_new_catalog_orderby' );