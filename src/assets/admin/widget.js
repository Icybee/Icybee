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

], function (Brickrouge) {

	return new Class({

		Extends: Brickrouge.Popover,
		Implements: [ Options, Events ],

		initialize: function(el, options)
		{
			this.parent(el, options)

			this.adjust = null
			this.selected = null
		},

		getAdjust: function()
		{
			return Brickrouge.from(this.element.querySelector('.popover-content :first-child'))
		},

		show: function()
		{
			this.parent()

			this.adjust = this.getAdjust()

			if (this.adjust)
			{
				this.adjust.addEvent('results', this.repositionCallback)
				this.adjust.addEvent('adjust', this.quickRepositionCallback)
			}
		}
	})

})

this.Icybee = {

	Widget: {

		AdjustPopover: require('icybee/adjust-popover')

	}

}
