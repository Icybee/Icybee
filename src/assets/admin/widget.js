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
				adjust.addEvent('results', this.repositionCallback)
				adjust.addEvent('adjust', this.quickRepositionCallback)
			}
		}

		/**
		 * @deprecated
		 */
		getAdjust()
		{
			console.log('deprecated use this.adjust', new Error)

			return this.adjust
		}

	}

})
