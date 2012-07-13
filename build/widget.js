/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var Icybee = {

	Widget: {

		AdjustPopover: new Class
		({
			Implements: [ Options, Events ],

			Extends: Brickrouge.Popover,

			initialize: function(el, options)
			{
				this.parent(el, options)

				this.adjust = null
				this.selected = null
			},

			show: function()
			{
				this.element.setStyle('display', 'none')
				document.body.appendChild(this.element)

				this.parent()

				this.adjust = this.element.getElement('.popover-content :first-child').get('widget')

				if (this.adjust)
				{
					this.adjust.addEvent('results', this.repositionCallback)
					this.adjust.addEvent('adjust', this.quickRepositionCallback)
				}
			},

			close: function()
			{
				this.parent()

				this.element.dispose()
			}
		})
	}
}