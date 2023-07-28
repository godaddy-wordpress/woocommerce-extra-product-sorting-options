module.exports = {
	deploy: 'wp',
	framework: false,
	tasks: {
		makepot: {
			reportBugsTo: 'https://wordpress.org/support/plugin/woocommerce-extra-product-sorting-options'
		}
	},
	paths: {
		src: '.',
		exclude: [
			"build"
		]
	}
}
