module.exports = {
	framework: false,
	deploy: 'wp',
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
