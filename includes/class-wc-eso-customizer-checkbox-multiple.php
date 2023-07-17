<?php
/**
 * Extra Product Sorting Options for WooCommerce
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Extra Product Sorting Options for WooCommerce to newer
 * versions in the future.
 *
 * Special thanks to Justin Tadlock for his guide here http://justintadlock.com/archives/2015/05/26/multiple-checkbox-customizer-control
 *  upon which this class is based.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2023, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Multiple checkbox customize control class.
 *
 * @since 2.7.0
 */
class WC_ESO_Customize_Checkbox_Multiple extends WP_Customize_Control {


	/**
	 * Enqueue scripts/styles.
	 *
	 * @since 2.7.0
	 */
	public function enqueue() {

		wp_enqueue_script( 'wc-extra-sorting-options-customize-controls', trailingslashit( wc_extra_sorting_options()->get_plugin_url() ) . 'assets/js/customize-controls.js', array( 'jquery' ), WC_Extra_Sorting_Options::VERSION, true );
	}


	/**
	 * Displays the control content.
	 *
	 * @since 2.7.0
	 */
	public function render_content() {

		if ( ! empty( $this->choices ) ) : ?>

			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>

			<?php $multi_values = ! is_array( $this->value() ) ? explode( ',', $this->value() ) : $this->value(); ?>

			<ul>
			<?php foreach ( $this->choices as $value => $label ) : ?>

				<li>
					<label>
						<input type="checkbox" value="<?php echo esc_attr( $value ); ?>" <?php checked( in_array( $value, $multi_values ), true ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				</li>

			<?php endforeach; ?>
			</ul>

			<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', $multi_values ) ); ?>" />
		<?php endif;

	}


}
