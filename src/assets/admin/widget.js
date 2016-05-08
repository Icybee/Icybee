/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('icybee/adjust-popover', [

	'brickrouge'

], (Brickrouge) => {

	return class extends Brickrouge.Popover {

		/**
		 * @param {Element} el
		 * @param {object} options
		 */
		constructor(el, options)
		{
			super(el, options)

			this.selected = null
		}

		/**
		 * Returns the adjust widget.
		 *
		 * @returns {object}
		 */
		get adjust()
		{
			return Brickrouge.from(this.element.querySelector('.popover-content :first-child'))
		}

		/**
		 * @inheritdoc
		 *
		 * Reposition the popover when the content of the adjust element is updated.
		 */
		show()
		{
			super.show()

			const adjust = this.adjust

			if (adjust)
			{
				try {
					if ('observeResult' in adjust) {
						adjust.observeResult(this.repositionCallback)
					}
					if ('observeChange' in adjust) {
						adjust.observeChange(this.quickRepositionCallback)
					}
					if ('addEvent' in adjust) {
						console.warn('adjust should implement observeChange:', adjust)
						adjust.addEvent('change', this.quickRepositionCallback)
					}
				} catch (e) {
					console.error(e)
				}
			}
		}

		/**
		 * @deprecated
		 */
		getAdjust()
		{
			console.log('getAdjust() is deprecated, use this.adjust', new Error)

			return this.adjust
		}

	}

})
